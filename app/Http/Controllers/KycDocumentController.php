<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\KycDocument;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycDocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $query = KycDocument::with('documentable')->latest();
        if (!$user->isSaaSFounder()) {
            $query->where('company_id', $user->company_id);
        }

        // Sales Executive Privacy Scope: view docs of assigned leads or self
        if ($user->isSales()) {
            $assignedLeadIds = Lead::where('assigned_to_user_id', $user->id)->pluck('id')->toArray();
            $query->where(function ($q) use ($user, $assignedLeadIds) {
                $q->where(function ($lq) use ($assignedLeadIds) {
                    $lq->whereIn('documentable_type', ['App\Models\Lead', 'Customer', 'Lead'])
                       ->whereIn('documentable_id', $assignedLeadIds);
                })->orWhere(function ($uq) use ($user) {
                    $uq->whereIn('documentable_type', ['App\Models\User', 'User'])
                       ->where('documentable_id', $user->id);
                });
            });
        }

        $documents = $query->get();

        $expiredCount = KycDocument::where('expiry_date', '<', now())->count();
        $expiringSoonCount = KycDocument::whereBetween('expiry_date', [now(), now()->addDays(30)])->count();

        $leads = $user->isSales() ? Lead::where('assigned_to_user_id', $user->id)->get() : Lead::where('company_id', $user->company_id)->get();
        $brokers = Broker::where('company_id', $user->company_id)->get();
        $teamUsers = User::where('company_id', $user->company_id)->get();

        return view('documents.index', compact('documents', 'expiredCount', 'expiringSoonCount', 'leads', 'brokers', 'teamUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer',
            'document_type' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'expiry_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        if ($user->isBroker()) {
            return back()->with('error', 'Brokers cannot upload internal KYC documents.');
        }

        $file = $request->file('document_file');
        $filePath = $file->store('kyc_documents', 'public');

        KycDocument::create([
            'company_id' => $user->company_id ?? 1,
            'documentable_type' => $request->documentable_type,
            'documentable_id' => $request->documentable_id,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'file_path' => '/storage/' . $filePath,
            'expiry_date' => $request->expiry_date,
            'status' => 'verified',
            'notes' => $request->notes,
        ]);

        return back()->with('status', 'KYC Document uploaded & organized successfully!');
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user->isCompanyAdmin() && !$user->isManager() && !$user->isSaaSFounder()) {
            return back()->with('error', 'Unauthorized. Only Admins and Managers can delete KYC documents.');
        }

        $doc = KycDocument::findOrFail($id);
        $doc->delete();

        return back()->with('status', 'Document deleted successfully.');
    }
}

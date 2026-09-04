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

        $query = KycDocument::with('documentable')->latest();
        if (!$user->isSaaSFounder()) {
            $query->where('company_id', $user->company_id);
        }

        $documents = $query->get();

        $expiredCount = KycDocument::where('expiry_date', '<', now())->count();
        $expiringSoonCount = KycDocument::whereBetween('expiry_date', [now(), now()->addDays(30)])->count();

        $leads = Lead::where('company_id', $user->company_id)->get();
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
        $doc = KycDocument::findOrFail($id);
        $doc->delete();

        return back()->with('status', 'Document deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\KycDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| PREVIOUS INTER-ENTITY SHARING LOGIC (COMMENTED OUT AS PER USER REQUEST)
|--------------------------------------------------------------------------
|
| class KycDocumentControllerOld extends Controller
| {
|     public function index(Request $request)
|     {
|         $user = Auth::user();
|         if ($user->isBroker()) {
|             return redirect()->route('dashboard');
|         }
|         $query = KycDocument::with('documentable')->latest();
|         if (!$user->isSaaSFounder()) {
|             $query->where('company_id', $user->company_id);
|         }
|         if ($user->isSales()) {
|             $assignedLeadIds = Lead::where('assigned_to_user_id', $user->id)->pluck('id')->toArray();
|             $query->where(function ($q) use ($user, $assignedLeadIds) {
|                 $q->where(function ($lq) use ($assignedLeadIds) {
|                     $lq->whereIn('documentable_type', ['App\Models\Lead', 'Customer', 'Lead'])
|                        ->whereIn('documentable_id', $assignedLeadIds);
|                 })->orWhere(function ($uq) use ($user) {
|                     $uq->whereIn('documentable_type', ['App\Models\User', 'User'])
|                        ->where('documentable_id', $user->id);
|                 });
|             });
|         }
|         $documents = $query->get();
|         $expiredCount = KycDocument::where('expiry_date', '<', now())->count();
|         $expiringSoonCount = KycDocument::whereBetween('expiry_date', [now(), now()->addDays(30)])->count();
|         $leads = $user->isSales() ? Lead::where('assigned_to_user_id', $user->id)->get() : Lead::where('company_id', $user->company_id)->get();
|         $brokers = Broker::where('company_id', $user->company_id)->get();
|         $teamUsers = User::where('company_id', $user->company_id)->get();
|         return view('documents.index_old', compact('documents', 'expiredCount', 'expiringSoonCount', 'leads', 'brokers', 'teamUsers'));
|     }
| }
|
*/

class KycDocumentController extends Controller
{
    /**
     * Company Private Digital Drive & File Vault
     * Strictly isolated per company (`company_id`). No inter-entity or cross-user sharing.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard')->with('error', 'Brokers do not have access to company private drive.');
        }

        $query = KycDocument::latest();

        // Strict Tenant Isolation: Only show files belonging to logged-in user's Company
        if (!$user->isSaaSFounder()) {
            $query->where('company_id', $user->company_id);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhere('document_type', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        // Folder / Category Filter
        if ($request->filled('category')) {
            $query->where('document_type', $request->category);
        }

        // Confidentiality Level Filter
        if ($request->filled('confidentiality')) {
            $query->where('document_number', $request->confidentiality);
        }

        $documents = $query->get();

        // Metrics Calculation for Company Drive
        $totalFilesCount = $documents->count();
        $legalFilesCount = KycDocument::where('company_id', $user->company_id ?? 1)
            ->where('document_type', 'Legal & RERA Documents')
            ->count();
        $financialFilesCount = KycDocument::where('company_id', $user->company_id ?? 1)
            ->whereIn('document_type', ['Company Registration & Tax', 'Financial & Banking Assets'])
            ->count();
        $confidentialFilesCount = KycDocument::where('company_id', $user->company_id ?? 1)
            ->where('document_number', 'Confidential (Admins Only)')
            ->count();

        return view('documents.index', compact(
            'documents',
            'totalFilesCount',
            'legalFilesCount',
            'financialFilesCount',
            'confidentialFilesCount'
        ));
    }

    /**
     * Upload & Store a Company Private File into the Drive
     */
    public function store(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'confidentiality' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip|max:20480',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        if ($user->isBroker()) {
            return back()->with('error', 'Brokers cannot upload files to company drive.');
        }

        $file = $request->file('document_file');
        $filePath = $file->store('company_drive_files', 'public');

        KycDocument::create([
            'company_id' => $user->company_id ?? 1,
            'documentable_type' => 'App\Models\Company',
            'documentable_id' => $user->company_id ?? 1,
            'document_type' => $request->category, // Folder / Category Name
            'document_number' => $request->confidentiality, // Confidentiality Level Tag
            'file_path' => '/storage/' . $filePath,
            'expiry_date' => $request->expiry_date,
            'status' => 'verified',
            'notes' => $request->file_name . ($request->notes ? ' - ' . $request->notes : ''),
        ]);

        return back()->with('status', "File '{$request->file_name}' successfully uploaded to Company Drive!");
    }

    /**
     * Delete a File from Company Drive
     */
    public function destroy($id)
    {
        $user = Auth::user();

        Gate::authorize('manage-users');

        $doc = KycDocument::where(function ($q) use ($user) {
            if (!$user->isSaaSFounder()) {
                $q->where('company_id', $user->company_id);
            }
        })->findOrFail($id);

        $doc->delete();

        return back()->with('status', 'Drive file deleted successfully.');
    }
}

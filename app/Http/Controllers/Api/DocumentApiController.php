<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use Illuminate\Http\Request;

class DocumentApiController extends Controller
{
    public function index(Request $request)
    {
        abort_if($request->user()->isBroker(), 403, 'Documents are available for internal staff only.');
        $query = KycDocument::where('company_id', $request->user()->company_id)->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('document_type', 'like', '%' . $request->search . '%')
                    ->orWhere('document_number', 'like', '%' . $request->search . '%')
                    ->orWhere('notes', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json(['status' => 'success', 'data' => $query->paginate(20)]);
    }

    public function store(Request $request)
    {
        abort_if($request->user()->isBroker(), 403, 'Documents are available for internal staff only.');
        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'confidentiality' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip|max:20480',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $path = $request->file('document_file')->store('company_drive_files', 'public');
        $document = KycDocument::create([
            'company_id' => $request->user()->company_id,
            'documentable_type' => 'App\Models\Company',
            'documentable_id' => $request->user()->company_id,
            'document_type' => $validated['category'],
            'document_number' => $validated['confidentiality'],
            'file_path' => '/storage/' . $path,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'status' => 'verified',
            'notes' => $validated['file_name'] . (!empty($validated['notes']) ? ' - ' . $validated['notes'] : ''),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Document uploaded.', 'data' => $document], 201);
    }

    public function destroy(Request $request, int $id)
    {
        abort_unless($request->user()->isManager() || $request->user()->isCompanyAdmin(), 403);
        $document = KycDocument::where('company_id', $request->user()->company_id)->findOrFail($id);
        $document->delete();
        return response()->json(['status' => 'success', 'message' => 'Document deleted.']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Booking;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        $agreements = Agreement::with(['booking.lead', 'booking.unit.project', 'booking.salesUser'])
            ->latest()
            ->get();

        $bookingsPendingAgreement = Booking::whereDoesntHave('agreement')
            ->with(['lead', 'unit.project'])
            ->latest()
            ->get();

        return view('agreements.index', compact('agreements', 'bookingsPendingAgreement'));
    }

    public function uploadDocument(Request $request, Agreement $agreement)
    {
        $request->validate([
            'document_type' => 'required|in:draft,signed',
            'agreement_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('agreement_file');
        $path = $file->store('agreements', 'public');

        if ($request->document_type === 'draft') {
            $agreement->update([
                'draft_file_path' => $path,
                'status' => 'pending_signature',
            ]);
            $msg = "Draft Agreement PDF uploaded successfully for Agreement {$agreement->agreement_number}!";
        } else {
            $agreement->update([
                'signed_file_path' => $path,
                'status' => 'completed',
                'executed_at' => now(),
            ]);
            $msg = "Signed Executed Agreement PDF uploaded for Agreement {$agreement->agreement_number}!";
        }

        \App\Services\AuditLogService::log('agreement_uploaded', $msg, $agreement);

        return back()->with('success', $msg);
    }

    public function viewFile(Agreement $agreement, string $type)
    {
        $path = ($type === 'signed') ? $agreement->signed_file_path : $agreement->draft_file_path;

        if (!$path) {
            abort(404, 'Agreement file path not found.');
        }

        $cleanPath = ltrim(str_replace('storage/', '', $path), '/');

        $fullPath = storage_path('app/public/' . $cleanPath);

        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $cleanPath);
        }

        if (!file_exists($fullPath)) {
            abort(404, 'Agreement PDF file does not exist on disk.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
        ]);
    }
}

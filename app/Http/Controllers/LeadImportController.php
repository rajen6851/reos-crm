<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LeadImportController extends Controller
{
    public function importCsv(Request $request)
    {
        Gate::authorize('manage-leads');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'auto_assign' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            return back()->with('error', 'Uploaded CSV file is empty or invalid.');
        }

        // Map column names
        $headerMap = array_map(fn($col) => strtolower(trim(str_replace([' ', '_', '-'], '', $col))), $header);

        $nameIndex = array_search('name', $headerMap);
        if ($nameIndex === false) $nameIndex = array_search('fullname', $headerMap);
        $phoneIndex = array_search('phone', $headerMap);
        if ($phoneIndex === false) $phoneIndex = array_search('mobile', $headerMap);
        $emailIndex = array_search('email', $headerMap);
        $sourceIndex = array_search('source', $headerMap);
        $budgetIndex = array_search('budget', $headerMap);
        $projectIndex = array_search('project', $headerMap);

        if ($nameIndex === false || $phoneIndex === false) {
            return back()->with('error', 'CSV must contain at least "Name" and "Phone" columns.');
        }

        $autoAssign = $request->boolean('auto_assign', true);

        // Fetch active sales executives for round-robin assignment
        $salesExecutives = User::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['sales_executive', 'manager']);
            })
            ->pluck('id')
            ->toArray();

        $executiveCount = count($salesExecutives);
        $execIndex = 0;

        $importedCount = 0;
        $defaultSource = LeadSource::firstOrCreate(
            ['company_id' => $user->company_id, 'name' => 'CSV Bulk Import'],
            ['code' => 'CSV-IMP', 'is_active' => true]
        );

        $defaultProject = Project::where('company_id', $user->company_id)->first();

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (empty($row[$nameIndex]) && empty($row[$phoneIndex])) {
                continue;
            }

            $name = $row[$nameIndex] ?? 'Prospective Client';
            $phone = $row[$phoneIndex] ?? '';
            $email = ($emailIndex !== false && isset($row[$emailIndex])) ? $row[$emailIndex] : null;
            $budget = ($budgetIndex !== false && isset($row[$budgetIndex])) ? (float)$row[$budgetIndex] : 0.00;

            // Determine assigned executive via Round-Robin
            $assignedUserId = null;
            if ($autoAssign && $executiveCount > 0) {
                $assignedUserId = $salesExecutives[$execIndex % $executiveCount];
                $execIndex++;
            }

            Lead::create([
                'company_id' => $user->company_id,
                'assigned_to_user_id' => $assignedUserId,
                'project_id' => $defaultProject?->id,
                'lead_source_id' => $defaultSource->id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'budget' => $budget,
                'status' => 'new',
                'priority' => 'warm',
                'notes' => 'Imported via CSV bulk upload.',
            ]);

            $importedCount++;
        }

        fclose($handle);

        return back()->with('status', "Successfully imported {$importedCount} leads! " . ($autoAssign ? "Assigned via Round-Robin distribution across {$executiveCount} sales agents." : ""));
    }
}

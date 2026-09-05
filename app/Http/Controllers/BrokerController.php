<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerCommission;
use App\Models\BrokerLead;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Services\BrokerCommissionService;
use App\Services\DuplicateLeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrokerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $broker = Broker::withoutGlobalScopes()->where('user_id', $user->id)->first()
            ?? Broker::withoutGlobalScopes()->where('email', $user->email)->first();

        if (!$broker) {
            $broker = Broker::withoutGlobalScopes()->create([
                'company_id' => $user->company_id ?? 1,
                'user_id' => $user->id,
                'agency_name' => ($user->name ?? 'Channel Partner') . ' Agency',
                'broker_code' => 'BRK-' . rand(1000, 9999),
                'phone' => $user->phone ?? '9000000000',
                'email' => $user->email,
                'commission_rate' => 2.50,
                'status' => 'active',
            ]);
        }

        if ($broker) {
            // Auto-heal 1: Ensure any Lead in the system assigned to this broker has a BrokerLead pivot record
            $assignedLeads = Lead::withoutGlobalScopes()->where('broker_id', $broker->id)->get();
            foreach ($assignedLeads as $l) {
                BrokerLead::withoutGlobalScopes()->firstOrCreate(
                    ['lead_id' => $l->id],
                    [
                        'company_id' => $l->company_id,
                        'broker_id' => $l->broker_id,
                        'project_id' => $l->interested_project_id ?? Project::withoutGlobalScopes()->first()?->id,
                        'submitted_at' => $l->created_at,
                        'broker_visible_status' => ucwords(str_replace('_', ' ', $l->status)),
                    ]
                );
            }

            // Auto-heal 2: Ensure all 'Booked' broker leads have a generated BrokerCommission
            $bookedLeads = BrokerLead::withoutGlobalScopes()
                ->where('broker_id', $broker->id)
                ->whereIn('broker_visible_status', ['Booked', 'converted', 'BOOKED', 'CONVERTED'])
                ->get();

            $commissionService = app(BrokerCommissionService::class);
            foreach ($bookedLeads as $bl) {
                $commissionService->ensureCommissionForBrokerLead($bl);
            }
        }

        // Bypassing tenant scope on BrokerLead, Lead, and Project models so independent broker sees ALL their submitted leads with full customer details
        $brokerLeads = $broker 
            ? BrokerLead::withoutGlobalScopes()
                ->where('broker_id', $broker->id)
                ->with([
                    'lead' => function ($q) {
                        $q->withoutGlobalScopes();
                    },
                    'lead.activities',
                    'project' => function ($q) {
                        $q->withoutGlobalScopes();
                    },
                    'project.company'
                ])
                ->latest()
                ->get() 
            : collect();

        // Bypassing tenant scope on BrokerCommission so broker sees earned commission payouts with full builder company and project details
        $commissions = $broker 
            ? BrokerCommission::withoutGlobalScopes()
                ->where('broker_id', $broker->id)
                ->with([
                    'booking' => function ($q) {
                        $q->withoutGlobalScopes()->with([
                            'project' => function ($pq) {
                                $pq->withoutGlobalScopes()->with('company');
                            }
                        ]);
                    }
                ])
                ->latest()
                ->get() 
            : collect();

        $totalCommissions = $broker 
            ? BrokerCommission::withoutGlobalScopes()
                ->where('broker_id', $broker->id)
                ->where('status', '!=', 'cancelled')
                ->sum('total_commission_amount') 
            : 0;

        $approvedCommissions = $broker 
            ? BrokerCommission::withoutGlobalScopes()
                ->where('broker_id', $broker->id)
                ->whereIn('status', ['approved', 'ready_for_payout', 'paid'])
                ->sum('total_commission_amount') 
            : 0;

        // Fetch ALL active Builder Companies and their PUBLIC Projects
        $companies = Company::withoutGlobalScopes()->where('status', 'active')->with(['projects' => function ($q) {
            $q->withoutGlobalScopes()->where('status', 'active')->where(function ($vq) {
                $vq->where('visibility', 'public')->orWhereNull('visibility');
            });
        }])->get();

        $projects = Project::withoutGlobalScopes()
            ->where('status', 'active')
            ->where(function ($vq) {
                $vq->where('visibility', 'public')->orWhereNull('visibility');
            })
            ->with('company')
            ->get();

        return view('dashboard.broker', compact('user', 'broker', 'brokerLeads', 'commissions', 'totalCommissions', 'approvedCommissions', 'companies', 'projects'));
    }

    public function storeLead(Request $request, DuplicateLeadService $duplicateService)
    {
        $user = Auth::user();
        $broker = Broker::withoutGlobalScopes()->where('user_id', $user->id)->first()
            ?? Broker::withoutGlobalScopes()->where('email', $user->email)->first();

        if (!$broker) {
            $broker = Broker::withoutGlobalScopes()->create([
                'company_id' => $user->company_id ?? 1,
                'user_id' => $user->id,
                'agency_name' => ($user->name ?? 'Channel Partner') . ' Agency',
                'broker_code' => 'BRK-' . rand(1000, 9999),
                'phone' => $user->phone ?? '9000000000',
                'email' => $user->email,
                'commission_rate' => 2.50,
                'status' => 'active',
            ]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'project_id' => 'required',
            'notes' => 'nullable|string',
        ]);

        $project = Project::withoutGlobalScopes()->findOrFail($validated['project_id']);

        if ($project->visibility === 'private') {
            return back()->with('error', 'Selected property is private and not open for channel partner lead submissions.');
        }

        $duplicate = $duplicateService->findDuplicate($project->company_id, $validated['phone'], $validated['email'] ?? null);

        $lead = Lead::withoutGlobalScopes()->create([
            'company_id' => $project->company_id,
            'lead_code' => 'LD-BRK' . rand(1000, 9999),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? '',
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'broker_id' => $broker->id,
            'interested_project_id' => $project->id,
            'status' => 'new',
            'is_duplicate' => $duplicate ? true : false,
            'notes' => $validated['notes'] ?? 'Lead submitted via Independent Channel Partner / Broker Portal.',
        ]);

        $brokerLead = BrokerLead::withoutGlobalScopes()->create([
            'company_id' => $project->company_id,
            'broker_id' => $broker->id,
            'lead_id' => $lead->id,
            'project_id' => $project->id,
            'submitted_at' => now(),
            'broker_visible_status' => 'Submitted',
        ]);

        if ($duplicate) {
            return redirect()->route('dashboard')->with('warning', "Lead {$lead->lead_code} submitted! DUPLICATE ALERT: Matches existing lead {$duplicate->lead_code}.");
        }

        return redirect()->route('dashboard')->with('success', "Lead {$lead->first_name} (Code: {$lead->lead_code}) submitted successfully!");
    }

    public function brokersDirectory(Request $request)
    {
        $user = Auth::user();

        if ($user->isSales() || $user->isBroker()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. Brokers Directory is reserved for Admins and Managers.');
        }

        // Bypassing tenant scope for SuperAdmin Founder to view all global brokers
        $query = $user->isSaaSFounder()
            ? Broker::withoutGlobalScopes()->with(['user', 'company'])
            : Broker::with(['user', 'company']);

        $brokers = $query->withCount([
            'brokerLeads as total_submitted_leads',
            'brokerLeads as converted_leads' => function ($q) {
                $q->whereIn('broker_visible_status', ['Booked', 'converted', 'BOOKED', 'CONVERTED']);
            }
        ])->latest()->get();

        $selectedBroker = null;
        if ($request->has('broker_id')) {
            $selectedBroker = Broker::withoutGlobalScopes()
                ->with([
                    'user',
                    'company',
                    'brokerLeads' => function ($q) {
                        $q->withoutGlobalScopes();
                    },
                    'brokerLeads.lead' => function ($q) {
                        $q->withoutGlobalScopes();
                    },
                    'brokerLeads.project' => function ($q) {
                        $q->withoutGlobalScopes();
                    },
                    'commissions' => function ($q) {
                        $q->withoutGlobalScopes();
                    },
                    'commissions.booking' => function ($q) {
                        $q->withoutGlobalScopes();
                    }
                ])
                ->find($request->get('broker_id'));
        }

        return view('brokers.index', compact('brokers', 'selectedBroker'));
    }

    public function storeBroker(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'agency_name' => 'required|string|max:150',
            'contact_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $brokerRole = \App\Models\Role::where(function ($q) use ($user) {
            $q->whereNull('company_id')->orWhere('company_id', $user->company_id);
        })->where('slug', 'broker')->first();

        if (!$brokerRole) {
            $brokerRole = \App\Models\Role::create([
                'company_id' => $user->company_id,
                'name' => 'Channel Partner / Broker',
                'slug' => 'broker',
                'description' => 'Channel Partner Broker role',
            ]);
        }

        $newUser = \App\Models\User::create([
            'company_id' => $user->company_id,
            'role_id' => $brokerRole->id,
            'name' => $validated['contact_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $broker = Broker::create([
            'company_id' => $user->company_id,
            'user_id' => $newUser->id,
            'agency_name' => $validated['agency_name'],
            'broker_code' => 'BRK-' . rand(1000, 9999),
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'commission_rate' => $validated['commission_rate'] ?? 2.50,
            'status' => 'active',
        ]);

        $defaultBrokerPassword = 'password123';

        // Dispatch Welcome Email with Login Credentials
        try {
            \Illuminate\Support\Facades\Mail::to($newUser->email)
                ->send(new \App\Mail\UserWelcomeCredentialsMail($newUser, $defaultBrokerPassword));
            \Illuminate\Support\Facades\Log::info("[WELCOME MAIL SENT] Credentials sent to new broker user #{$newUser->id} ({$newUser->email})");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("[WELCOME MAIL ERROR] Failed to send credentials email to broker #{$newUser->id} ({$newUser->email}): " . $e->getMessage());
        }

        return redirect()->route('brokers.index')->with('success', "Channel Partner Broker '{$broker->agency_name}' registered successfully! Welcome email sent to {$newUser->email}");
    }

    public function updateBroker(Request $request, Broker $broker)
    {
        $validated = $request->validate([
            'agency_name' => 'required|string|max:150',
            'contact_name' => 'nullable|string|max:150',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        $broker->update([
            'agency_name' => $validated['agency_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'commission_rate' => $validated['commission_rate'] ?? $broker->commission_rate,
            'status' => $validated['status'] ?? $broker->status,
        ]);

        if ($broker->user) {
            $broker->user->update([
                'name' => $validated['contact_name'] ?? $validated['agency_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);
        }

        return redirect()->route('brokers.index')->with('success', "Partner Broker '{$broker->agency_name}' specs updated successfully!");
    }

    public function destroy(Broker $broker)
    {
        if (!Auth::user()->isCompanyAdmin() && Auth::user()->role?->slug !== 'founder') {
            return back()->with('error', 'Only Company Admins can delete broker profiles.');
        }

        $agencyName = $broker->agency_name;
        $broker->delete();

        return redirect()->route('brokers.index')->with('success', "Broker profile '{$agencyName}' deleted successfully!");
    }

    public function show($id)
    {
        $broker = Broker::withoutGlobalScopes()
            ->with([
                'user',
                'company',
                'brokerLeads' => function ($q) {
                    $q->withoutGlobalScopes();
                },
                'brokerLeads.lead' => function ($q) {
                    $q->withoutGlobalScopes();
                },
                'brokerLeads.project' => function ($q) {
                    $q->withoutGlobalScopes();
                },
                'commissions' => function ($q) {
                    $q->withoutGlobalScopes();
                },
                'commissions.booking' => function ($q) {
                    $q->withoutGlobalScopes();
                }
            ])
            ->findOrFail($id);

        $totalCommissions = BrokerCommission::withoutGlobalScopes()
            ->where('broker_id', $broker->id)
            ->where('status', '!=', 'cancelled')
            ->sum('total_commission_amount');

        $approvedCommissions = BrokerCommission::withoutGlobalScopes()
            ->where('broker_id', $broker->id)
            ->whereIn('status', ['approved', 'ready_for_payout', 'paid'])
            ->sum('total_commission_amount');

        return view('brokers.show', compact('broker', 'totalCommissions', 'approvedCommissions'));
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Demand Notice – {{ $schedule->milestone_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; padding: 0 !important; }
            .print-card { border: none !important; shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 text-slate-900">
    <div class="max-w-3xl mx-auto space-y-4">
        <!-- Control Actions -->
        <div class="no-print flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
            <a href="{{ route('payments.index') }}" class="text-xs font-bold text-slate-600 hover:text-indigo-600">← Back to Payments</a>
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                <i class="fa-solid fa-print mr-1"></i>Print / Save Demand Letter PDF
            </button>
        </div>

        <!-- Formal Demand Letter Card -->
        <div class="print-card bg-white p-8 md:p-12 rounded-3xl border border-slate-200 shadow-xl space-y-8">
            <!-- Company Letterhead Header -->
            <div class="flex justify-between items-start border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $schedule->booking->company->name ?? 'REOS Enterprise Developer' }}</h1>
                    <p class="text-xs text-slate-500 max-w-sm mt-1">{{ $schedule->booking->company->address ?? 'Corporate Real Estate Office' }}</p>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">GST/TAX: {{ $schedule->booking->company->tax_number ?? '36AAACA12341ZV' }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-extrabold rounded-full uppercase tracking-wider">
                        Official Demand Notice
                    </span>
                    <p class="text-xs font-mono text-slate-500 mt-2">Ref: DMN-{{ str_pad($schedule->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-xs text-slate-500">Date: {{ date('d M, Y') }}</p>
                </div>
            </div>

            <!-- Recipient Details -->
            <div class="grid grid-cols-2 gap-6 text-xs">
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Primary Buyer</span>
                    <p class="font-extrabold text-slate-900 text-sm">{{ $schedule->booking->lead->name ?? 'Valued Purchaser' }}</p>
                    <p class="text-slate-600">{{ $schedule->booking->lead->phone ?? '' }}</p>
                    <p class="text-slate-600">{{ $schedule->booking->lead->email ?? '' }}</p>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Unit & Project Allotment</span>
                    <p class="font-extrabold text-slate-900 text-sm">Unit {{ $schedule->booking->unit->unit_number ?? 'N/A' }}</p>
                    <p class="text-slate-600">{{ $schedule->booking->unit->project->name ?? 'Real Estate Project' }}</p>
                    <p class="text-slate-600">Booking Ref: #BK-{{ $schedule->booking->id }}</p>
                </div>
            </div>

            <!-- Subject & Letter Body -->
            <div class="space-y-4 text-xs leading-relaxed text-slate-700">
                <p class="font-bold text-slate-900 text-sm">
                    Subject: Demand Notice for Milestone Payment – {{ $schedule->milestone_name }}
                </p>
                <p>Dear Customer,</p>
                <p>
                    Greetings from <strong>{{ $schedule->booking->company->name ?? 'REOS Realty' }}</strong>. We are pleased to inform you that construction work at <strong>{{ $schedule->booking->unit->project->name ?? 'the project site' }}</strong> has reached the <strong>{{ $schedule->milestone_name }}</strong> stage.
                </p>
                <p>
                    As per the agreed booking payment schedule, the installment for this milestone is now due for payment on or before <strong>{{ optional($schedule->due_date)->format('d M, Y') ?? 'Immediate Due' }}</strong>.
                </p>
            </div>

            <!-- Payment Breakdown Table -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                        <tr>
                            <th class="p-3.5">Milestone Description</th>
                            <th class="p-3.5 text-center">Stage %</th>
                            <th class="p-3.5 text-right">Amount Due (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr>
                            <td class="p-3.5 font-bold text-slate-900">{{ $schedule->milestone_name }}</td>
                            <td class="p-3.5 text-center font-mono text-slate-600">{{ number_format($schedule->percentage, 2) }}%</td>
                            <td class="p-3.5 text-right font-extrabold text-slate-900 font-mono">₹{{ number_format($schedule->due_amount, 2) }}</td>
                        </tr>
                        @if($schedule->paid_amount > 0)
                        <tr class="bg-emerald-50/50 text-emerald-800">
                            <td colspan="2" class="p-3.5 font-bold">Less: Previously Received Token Payment</td>
                            <td class="p-3.5 text-right font-bold font-mono">- ₹{{ number_format($schedule->paid_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-300">
                            <td colspan="2" class="p-3.5 text-right font-black uppercase text-[11px]">Net Amount Payable:</td>
                            <td class="p-3.5 text-right font-black text-indigo-700 text-sm font-mono">
                                ₹{{ number_format(max(0, $schedule->due_amount - $schedule->paid_amount), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bank Remittance Instructions -->
            <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100 space-y-2 text-xs">
                <div class="font-extrabold text-indigo-900"><i class="fa-solid fa-building-columns text-indigo-600 mr-1"></i>Payment Remittance Details</div>
                <div class="grid grid-cols-2 gap-2 text-indigo-950 font-mono text-[11px]">
                    <div>Bank Name: HDFC Bank Ltd</div>
                    <div>Account Name: {{ $schedule->booking->company->name ?? 'REOS Real Estate' }}</div>
                    <div>A/C Number: 50200088991234</div>
                    <div>IFSC Code: HDFC0000123</div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="pt-8 border-t border-slate-200 flex justify-between items-end text-xs text-slate-500">
                <div>
                    <p class="font-bold text-slate-900">Authorized Signatory</p>
                    <p>{{ $schedule->booking->company->name ?? 'REOS Enterprise Developer' }}</p>
                </div>
                <div class="text-right italic">
                    This is a computer-generated demand notice issued via REOS Platform.
                </div>
            </div>
        </div>
    </div>
</body>
</html>

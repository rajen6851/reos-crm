@extends('layouts.reos')

@section('title', 'Salary Payslip - ' . ($salarySlip->user->name ?? 'Staff') . ' - REOS')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hrms.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-[#0F172A] font-bold text-xs rounded-xl transition flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to HRMS</span>
        </a>
        <button onclick="window.print()" class="px-5 py-2.5 bg-[#059669] hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
            <i class="fa-solid fa-print"></i>
            <span>Print Payslip</span>
        </button>
    </div>

    <!-- Official Payslip Document Card -->
    <div class="bg-white rounded-3xl p-8 border border-[#E2E8F0] shadow-md space-y-8 print:border-none print:shadow-none print:p-0">
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-[#E2E8F0] pb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl overflow-hidden border border-emerald-200 p-0.5 bg-white shadow-2xs">
                    <img src="{{ asset('images/logo.jpg') }}" alt="REOS Logo" class="w-full h-full object-cover rounded-xl">
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-[#0F172A] tracking-tight">{{ $salarySlip->company->name ?? 'REOS CRM Enterprise' }}</h2>
                    <p class="text-xs text-slate-500 font-medium">Official Confidential Employee Payslip</p>
                </div>
            </div>
            <div class="text-right">
                <div class="px-3 py-1 bg-emerald-50 text-[#047857] border border-emerald-200 text-xs font-bold font-mono rounded-lg inline-block">
                    PAYSLIP FOR {{ strtoupper($salarySlip->month) }}
                </div>
                <div class="text-[11px] text-slate-400 font-mono mt-1">Generated: {{ $salarySlip->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Employee Name</div>
                <div class="font-extrabold text-[#0F172A] text-sm mt-0.5">{{ $salarySlip->user->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Email / Phone</div>
                <div class="font-bold text-slate-700 mt-0.5">{{ $salarySlip->user->email ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Designation / Role</div>
                <div class="font-bold text-indigo-700 uppercase mt-0.5">{{ $salarySlip->user->role->name ?? 'Staff Member' }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase">Pay Period</div>
                <div class="font-bold font-mono text-emerald-800 mt-0.5">{{ $salarySlip->month }}</div>
            </div>
        </div>

        <!-- Earnings & Deductions Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Earnings -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-emerald-50 px-4 py-2.5 font-bold text-xs text-[#047857] border-b border-emerald-200 uppercase tracking-wider flex justify-between">
                    <span>Earnings</span>
                    <span>Amount (₹)</span>
                </div>
                <div class="p-4 space-y-3 text-xs">
                    <div class="flex justify-between font-medium">
                        <span class="text-slate-600">Basic Salary</span>
                        <span class="font-mono font-bold text-[#0F172A]">₹{{ number_format($salarySlip->basic_salary, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-medium">
                        <span class="text-slate-600">HRA & Allowances</span>
                        <span class="font-mono font-bold text-[#0F172A]">₹{{ number_format($salarySlip->allowances, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-medium">
                        <span class="text-slate-600">Sales Commission Earned</span>
                        <span class="font-mono font-bold text-emerald-700">₹{{ number_format($salarySlip->commission_earned, 2) }}</span>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-between font-extrabold text-sm">
                        <span>Total Gross Earnings</span>
                        <span class="font-mono text-emerald-800">₹{{ number_format($salarySlip->basic_salary + $salarySlip->allowances + $salarySlip->commission_earned, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Deductions -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-rose-50 px-4 py-2.5 font-bold text-xs text-[#DC2626] border-b border-rose-200 uppercase tracking-wider flex justify-between">
                    <span>Deductions</span>
                    <span>Amount (₹)</span>
                </div>
                <div class="p-4 space-y-3 text-xs">
                    <div class="flex justify-between font-medium">
                        <span class="text-slate-600">Professional Tax & TDS</span>
                        <span class="font-mono font-bold text-[#0F172A]">₹{{ number_format($salarySlip->deductions, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-medium text-slate-400">
                        <span>Unpaid Leave / LOP</span>
                        <span class="font-mono font-bold">₹0.00</span>
                    </div>
                    <div class="pt-9 border-t border-slate-200 flex justify-between font-extrabold text-sm">
                        <span>Total Deductions</span>
                        <span class="font-mono text-rose-700">₹{{ number_format($salarySlip->deductions, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Salary Summary Banner -->
        <div class="p-6 rounded-2xl bg-linear-to-r from-[#0F172A] to-slate-800 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-xs text-indigo-300 font-bold uppercase tracking-wider">Net Take-Home Salary</div>
                <div class="text-xs text-slate-400">Transferred to registered bank account</div>
            </div>
            <div class="text-3xl font-extrabold font-mono text-emerald-400">
                ₹{{ number_format($salarySlip->net_salary, 2) }}
            </div>
        </div>

        <!-- Footnote Signatures -->
        <div class="pt-12 flex justify-between items-end text-xs text-slate-500">
            <div>
                <div class="font-bold text-[#0F172A]">Employee Signature</div>
                <div class="w-48 border-b border-slate-300 mt-8"></div>
            </div>
            <div class="text-right">
                <div class="font-bold text-[#0F172A]">Authorized Signatory</div>
                <div class="w-48 border-b border-slate-300 mt-8 ml-auto"></div>
                <div class="text-[10px] text-slate-400 mt-1">HR & Accounts Department</div>
            </div>
        </div>
    </div>
</div>
@endsection

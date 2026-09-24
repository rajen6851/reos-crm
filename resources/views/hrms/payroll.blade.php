@extends('layouts.reos')

@section('title', 'Payroll Management – UrbanProperty')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Banner & Breadcrumb -->
    <div class="bg-gradient-to-r from-emerald-900 to-emerald-700 text-white p-6 rounded-2xl shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="relative z-10">
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-300 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-white transition">Home</a>
                <span>›</span>
                <a href="{{ route('hrms.dashboard') }}" class="hover:text-white transition">HRMS</a>
                <span>›</span>
                <span class="text-white font-bold">Payroll Management</span>
            </div>
            <h1 class="page-heading text-3xl font-extrabold tracking-tight">
                Payroll & Salary Slips
            </h1>
            <p class="text-emerald-200 text-sm mt-1 max-w-xl">
                Generate, manage, and distribute monthly salary slips for your staff.
            </p>
        </div>
        
        @if(auth()->user()->isCompanyAdmin() || auth()->user()->isCompanyFounder() || auth()->user()->isSaaSFounder())
        <div class="relative z-10 flex space-x-3">
            <button onclick="document.getElementById('payrollModal').classList.remove('hidden')" class="px-5 py-2.5 bg-white text-emerald-900 hover:bg-emerald-50 font-bold rounded-xl shadow-lg transition flex items-center space-x-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                <span>Generate Salary Slip</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Payroll Table -->
    <div class="bg-white/80 backdrop-blur-xl border border-slate-200 p-0 rounded-2xl shadow-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white">
            <h3 class="text-lg font-extrabold text-slate-900"><i class="fa-solid fa-money-check-dollar text-emerald-500 mr-2"></i>Generated Salary Slips</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wider font-extrabold">
                        <th class="py-4 px-6">Employee</th>
                        <th class="py-4 px-6">Month</th>
                        <th class="py-4 px-6">Net Salary</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($salarySlips as $slip)
                    <tr class="hover:bg-emerald-50/50 transition duration-200 group">
                        <td class="py-4 px-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold shadow-sm">
                                    {{ substr($slip->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-extrabold text-slate-900">{{ $slip->user->name ?? 'User' }}</div>
                                    @if(auth()->user()->isSaaSFounder())
                                        <div class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full inline-block mt-1">{{ $slip->company->name ?? '' }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 font-mono font-bold text-slate-700">{{ date('F Y', strtotime($slip->month . '-01')) }}</td>
                        <td class="py-4 px-6 font-mono font-extrabold text-emerald-600">₹{{ number_format($slip->net_salary, 2) }}</td>
                        <td class="py-4 px-6">
                            @if($slip->status === 'pending_deletion')
                                <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-red-100 text-red-700 border border-red-200 shadow-sm animate-pulse"><i class="fa-solid fa-trash-can mr-1"></i>Deletion Pending</span>
                                <div class="text-[9px] text-red-500 font-bold mt-1">Requested by {{ $slip->deletionRequester->name ?? 'Admin' }}</div>
                            @else
                                <span class="px-3 py-1.5 text-xs font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-200 shadow-sm"><i class="fa-solid fa-check-circle mr-1"></i>Generated</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('hrms.salary-slips.show', $slip->id) }}" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-lg text-xs transition shadow-sm" target="_blank">
                                    <i class="fa-solid fa-eye mr-1"></i> View
                                </a>
                                
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex space-x-2">
                                    <!-- Maker-Checker Deletion -->
                                    @if($slip->status === 'pending_deletion' && (auth()->user()->isCompanyFounder() || auth()->user()->isSaaSFounder()))
                                        <form action="{{ route('hrms.salary-slips.approve-delete', $slip->id) }}" method="POST" class="inline">
                                            @csrf <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs transition shadow-sm">Confirm Delete</button>
                                        </form>
                                        <form action="{{ route('hrms.salary-slips.approve-delete', $slip->id) }}" method="POST" class="inline">
                                            @csrf <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition">Reject Delete</button>
                                        </form>
                                    @elseif($slip->status !== 'pending_deletion' && (auth()->user()->isCompanyFounder() || auth()->user()->isSaaSFounder() || auth()->user()->isCompanyAdmin()))
                                        <form action="{{ route('hrms.salary-slips.delete', $slip->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this salary slip?');">
                                            @csrf
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 hover:bg-red-600 text-red-500 hover:text-white transition">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center opacity-50">
                                <i class="fa-solid fa-file-invoice-dollar text-5xl text-slate-300 mb-4"></i>
                                <span class="text-slate-500 font-bold">No salary slips generated yet.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
@if(auth()->user()->isCompanyAdmin() || auth()->user()->isCompanyFounder() || auth()->user()->isSaaSFounder())
<div id="payrollModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-extrabold text-slate-900">Generate Salary Slip</h3>
            <button onclick="document.getElementById('payrollModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form action="{{ route('hrms.salary-slips.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Employee</label>
                    <select name="user_id" required class="w-full border-slate-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach($staffUsers as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Month</label>
                    <input type="month" name="month" required value="{{ date('Y-m') }}" class="w-full border-slate-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Basic Salary (₹)</label>
                    <input type="number" name="basic_salary" required class="w-full border-slate-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="e.g. 25000">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Allowances (₹)</label>
                    <input type="number" name="allowances" value="0" class="w-full border-slate-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Commission Earned (₹)</label>
                    <input type="number" name="commission_earned" value="0" class="w-full border-slate-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deductions (₹)</label>
                    <input type="number" name="deductions" value="0" class="w-full border-slate-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition">Generate Salary Slip</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

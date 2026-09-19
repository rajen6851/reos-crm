@extends('layouts.reos')

@section('title', "Booking {$booking->booking_code} – REOS")

@section('content')
<div class="max-w-7xl mx-auto space-y-8 animate-fade-in-up">
    
    <!-- Hero Header / Action Bar -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-[#1e1b4b] to-purple-900 p-8 shadow-2xl text-white flex flex-col md:flex-row md:items-center justify-between gap-6 group">
        <!-- Decorative Background Blob -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 group-hover:opacity-50 transition-opacity duration-700"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 group-hover:opacity-50 transition-opacity duration-700"></div>

        <div class="relative z-10">
            <div class="flex items-center space-x-3 mb-3">
                <a href="{{ route('bookings.index') }}" class="text-xs font-semibold text-indigo-200 hover:text-white transition-colors bg-white/10 px-3 py-1.5 rounded-full backdrop-blur-md">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Directory
                </a>
                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-mono font-bold text-[11px] shadow-inner">
                    {{ $booking->booking_code }}
                </span>
            </div>
            <h1 class="text-3xl font-black tracking-tight mt-1 bg-clip-text text-transparent bg-gradient-to-r from-white to-indigo-200">
                Property Booking Record
            </h1>
            <p class="text-xs text-indigo-300 mt-2 flex items-center">
                <i class="fa-regular fa-clock mr-1.5"></i> Registered on {{ $booking->created_at->format('d M Y, h:i A') }}
            </p>
        </div>

        <div class="relative z-10 flex flex-wrap items-center gap-3">
            @php
                $statusColors = match($booking->status) {
                    'approved', 'confirmed' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/50',
                    'rejected' => 'bg-rose-500/20 text-rose-300 border-rose-500/50',
                    default => 'bg-amber-500/20 text-amber-300 border-amber-500/50'
                };
                $statusIcon = match($booking->status) {
                    'approved', 'confirmed' => 'fa-circle-check',
                    'rejected' => 'fa-circle-xmark',
                    default => 'fa-clock'
                };
            @endphp
            <span class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border backdrop-blur-sm shadow-sm flex items-center space-x-2 {{ $statusColors }}">
                <i class="fa-solid {{ $statusIcon }}"></i>
                <span>{{ strtoupper($booking->status) }}</span>
            </span>

            @php
                $canManage = auth()->user()->isCompanyAdmin() || auth()->user()->role?->slug === 'founder' || auth()->user()->isManager() || auth()->user()->can('manage-bookings');
            @endphp

            @if($canManage)
            <button onclick="document.getElementById('editBookingModal').classList.remove('hidden')" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5 flex items-center space-x-2 backdrop-blur-md cursor-pointer">
                <i class="fa-solid fa-pen"></i><span>Edit Details</span>
            </button>
            @endif

            <a href="{{ route('bookings.download-receipt', $booking->id) }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5 flex items-center space-x-2 backdrop-blur-md">
                <i class="fa-solid fa-file-pdf"></i><span>Receipt PDF</span>
            </a>

            @if($booking->status === 'pending_approval' && $canManage)
            <form method="POST" action="{{ route('bookings.approve', $booking->id) }}" class="inline">
                @csrf
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold text-xs rounded-xl transition-all duration-300 shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5 flex items-center space-x-2">
                    <i class="fa-solid fa-check-double"></i><span>Approve Booking</span>
                </button>
            </form>
            @endif

            @if(in_array($booking->status, ['confirmed', 'pending_approval']) && $canManage)
            <button onclick="document.getElementById('cancelBookingModal').classList.remove('hidden')" class="px-5 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-100 font-bold text-xs rounded-xl border border-rose-500/20 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5 flex items-center space-x-2 backdrop-blur-md cursor-pointer">
                <i class="fa-solid fa-ban"></i><span>Cancel Booking</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- LEFT 2 COLS: Financial Summary & Payments -->
        <div class="xl:col-span-2 space-y-8">
            
            <!-- Financial Metrics Strip -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Unit Price -->
                <div class="p-5 bg-white rounded-3xl border border-slate-100 shadow-lg shadow-slate-200/40 hover:shadow-xl hover:shadow-slate-200/60 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
                        <i class="fa-solid fa-tags text-6xl text-slate-900"></i>
                    </div>
                    <div class="relative z-10">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-tags text-indigo-500 mr-2"></i> Unit Final Price
                        </span>
                        <div class="text-2xl font-black font-mono text-slate-900 mt-2 tracking-tight">₹{{ number_format($unitPrice) }}</div>
                    </div>
                </div>

                <!-- Total Paid -->
                <div class="p-5 bg-white rounded-3xl border border-emerald-100 shadow-lg shadow-emerald-100/40 hover:shadow-xl hover:shadow-emerald-200/60 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
                        <i class="fa-solid fa-coins text-6xl text-emerald-900"></i>
                    </div>
                    <div class="relative z-10">
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-coins mr-2"></i> Total Paid
                        </span>
                        <div class="text-2xl font-black font-mono text-emerald-700 mt-2 tracking-tight">₹{{ number_format($totalPaid) }}</div>
                    </div>
                </div>

                <!-- Balance Remaining -->
                <div class="p-5 bg-white rounded-3xl border border-rose-100 shadow-lg shadow-rose-100/40 hover:shadow-xl hover:shadow-rose-200/60 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
                        <i class="fa-solid fa-scale-unbalanced text-6xl text-rose-900"></i>
                    </div>
                    <div class="relative z-10">
                        <span class="text-xs font-bold text-rose-600 uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-scale-unbalanced mr-2"></i> Balance Remaining
                        </span>
                        <div class="text-2xl font-black font-mono text-rose-700 mt-2 tracking-tight">₹{{ number_format($balanceRemaining) }}</div>
                    </div>
                </div>
            </div>

            <!-- Payment Ledger -->
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40">
                <div class="flex justify-between items-center border-b border-slate-100 pb-5 mb-5">
                    <h3 class="text-lg font-black text-slate-900 flex items-center">
                        <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mr-3 shadow-inner">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </span>
                        Payment Collection History
                    </h3>
                    @can('manage-commissions')
                    <button onclick="document.getElementById('recordPaymentModal').classList.remove('hidden')" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold rounded-xl transition-all duration-300 shadow-md shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 flex items-center space-x-1.5">
                        <i class="fa-solid fa-plus"></i>
                        <span>Record Payment</span>
                    </button>
                    @endcan
                </div>

                <div class="space-y-4">
                    <!-- Initial Token Payment -->
                    <div class="group p-5 bg-gradient-to-r from-slate-50 to-white border border-slate-200/60 hover:border-indigo-200 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold shadow-inner">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <div class="font-black text-slate-900 text-sm">Token Booking Payment</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-1 bg-slate-100 px-2 py-0.5 rounded-md inline-block">Date: {{ $booking->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                        <div class="text-left sm:text-right w-full sm:w-auto flex sm:flex-col justify-between sm:justify-center items-center sm:items-end border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                            <div class="font-black text-emerald-600 text-lg tracking-tight">₹{{ number_format($booking->booking_amount) }}</div>
                            <span class="text-[9px] px-2 py-1 rounded-md bg-emerald-100 text-emerald-700 font-black tracking-widest uppercase mt-1 border border-emerald-200 shadow-sm">CLEARED</span>
                        </div>
                    </div>

                    <!-- Subsequent Payments -->
                    @foreach($booking->payments as $payment)
                    <div class="group p-5 bg-gradient-to-r from-slate-50 to-white border border-slate-200/60 hover:border-indigo-200 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center font-bold shadow-inner border border-indigo-100">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <div class="font-black text-slate-900 text-sm flex items-center space-x-2">
                                    <span>Receipt #{{ $payment->receipt_number }}</span>
                                    <span class="text-[9px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded uppercase tracking-wider">{{ $payment->payment_method }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono mt-1 space-x-2 flex items-center">
                                    <span class="bg-slate-100 px-2 py-0.5 rounded-md">Ref: {{ $payment->transaction_reference ?? 'N/A' }}</span>
                                    <span class="bg-slate-100 px-2 py-0.5 rounded-md">Date: {{ $payment->payment_date?->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-left sm:text-right w-full sm:w-auto flex sm:flex-col justify-between sm:justify-center items-center sm:items-end border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0 gap-2">
                            <div class="font-black text-emerald-600 text-lg tracking-tight">₹{{ number_format($payment->amount) }}</div>
                            <a href="{{ route('payments.download-receipt', $payment->id) }}" class="text-[10px] text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white px-2 py-1 rounded-md font-bold transition-colors border border-indigo-100 flex items-center space-x-1">
                                <i class="fa-solid fa-download"></i><span>Receipt</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT 1 COL: Details Sidebar -->
        <div class="space-y-8">
            <!-- Customer Card -->
            <div class="bg-white p-1 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-indigo-600"></div>
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-black shadow-inner border border-indigo-200/50">
                            {{ strtoupper(substr($booking->lead->first_name ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Customer Info</h3>
                            <p class="text-xs text-slate-500 font-medium">Primary applicant details</p>
                        </div>
                    </div>
                    
                    <div class="space-y-5">
                        <div class="group/item">
                            <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Full Name</div>
                            <div class="font-bold text-slate-900 text-sm group-hover/item:text-indigo-600 transition-colors">{{ $booking->lead->first_name ?? '' }} {{ $booking->lead->last_name ?? '' }}</div>
                        </div>
                        <div class="group/item">
                            <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Contact Number</div>
                            <div class="font-mono font-bold text-slate-800 text-sm bg-slate-50 px-3 py-1.5 rounded-lg inline-block border border-slate-100">{{ $booking->lead->phone ?? 'N/A' }}</div>
                        </div>
                        <div class="group/item">
                            <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Email Address</div>
                            <div class="font-mono text-slate-700 text-xs truncate" title="{{ $booking->lead->email }}">{{ $booking->lead->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unit Specs Card -->
            <div class="bg-white p-1 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 to-teal-600"></div>
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-100 text-teal-600 flex items-center justify-center text-xl font-black shadow-inner border border-teal-200/50">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Unit Information</h3>
                            <p class="text-xs text-slate-500 font-medium">Property specifications</p>
                        </div>
                    </div>
                    
                    <div class="space-y-5">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Project</div>
                            <div class="font-black text-indigo-600 text-base">{{ $booking->project->name ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Unit Number</div>
                                <div class="font-mono font-bold text-slate-900">{{ $booking->unit->unit_number ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Unit Type</div>
                                <div class="font-bold text-slate-700">{{ $booking->unit->unit_type ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Tower</div>
                                <div class="font-bold text-slate-800">{{ $booking->unit->building->name ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Carpet Area</div>
                                <div class="font-mono text-slate-800"><span class="font-bold">{{ $booking->unit->carpet_area ?? '0' }}</span> <span class="text-[10px]">sqft</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div id="recordPaymentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('recordPaymentModal').classList.add('hidden')"></div>
        <div class="bg-white w-full max-w-md p-8 rounded-[2rem] space-y-6 border border-slate-200 shadow-2xl relative z-10 animate-fade-in-up">
            <div class="flex justify-between items-start border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">Record Installment</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Log a new payment for this booking</p>
                </div>
                <button onclick="document.getElementById('recordPaymentModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 transition-colors flex items-center justify-center font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('bookings.payment', $booking->id) }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[11px] uppercase tracking-wider text-slate-500 mb-1.5 font-bold">Payment Amount (₹) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-slate-400 font-mono font-bold">₹</span>
                        </div>
                        <input type="number" name="amount" required min="1" value="50000" class="w-full bg-slate-50 border border-slate-200 hover:border-indigo-300 rounded-xl pl-8 pr-4 py-3 text-slate-900 font-mono font-bold text-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] uppercase tracking-wider text-slate-500 mb-1.5 font-bold">Payment Method <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full bg-slate-50 border border-slate-200 hover:border-indigo-300 rounded-xl px-4 py-3 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="cheque">Cheque</option>
                        <option value="upi">UPI / Online</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] uppercase tracking-wider text-slate-500 mb-1.5 font-bold">Transaction Reference / Cheque No.</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. TXN987654321" class="w-full bg-slate-50 border border-slate-200 hover:border-indigo-300 rounded-xl px-4 py-3 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('recordPaymentModal').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:-translate-y-0.5 transition-all">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Edit Booking Modal -->
    <div id="editBookingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('editBookingModal').classList.add('hidden')"></div>
        <div class="bg-white w-full max-w-lg p-8 rounded-[2rem] space-y-6 border border-slate-200 shadow-2xl relative z-10 animate-fade-in-up">
            <div class="flex justify-between items-start border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">Edit Booking Details</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Update basic customer or property information</p>
                </div>
                <button onclick="document.getElementById('editBookingModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 transition-colors flex items-center justify-center font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('bookings.update', $booking->id) }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] uppercase tracking-wider text-slate-500 mb-1 font-bold">Customer Name</label>
                        <input type="text" name="customer_name" value="{{ $booking->customer_name }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] uppercase tracking-wider text-slate-500 mb-1 font-bold">Phone</label>
                        <input type="text" name="customer_phone" value="{{ $booking->customer_phone }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 text-sm focus:border-indigo-500 outline-none font-mono">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[11px] uppercase tracking-wider text-slate-500 mb-1 font-bold">Email</label>
                        <input type="email" name="customer_email" value="{{ $booking->customer_email }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 text-sm focus:border-indigo-500 outline-none font-mono">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-4">
                    <label class="block text-[11px] uppercase tracking-wider text-slate-500 mb-1 font-bold">Unit Identifier / Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="unit_identifier" required value="{{ $booking->unit_identifier }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 text-sm font-bold focus:border-indigo-500 outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">If the project doesn't have strict unit mapping, update the flat/tower identifier here.</p>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 mt-4">
                    <button type="button" onclick="document.getElementById('editBookingModal').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition-all">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Booking Modal -->
    <div id="cancelBookingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('cancelBookingModal').classList.add('hidden')"></div>
        <div class="bg-white w-full max-w-lg p-8 rounded-[2rem] space-y-6 border border-slate-200 shadow-2xl relative z-10 animate-fade-in-up">
            <div class="flex justify-between items-start border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-black text-rose-600 tracking-tight">Cancel Booking</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">This action will cancel the booking and release the unit</p>
                </div>
                <button onclick="document.getElementById('cancelBookingModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 transition-colors flex items-center justify-center font-bold">✕</button>
            </div>

            <form method="POST" action="{{ route('bookings.cancel', $booking->id) }}" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-[11px] uppercase tracking-wider text-slate-500 mb-1 font-bold">Reason for Cancellation <span class="text-rose-500">*</span></label>
                    <textarea name="cancellation_reason" required rows="3" placeholder="e.g. Customer defaulted on remaining payments after token." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 text-sm focus:border-rose-500 outline-none"></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">This reason will be recorded for audit purposes.</p>
                </div>

                <div class="bg-rose-50 border border-rose-100 rounded-xl p-4 mt-2">
                    <p class="text-xs text-rose-700"><strong>Warning:</strong> Cancelling this booking will immediately mark the unit as Available for other sales executives to book. The associated lead will be pushed back to Negotiation status.</p>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 mt-4">
                    <button type="button" onclick="document.getElementById('cancelBookingModal').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors">Go Back</button>
                    <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-rose-600/30 transition-all">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Optional extra animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out forwards;
    }
</style>
@endsection

@extends('layouts.reos')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold tracking-tight">Lead Distribution Engine âœ¨</h1>
            <p class="text-sm text-slate-500 mt-1">Manage and automate how incoming leads are distributed among your team.</p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('distribution-rules.create') }}" class="btn bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-all duration-200 flex items-center group">
                <i class="fas fa-plus w-4 h-4 fill-current opacity-70 shrink-0 mr-2 group-hover:scale-110 transition-transform"></i>
                <span class="hidden xs:block">Create New Rule</span>
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center shadow-sm">
        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600 mr-3">
            <i class="fas fa-check"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- Rules Table Card -->
    <div class="bg-white shadow-lg rounded-xl border border-slate-200 overflow-hidden relative">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full divide-y divide-slate-200">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50 font-semibold rounded-t-xl border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap text-left"><div class="font-bold">Priority</div></th>
                        <th class="px-6 py-4 whitespace-nowrap text-left"><div class="font-bold">Rule Name</div></th>
                        <th class="px-6 py-4 whitespace-nowrap text-left"><div class="font-bold">Conditions</div></th>
                        <th class="px-6 py-4 whitespace-nowrap text-left"><div class="font-bold">Method</div></th>
                        <th class="px-6 py-4 whitespace-nowrap text-center"><div class="font-bold">Status</div></th>
                        <th class="px-6 py-4 whitespace-nowrap text-right"><div class="font-bold">Actions</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    @forelse($rules as $rule)
                    <tr class="hover:bg-slate-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 border border-slate-200 text-slate-600 font-bold text-xs">
                                {{ $rule->priority }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $rule->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">Version {{ $rule->version }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <span class="inline-flex items-center text-xs font-medium text-slate-500">
                                    <i class="fas fa-building w-4 text-slate-400"></i>
                                    {{ $rule->project ? $rule->project->name : 'All Projects' }}
                                </span>
                                <span class="inline-flex items-center text-xs font-medium text-slate-500">
                                    <i class="fas fa-bullhorn w-4 text-slate-400"></i>
                                    {{ $rule->source ? $rule->source->name : 'All Sources' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($rule->distribution_method == 'percentage')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">
                                    <i class="fas fa-chart-pie mr-1"></i> Percentage
                                </span>
                            @elseif($rule->distribution_method == 'round_robin')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                    <i class="fas fa-sync-alt mr-1"></i> Round Robin
                                </span>
                            @elseif($rule->distribution_method == 'fixed_quantity')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    <i class="fas fa-boxes mr-1"></i> Fixed Quantity
                                </span>
                            @elseif($rule->distribution_method == 'performance')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 shadow-sm ring-1 ring-inset ring-amber-200">
                                    <i class="fas fa-bolt text-amber-500 mr-1 animate-pulse"></i> Performance AI
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $rule->distribution_method)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($rule->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('distribution-rules.edit', $rule->id) }}" class="p-2 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors group" title="Edit Rule">
                                    <i class="fas fa-pen-to-square group-hover:scale-110 transition-transform"></i>
                                </a>
                                <form action="{{ route('distribution-rules.destroy', $rule->id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors group" title="Delete Rule">
                                        <i class="fas fa-trash-alt group-hover:scale-110 transition-transform"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                <i class="fas fa-sitemap text-2xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-slate-900 mb-1">No Distribution Rules</h3>
                            <p class="text-sm text-slate-500 mb-6">Create your first rule to automate lead assignment.</p>
                            <a href="{{ route('distribution-rules.create') }}" class="btn bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-all">
                                <i class="fas fa-plus mr-2"></i> Create Rule
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

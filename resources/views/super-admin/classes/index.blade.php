@extends('layouts.app')
@section('page_title', 'Class Master')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-white">Class Master</h2>
        <p class="text-sm text-slate-400 mt-0.5">Manage all class definitions available for registration</p>
    </div>
    <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Add Class
    </a>
</div>
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800/60">
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Class Name</th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Code</th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Description</th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Registration Fee</th>
                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60">
            @forelse($classes as $class)
            <tr class="hover:bg-slate-800/20 transition-colors">
                <td class="px-6 py-4 font-semibold text-slate-200">{{ $class->name }}</td>
                <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-lg bg-indigo-600/10 text-indigo-400 text-xs font-mono font-semibold border border-indigo-500/20">{{ $class->code }}</span></td>
                <td class="px-6 py-4 text-slate-400 hidden md:table-cell">{{ $class->description ?? '—' }}</td>
                <td class="px-6 py-4 text-slate-200 font-semibold">₹{{ number_format($class->registration_fee, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    @if($class->status)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Active</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20"><span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.classes.edit', $class) }}" class="p-2 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.classes.toggle-status', $class) }}">
                            @csrf
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-amber-400 transition-all cursor-pointer" title="Toggle Status">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" onsubmit="return confirm('Delete this class?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-all cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-16 text-center text-slate-500">No classes found. <a href="{{ route('admin.classes.create') }}" class="text-indigo-400">Add one →</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

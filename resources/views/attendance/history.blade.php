@extends('layouts.app')
@section('page_title', 'Scan History')
@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-white">Scan Log & Session Stats</h2>
        <p class="text-sm text-slate-400 mt-0.5">Audit trail of all QR code scans and confirmations by you</p>
    </div>
    @if(\App\Models\Examination::where('status', 'Examination Ongoing')->exists())
    <a href="{{ route('attendance.scanner') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
        Open Scanner
    </a>
    @endif
</div>

{{-- Quick Stats Panel --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-3xl p-6 flex items-center justify-between shadow-lg">
        <div>
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Today's Present Marks</span>
            <span class="text-3xl font-extrabold text-white mt-1 block" id="scan-counter">{{ $totalScans }}</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800/60">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student & School</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Activity Action</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Device & User IP</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Scan Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4">
                        @if($log->student)
                            <div>
                                <p class="font-semibold text-slate-200">{{ $log->student->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $log->student->hall_ticket_number }} · {{ $log->student->school->name }}</p>
                            </div>
                        @else
                            <span class="text-xs text-rose-400 italic">Unknown/Corrupt Student Record</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($log->action === 'marked_present' || $log->action === 'mark_present')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Marked Present
                            </span>
                        @elseif($log->action === 'scan_success')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Verified Success
                            </span>
                        @elseif($log->action === 'scan_duplicate')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Duplicate Attempt
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Invalid Ticket
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-slate-400 hidden md:table-cell">
                        <p class="truncate max-w-xs text-xs font-mono" title="{{ $log->device_info }}">{{ $log->device_info }}</p>
                        <p class="text-[10px] text-slate-500 mt-0.5 font-mono">IP: {{ $log->ip_address }}</p>
                    </td>
                    <td class="px-6 py-4 text-right text-slate-400 font-medium">{{ $log->scan_time->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        No scans logged today.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-slate-800/60">{{ $logs->withQueryString()->links() }}</div>
    @endif
</div>
@endsection

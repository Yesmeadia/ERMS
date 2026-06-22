@extends('layouts.app')

@section('page_title', 'Activity & Audit Logs')

@section('content')
    <div class="mb-6">
        <p class="text-sm text-slate-400">Complete audit trail of all system activities.</p>
    </div>

    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-800/60">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date & Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $activity->id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-slate-300 font-medium">{{ $activity->causer?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-300">{{ $activity->description }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($activity->subject_type)
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700/60">
                                        {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                    </span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-slate-300 text-xs">{{ $activity->created_at->format('d M Y') }}</p>
                                    <p class="text-slate-500 text-xs">{{ $activity->created_at->format('h:i A') }}</p>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                                    stroke="currentColor" class="w-12 h-12 mx-auto text-slate-700 mb-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0112 3m0 0c2.917 0 5.747.294 8.5.862m-10.5 6h9m-9 3h9m-9 3h9m-9 3h9" />
                                </svg>
                                <p class="text-slate-500 font-medium">No activity logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($activities->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/60">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
@endsection
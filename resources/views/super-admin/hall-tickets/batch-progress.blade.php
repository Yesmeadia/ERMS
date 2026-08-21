@extends('layouts.app')

@section('page_title', 'Hall Ticket Generation Progress')

@section('content')
<div class="max-w-4xl mx-auto" x-data="batchProgress({
    batchId: {{ $batch->id }},
    statusUrl: '{{ route('admin.hall-tickets.batches.status', $batch) }}',
    initialStatus: '{{ $batch->status }}',
    totalStudents: {{ $batch->total_students }},
    completedStudents: {{ $batch->completed_students }},
    failedStudents: {{ $batch->failed_students }},
    totalParts: {{ $batch->total_parts }},
    completedParts: {{ $batch->completed_parts }},
    failedParts: {{ $batch->failed_parts }},
    progress: {{ $batch->progress_percentage }}
})">
    {{-- Header Breadcrumb & Back --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.hall-tickets.index') }}" class="hover:text-indigo-400 transition-colors">Hall Ticket Management</a>
                <span>/</span>
                <span class="text-slate-200">Batch #{{ $batch->id }}</span>
            </div>
            <h1 class="text-xl font-bold text-slate-100">Hall Ticket Bulk Generation (Admin)</h1>
            <p class="text-xs text-slate-400 mt-1">
                School: <span class="text-slate-200 font-semibold">{{ $batch->school->name ?? 'School' }}</span> · 
                Session: <span class="text-indigo-400 font-semibold">{{ $batch->examination->name ?? 'Examination' }}</span>
            </p>
        </div>
        <div>
            <a href="{{ route('admin.hall-tickets.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold rounded-xl border border-slate-700/60 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Back to Hall Tickets
            </a>
        </div>
    </div>

    {{-- Main Progress Card --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 mb-6 backdrop-blur-xl relative overflow-hidden">
        {{-- Status Glow Background --}}
        <div class="absolute -top-24 -right-24 w-48 h-48 rounded-full blur-3xl pointer-events-none"
             :class="{
                 'bg-indigo-600/10': status === 'processing' || status === 'pending',
                 'bg-emerald-600/10': status === 'completed' && failedParts === 0,
                 'bg-amber-600/10': status === 'completed_with_errors' || (status === 'completed' && failedParts > 0),
                 'bg-rose-600/10': status === 'failed'
             }"></div>

        {{-- Status Badge & Summary --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Batch Status</span>
                <div class="flex items-center gap-2.5 mt-1.5">
                    {{-- Status Pills --}}
                    <template x-if="status === 'pending'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span> Queued
                        </span>
                    </template>
                    <template x-if="status === 'processing'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                            <svg class="animate-spin -ml-0.5 mr-1 h-3 w-3 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Generating PDFs...
                        </span>
                    </template>
                    <template x-if="status === 'completed' && failedParts === 0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Completed & Ready
                        </span>
                    </template>
                    <template x-if="status === 'completed_with_errors' || (status === 'completed' && failedParts > 0)">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Partially Ready (Some Parts Failed)
                        </span>
                    </template>
                    <template x-if="status === 'failed'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span> Generation Failed
                        </span>
                    </template>

                    <span class="text-xs text-slate-400">
                        <span x-text="completedStudents"></span> of <span x-text="totalStudents"></span> students prepared
                    </span>
                </div>
            </div>

            {{-- Progress Counter Percentage --}}
            <div class="text-right">
                <span class="text-3xl font-extrabold text-white tracking-tight" x-text="progress + '%'"></span>
                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-semibold">Total Progress</p>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-slate-800/80 rounded-full h-3 mb-6 p-0.5 border border-slate-700/50 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 ease-out"
                 :class="{
                     'bg-gradient-to-r from-indigo-500 to-indigo-400': status === 'processing' || status === 'pending',
                     'bg-gradient-to-r from-emerald-500 to-emerald-400': status === 'completed' && failedParts === 0,
                     'bg-gradient-to-r from-amber-500 to-amber-400': status === 'completed_with_errors' || (status === 'completed' && failedParts > 0),
                     'bg-gradient-to-r from-rose-500 to-rose-400': status === 'failed'
                 }"
                 :style="`width: ${Math.max(progress, 3)}%`"></div>
        </div>

        {{-- Meta Stats Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-slate-800/60">
            <div class="bg-slate-800/40 rounded-xl p-3 border border-slate-800/40">
                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Total Students</span>
                <p class="text-base font-bold text-slate-200 mt-0.5" x-text="totalStudents"></p>
            </div>
            <div class="bg-slate-800/40 rounded-xl p-3 border border-slate-800/40">
                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">PDF Parts</span>
                <p class="text-base font-bold text-slate-200 mt-0.5">
                    <span x-text="completedParts"></span> / <span x-text="totalParts"></span>
                </p>
            </div>
            <div class="bg-slate-800/40 rounded-xl p-3 border border-slate-800/40">
                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Completed</span>
                <p class="text-base font-bold text-emerald-400 mt-0.5" x-text="completedStudents"></p>
            </div>
            <div class="bg-slate-800/40 rounded-xl p-3 border border-slate-800/40">
                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Failed</span>
                <p class="text-base font-bold text-rose-400 mt-0.5" x-text="failedStudents"></p>
            </div>
        </div>
    </div>

    {{-- PDF Parts Section --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider">Generated PDF Parts (Max 100 Students per File)</h3>
                <p class="text-xs text-slate-400 mt-0.5">Each part contains up to 100 Hall Tickets (1 page per student).</p>
            </div>
            <template x-if="failedParts > 0">
                <form method="POST" action="{{ route('admin.hall-tickets.batches.retry', $batch) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white text-xs font-semibold rounded-xl border border-amber-500/30 transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        Retry Failed Parts
                    </button>
                </form>
            </template>
        </div>

        <div class="space-y-3">
            <template x-for="part in parts" :key="part.id">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border transition-all"
                     :class="{
                         'bg-slate-800/40 border-slate-700/60': part.status === 'completed',
                         'bg-indigo-950/20 border-indigo-800/40': part.status === 'processing',
                         'bg-slate-800/20 border-slate-800/40 opacity-75': part.status === 'pending',
                         'bg-rose-950/20 border-rose-800/40': part.status === 'failed'
                     }">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0"
                             :class="{
                                 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': part.status === 'completed',
                                 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 animate-pulse': part.status === 'processing',
                                 'bg-slate-800 text-slate-400 border border-slate-700/60': part.status === 'pending',
                                 'bg-rose-500/10 text-rose-400 border border-rose-500/20': part.status === 'failed'
                             }">
                            <span x-text="'P' + part.part_number"></span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-semibold text-slate-200" x-text="`Hall Tickets - Part ${part.part_number}`"></h4>
                                <span class="text-xs text-slate-500" x-text="`(${part.total_students} Students)`"></span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                <span x-show="part.status === 'completed'" class="text-emerald-400">✓ Ready for download</span>
                                <span x-show="part.status === 'processing'" class="text-indigo-400">⚡ Generating PDF pages...</span>
                                <span x-show="part.status === 'pending'" class="text-slate-500">Queued in background...</span>
                                <span x-show="part.status === 'failed'" class="text-rose-400" x-text="part.error_message || 'Generation failed.'"></span>
                                <span x-show="part.file_size_formatted" class="text-slate-500 ml-1" x-text="`· ${part.file_size_formatted}`"></span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <template x-if="part.status === 'completed' && part.download_url">
                            <a :href="part.download_url"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-600/15 transition-all cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                <span x-text="`Download Part ${part.part_number}`"></span>
                            </a>
                        </template>
                        <template x-if="part.status !== 'completed'">
                            <span class="text-xs text-slate-500 italic px-3 py-1.5">Processing...</span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script @nonce>
function batchProgress(config) {
    return {
        batchId: config.batchId,
        statusUrl: config.statusUrl,
        status: config.initialStatus,
        totalStudents: config.totalStudents,
        completedStudents: config.completedStudents,
        failedStudents: config.failedStudents,
        totalParts: config.totalParts,
        completedParts: config.completedParts,
        failedParts: config.failedParts || 0,
        progress: config.progress,
        parts: @js($initialParts ?? []),
        pollInterval: null,

        init() {
            if (this.status === 'pending' || this.status === 'processing') {
                this.startPolling();
            }
        },

        startPolling() {
            this.pollInterval = setInterval(() => {
                this.checkStatus();
            }, 3500);
        },

        async checkStatus() {
            // Pause polling when tab is not active/visible to save server resources
            if (document.hidden) return;

            try {
                const response = await fetch(this.statusUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) return;

                const data = await response.json();
                this.status = data.status;
                this.totalStudents = data.total_students;
                this.completedStudents = data.completed_students;
                this.failedStudents = data.failed_students;
                this.totalParts = data.total_parts;
                this.completedParts = data.completed_parts;
                this.failedParts = data.failed_parts || 0;
                this.progress = data.progress;
                this.parts = data.parts;

                if (data.status === 'completed' || data.status === 'completed_with_errors' || data.status === 'failed' || data.status === 'expired') {
                    clearInterval(this.pollInterval);
                }
            } catch (err) {
                console.error('Batch status poll error:', err);
            }
        },

        destroy() {
            if (this.pollInterval) clearInterval(this.pollInterval);
        }
    }
}
</script>
@endsection

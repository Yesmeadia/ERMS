@extends('layouts.app')
@section('page_title', 'Examination Details')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.examinations.index') }}" class="p-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        </a>
        <h2 class="text-xl font-bold text-white">{{ $examination->name }}</h2>
        @php
            $statusColors = [
                'Draft' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                'Registration Started' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                'Registartion closed' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                'Examination Ongoing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                'result published' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            ];
        @endphp
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$examination->status] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20' }}">
            {{ $examination->status }}
        </span>
    </div>

    {{-- Details Card --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-8 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Examination Name</p>
                <p class="text-sm text-slate-200 font-medium">{{ $examination->name }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Academic Year</p>
                <p class="text-sm text-slate-200 font-medium">{{ $examination->academic_year }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Registration Start</p>
                <p class="text-sm text-slate-200 font-medium">{{ \Carbon\Carbon::parse($examination->registration_start_date)->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Registration End</p>
                <p class="text-sm text-slate-200 font-medium">{{ \Carbon\Carbon::parse($examination->registration_end_date)->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Hall Ticket Release</p>
                <p class="text-sm text-slate-200 font-medium">{{ \Carbon\Carbon::parse($examination->hall_ticket_release_date)->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Registrations</p>
                <p class="text-2xl font-bold text-indigo-400">{{ number_format($examination->students_count) }}</p>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.examinations.edit', $examination) }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm">Edit</a>
        @foreach(['Draft', 'Registration Started', 'Registartion closed', 'Examination Ongoing', 'result published'] as $s)
            @if($s !== $examination->status)
            <form method="POST" action="{{ route('admin.examinations.update-status', $examination) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $s }}">
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-slate-300 font-semibold px-5 py-2.5 rounded-xl transition-all text-sm cursor-pointer">Set {{ $s }}</button>
            </form>
            @endif
        @endforeach
        <form method="POST" action="{{ route('admin.examinations.destroy', $examination) }}" onsubmit="return confirm('Delete this examination session?')">
            @csrf @method('DELETE')
            <button type="submit" class="bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 font-semibold px-5 py-2.5 rounded-xl transition-all text-sm cursor-pointer">Delete</button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.auth')

@section('content')
<div>
    <!-- Title and Subtitle -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Check Exam Results</h1>
        <p class="text-sm text-slate-400 mt-1.5">Enter details below to access your marksheet</p>
    </div>

    <!-- Alert Banners -->
    @if (session('error'))
        <div class="mb-5 p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-rose-200 text-xs font-medium leading-relaxed">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-rose-950/40 border border-rose-800/40 text-rose-200 text-xs font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Results Verification Form -->
    <form method="POST" action="{{ route('results.check-submit') }}" class="space-y-5">
        @csrf
        
        <!-- Examination Dropdown -->
        <div>
            <label for="examination_id" class="block text-sm font-semibold text-slate-300 mb-1.5">Examination Session</label>
            <select id="examination_id" name="examination_id" required autofocus
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-650 focus:outline-none transition-all duration-200 text-sm">
                <option value="" class="bg-slate-900 text-slate-400">-- Select Examination --</option>
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" @selected(old('examination_id') == $exam->id) class="bg-slate-900 text-slate-200">{{ $exam->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Registration or Hall Ticket Number Input -->
        <div>
            <label for="search_number" class="block text-sm font-semibold text-slate-300 mb-1.5">Registration Number / Hall Ticket Number</label>
            <input id="search_number" type="text" name="search_number" value="{{ old('search_number') }}" required placeholder="e.g. 30051 or HT30051"
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-sm">
        </div>

        <!-- Date of Birth Input -->
        <div>
            <label for="dob" class="block text-sm font-semibold text-slate-300 mb-1.5">Candidate Date of Birth (DOB)</label>
            <input id="dob" type="date" name="dob" value="{{ old('dob') }}" required
                class="block w-full px-4 py-3 bg-slate-950/50 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none transition-all duration-200 text-sm">
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all duration-200 shadow-md shadow-indigo-600/10 flex items-center justify-center gap-2 cursor-pointer mt-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
            </svg>
            Retrieve Result Card
        </button>
    </form>
</div>
@endsection

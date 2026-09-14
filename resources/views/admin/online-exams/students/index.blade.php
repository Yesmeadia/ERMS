@extends('layouts.app')

@section('page_title', 'Eligible Students - ' . $exam->name)

@section('content')
<div class="space-y-6 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.online-exams.show', $exam) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                    &larr; Back to {{ $exam->name }}
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1">
                Manage Eligible Students
            </h1>
            <p class="text-sm text-slate-400 mt-0.5">
                Category: <span class="text-indigo-300 font-medium">{{ $exam->category->name }}</span> | 
                Capacity Constraint: <strong class="{{ $enrolledCount >= 200 ? 'text-rose-400' : 'text-emerald-400' }}">{{ $enrolledCount }}</strong> / 199 Maximum Allowed
            </p>
        </div>

        @if($enrolledCount >= 200)
            <div class="px-4 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold">
                Capacity Limit Reached ({{ $enrolledCount }} / 199). Remove students before publishing.
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- 1. Enrolled Students Table -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
            <h2 class="text-base font-semibold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                Currently Enrolled Students ({{ $enrolledCount }})
            </h2>
            <span class="text-xs text-slate-400">Enrolled: {{ $enrolledCount }} / 199 Max</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Reg No</th>
                        <th class="px-5 py-3.5 font-semibold">Student Name</th>
                        <th class="px-5 py-3.5 font-semibold">Institution / School</th>
                        <th class="px-5 py-3.5 font-semibold">Class</th>
                        <th class="px-5 py-3.5 font-semibold">Enrolled At</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($enrolledStudents as $item)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-3.5 font-mono font-bold text-white">
                                {{ $item->registration_number }}
                            </td>
                            <td class="px-5 py-3.5 text-white font-medium">
                                {{ $item->student->name }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">
                                {{ $item->student->school ? $item->student->school->name : 'N/A' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">
                                {{ $item->student->class ? $item->student->class->name : 'N/A' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $item->enrolled_at ? $item->enrolled_at->format('d M Y, H:i') : $item->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($exam->status === \App\Enums\ExamStatus::DRAFT)
                                    <form method="POST" action="{{ route('admin.online-exams.students.remove', [$exam, $item->student_id]) }}" 
                                          onsubmit="return confirm('Remove student {{ $item->student->name }} from this exam?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-medium">
                                            Remove
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-600">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                No students enrolled yet. Select students from the category pool below.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enrolledStudents->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $enrolledStudents->links() }}
            </div>
        @endif
    </div>

    <!-- 2. Available ERMS Students Pool -->
    @if($exam->status === \App\Enums\ExamStatus::DRAFT)
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-sm" x-data="{ selectAll: false }">
            <div class="p-5 border-b border-slate-800/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Available {{ $exam->category->name }} Students in ERMS
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Select and enroll students. Remaining capacity: <strong class="text-emerald-400">{{ max(0, 199 - $enrolledCount) }}</strong> slots.</p>
                </div>

                <!-- Filter for available students -->
                <form method="GET" action="{{ route('admin.online-exams.students.index', $exam) }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or reg no..."
                           class="bg-slate-950/80 border border-slate-700/80 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">

                    <select name="school_id" class="bg-slate-950/80 border border-slate-700/80 rounded-xl px-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="">All Schools</option>
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>
                                {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700">
                        Filter
                    </button>
                </form>
            </div>

            @if($availableStudents->count() > 0)
                <form method="POST" action="{{ route('admin.online-exams.students.enroll', $exam) }}">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-300">
                            <thead class="bg-slate-950/60 text-xs uppercase text-slate-400 border-b border-slate-800">
                                <tr>
                                    <th class="px-5 py-3.5 w-12 text-center">
                                        <input type="checkbox" x-model="selectAll" 
                                               @change="document.querySelectorAll('.student-enroll-checkbox').forEach(c => c.checked = selectAll)"
                                               class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-5 py-3.5 font-semibold">Reg No</th>
                                    <th class="px-5 py-3.5 font-semibold">Student Name</th>
                                    <th class="px-5 py-3.5 font-semibold">Date of Birth</th>
                                    <th class="px-5 py-3.5 font-semibold">Institution / School</th>
                                    <th class="px-5 py-3.5 font-semibold">Class</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach($availableStudents as $student)
                                    <tr class="hover:bg-slate-800/30 transition-colors">
                                        <td class="px-5 py-3.5 text-center">
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-enroll-checkbox w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-5 py-3.5 font-mono font-bold text-white">
                                            {{ $student->registration_number }}
                                        </td>
                                        <td class="px-5 py-3.5 text-white font-medium">
                                            {{ $student->name }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-slate-400">
                                            {{ $student->dob ? $student->dob->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-slate-400">
                                            {{ $student->school ? $student->school->name : 'N/A' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-slate-400">
                                            {{ $student->class ? $student->class->name : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                        <div class="text-xs text-slate-400">
                            {{ $availableStudents->links() }}
                        </div>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-all">
                            Enroll Selected Students
                        </button>
                    </div>
                </form>
            @else
                <div class="p-8 text-center text-slate-500">
                    No matching unenrolled students found for category {{ $exam->category->name }}.
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

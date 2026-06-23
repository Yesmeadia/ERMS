<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Statement of Marks - {{ $student->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .cert-border {
            border: 8px double #334155;
        }
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-bg-slate-950 {
                background-color: white !important;
            }
            .print-card {
                background: white !important;
                border: 2px solid #000000 !important;
                box-shadow: none !important;
                color: black !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 1.5rem !important;
            }
            .cert-border {
                border: 4px double #000000 !important;
            }
            .print-text-white {
                color: #000000 !important;
            }
            .print-text-dark {
                color: #111827 !important;
            }
            .print-text-muted {
                color: #374151 !important;
            }
            .print-bg-light {
                background-color: #f3f4f6 !important;
            }
            .print-bg-transparent {
                background-color: transparent !important;
            }
            .print-border-slate {
                border-color: #9ca3af !important;
            }
            .print-badge-pass {
                border: 2px solid #10b981 !important;
                color: #047857 !important;
                background-color: #ecfdf5 !important;
                box-shadow: none !important;
            }
            .print-badge-fail {
                border: 2px solid #f43f5e !important;
                color: #be123c !important;
                background-color: #fff1f2 !important;
                box-shadow: none !important;
            }
            .print-badge-amber {
                border: 2px solid #f59e0b !important;
                color: #b45309 !important;
                background-color: #fef3c7 !important;
                box-shadow: none !important;
            }
            @page {
                size: A4;
                margin: 10mm;
            }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-10 px-4 flex flex-col justify-between relative overflow-x-hidden print-bg-slate-950">
    <!-- Neon Orbs Decorators (Hidden on print) -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl pointer-events-none no-print"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none no-print"></div>

    <div class="max-w-4xl w-full mx-auto my-auto print-card">
        <!-- Main Certificate Card -->
        <div class="bg-slate-900/40 border border-slate-800/80 rounded-3xl p-6 md:p-10 shadow-2xl relative cert-border print-card">
            
            <!-- Certificate Header -->
            <div class="text-center border-b border-slate-800/80 pb-6 mb-8 print-border-slate">
                <div class="flex justify-center mb-4">
                    <img src="{{ asset('icon.png') }}" alt="ERMS Logo" class="w-16 h-16 rounded-xl object-contain filter drop-shadow-[0_0_15px_rgba(99,102,241,0.2)]">
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white print-text-dark">YES INDIA FOUNDATION</h1>
                <p class="text-xs uppercase font-bold tracking-widest text-indigo-400 mt-1">Examination Board Portal</p>
                <h2 class="text-sm font-semibold text-slate-300 mt-2 uppercase tracking-wide print-text-muted">Official Statement of Marks</h2>
            </div>

            <!-- Candidate Details Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 items-start">
                <!-- Info Grid -->
                <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Candidate Name</span>
                        <strong class="text-slate-100 text-base print-text-dark">{{ $student->name }}</strong>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Registration Number</span>
                        <strong class="text-slate-200 font-mono text-base print-text-dark">{{ $student->registration_number }}</strong>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Hall Ticket Number</span>
                        <strong class="text-slate-200 font-mono text-base print-text-dark">{{ $student->hall_ticket_number ?? 'N/A' }}</strong>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Date of Birth (DOB)</span>
                        <strong class="text-slate-200 text-base print-text-dark">{{ $student->dob->format('d M Y') }}</strong>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Class & Category</span>
                        <strong class="text-slate-200 print-text-dark">{{ $student->class->name }} ({{ $student->category->name }})</strong>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Examination Session</span>
                        <strong class="text-slate-200 print-text-dark">{{ $student->examination->name }} ({{ $student->examination->academic_year }})</strong>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">School Name</span>
                        <strong class="text-slate-200 print-text-dark leading-relaxed">{{ $student->school->name }}</strong>
                    </div>
                </div>

                <!-- Photograph Container -->
                <div class="flex justify-center md:justify-end md:col-span-1">
                    <div class="w-28 h-32 rounded-xl overflow-hidden border border-slate-800 p-1.5 bg-slate-950/40 print-border-slate">
                        <img src="{{ $student->photo_url }}" alt="Candidate Photo" class="w-full h-full object-cover rounded-lg">
                    </div>
                </div>
            </div>

            <!-- Subject Scores Table -->
            <div class="border border-slate-800/80 rounded-2xl overflow-hidden mb-8 print-border-slate">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-slate-950/40 border-b border-slate-800/80 print-bg-light print-border-slate">
                            <th class="px-5 py-3 font-semibold text-slate-400 uppercase tracking-wider print-text-dark">Subject Details</th>
                            <th class="px-5 py-3 font-semibold text-slate-400 uppercase tracking-wider text-center print-text-dark w-36">Marks Obtained</th>
                            <th class="px-5 py-3 font-semibold text-slate-400 uppercase tracking-wider text-center print-text-dark w-36">Max Marks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/60 print-border-slate">
                        @if($result->subject_marks && count($result->subject_marks) > 0)
                            @foreach($result->subject_marks as $subjectName => $data)
                                <tr class="hover:bg-slate-900/10 transition-colors print-bg-transparent">
                                    <td class="px-5 py-3 font-medium text-slate-200 print-text-dark">{{ $subjectName }}</td>
                                    <td class="px-5 py-3 text-center text-slate-200 font-mono font-semibold print-text-dark">{{ $data['marks'] }}</td>
                                    <td class="px-5 py-3 text-center text-slate-400 font-mono print-text-muted">{{ $data['max'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            {{-- Fallback: If no subject-specific details are added, just show the aggregate line item --}}
                            <tr class="hover:bg-slate-900/10 transition-colors print-bg-transparent">
                                <td class="px-5 py-3 font-medium text-slate-200 print-text-dark">Aggregate Exam Marks</td>
                                <td class="px-5 py-3 text-center text-slate-200 font-mono font-semibold print-text-dark">{{ $result->marks_obtained }}</td>
                                <td class="px-5 py-3 text-center text-slate-400 font-mono print-text-muted">{{ $result->max_marks }}</td>
                            </tr>
                        @endif
                        
                        <!-- Totals Row -->
                        <tr class="bg-slate-950/20 border-t border-slate-800 font-bold print-bg-light print-border-slate">
                            <td class="px-5 py-4 text-slate-200 print-text-dark uppercase tracking-wider text-xs">Total Aggregate Marks</td>
                            <td class="px-5 py-4 text-center text-slate-100 font-mono text-base print-text-dark">{{ $result->marks_obtained }}</td>
                            <td class="px-5 py-4 text-center text-slate-400 font-mono print-text-muted">{{ $result->max_marks }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Grade & Summary Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10 items-center">
                <!-- Percentage & Grade Box -->
                <div class="sm:col-span-2 grid grid-cols-2 gap-4">
                    <div class="bg-slate-950/30 border border-slate-850 p-4 rounded-2xl flex flex-col justify-center print-bg-light print-border-slate">
                        <span class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Percentage</span>
                        <strong class="text-xl font-bold text-slate-200 font-mono print-text-dark mt-1">{{ $result->percentage }}%</strong>
                    </div>
                    <div class="bg-slate-950/30 border border-slate-850 p-4 rounded-2xl flex flex-col justify-center print-bg-light print-border-slate">
                        <span class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Final Grade</span>
                        <strong class="text-xl font-bold text-indigo-400 print-text-dark mt-1">{{ $result->grade }}</strong>
                    </div>
                </div>

                <!-- Status Badge Banner -->
                <div class="flex justify-center sm:justify-end">
                    @php
                        $resStatus = $result->status;
                        $badgeClass = 'border-indigo-500/20 text-indigo-400 bg-indigo-500/10 shadow-[0_0_20px_rgba(99,102,241,0.08)] print-badge-amber';
                        if ($resStatus === 'Pass') {
                            $badgeClass = 'border-emerald-500/20 text-emerald-400 bg-emerald-500/10 shadow-[0_0_20px_rgba(16,185,129,0.08)] print-badge-pass';
                        } elseif ($resStatus === 'Fail') {
                            $badgeClass = 'border-rose-500/20 text-rose-400 bg-rose-500/10 shadow-[0_0_20px_rgba(244,63,94,0.08)] print-badge-fail';
                        } elseif ($resStatus === 'Absent' || $resStatus === 'Withheld') {
                            $badgeClass = 'border-amber-500/20 text-amber-400 bg-amber-500/10 shadow-[0_0_20px_rgba(245,158,11,0.08)] print-badge-amber';
                        }
                    @endphp
                    <div class="w-full border-2 rounded-2xl py-5 text-center font-extrabold text-2xl uppercase tracking-widest leading-none {{ $badgeClass }}">
                        {{ $resStatus }}
                    </div>
                </div>
            </div>

            <!-- Signatures Layout -->
            <div class="grid grid-cols-2 gap-8 border-t border-slate-800/80 pt-10 mt-10 print-border-slate text-center text-xs">
                <div>
                    <div class="h-10"></div> <!-- Sig spacing -->
                    <div class="w-36 mx-auto border-b border-slate-700/60 print-border-slate mb-1.5"></div>
                    <span class="block text-slate-500 font-semibold uppercase tracking-wider">Controller of Examinations</span>
                    <span class="block text-[10px] text-slate-600 mt-0.5">Yes India Foundation</span>
                </div>
                <div>
                    <div class="h-10"></div> <!-- Sig spacing -->
                    <div class="w-36 mx-auto border-b border-slate-700/60 print-border-slate mb-1.5"></div>
                    <span class="block text-slate-500 font-semibold uppercase tracking-wider">Board President</span>
                    <span class="block text-[10px] text-slate-600 mt-0.5">Yes India Foundation</span>
                </div>
            </div>
            
        </div>

        <!-- Print Actions Bar (Hidden on print) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8 px-2 no-print">
            <a href="{{ route('results.check-form') }}" class="text-sm font-semibold text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Check Another Result
            </a>

            <button type="button" onclick="window.print()" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 flex items-center gap-2 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0a2.25 2.25 0 0 1-2.25 2.25H8.59a2.25 2.25 0 0 1-2.25-2.25M17.66 18c.067-.179.1-.368.1-.562V12h1.5A2.25 2.25 0 0 0 17 9.75H7A2.25 2.25 0 0 0 4.75 12v5.438c0 .194.033.383.1.562m12.8 0H4.85M16.5 9.75V4.875c0-.621-.504-1.125-1.125-1.125h-6.75a1.125 1.125 0 0 0-1.125 1.125V9.75M8.25 12.5h.008v.008H8.25v-.008Zm3.75 0h.008v.008H12v-.008Z" />
                </svg>
                Print Statement of Marks
            </button>
        </div>
    </div>
</body>
</html>

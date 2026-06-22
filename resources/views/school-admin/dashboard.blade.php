@extends('layouts.app')

@section('page_title', 'School Admin Dashboard')

@section('content')
    <div
        class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950/30 border border-slate-800/60 rounded-2xl p-6 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-lg shadow-slate-950/20">
        <!-- Floating decorative glowing background circle -->
        <div class="absolute -right-16 -top-16 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none">
        </div>

        <div class="flex items-center gap-4 relative z-10">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-bold text-white tracking-tight">{{ $school->name }}</h2>
                    <span
                        class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 uppercase tracking-wider">
                        Code: {{ $school->code }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 mt-1 leading-relaxed font-normal">Overview of student examination
                    registrations for your school.</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
        @php
            $statCards = [
                ['label' => 'Registered Students', 'value' => $stats['total_registered'], 'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z', 'color' => 'sky'],
                ['label' => 'Approved', 'value' => $stats['approved'], 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald'],
                [
                    'label' => 'Hall Tickets Issued',
                    'value' => $stats['hall_tickets_available'],
                    'icon' => null,
                    'paths' => [
                        'M4 8.25A2.25 2.25 0 016.25 6h11.5A2.25 2.25 0 0120 8.25V10a2 2 0 010 4v1.75A2.25 2.25 0 0117.75 18H6.25A2.25 2.25 0 014 15.75V14a2 2 0 010-4V8.25z',
                        'M12 6v12',
                    ],
                    'color' => 'purple'
                ],
                ['label' => 'Present Students', 'value' => $stats['present_students'], 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald'],
                ['label' => 'Absent Students', 'value' => $stats['absent_students'], 'icon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'rose'],
                ['label' => 'Attendance Rate', 'value' => $stats['attendance_percentage'] . '%', 'icon' => 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605', 'color' => 'indigo'],
            ];
            $colorMap = [
                'sky' => 'bg-sky-600/10 border-sky-500/20 text-sky-400',
                'indigo' => 'bg-indigo-600/10 border-indigo-500/20 text-indigo-400',
                'emerald' => 'bg-emerald-600/10 border-emerald-500/20 text-emerald-400',
                'rose' => 'bg-rose-600/10 border-rose-500/20 text-rose-400',
                'purple' => 'bg-purple-600/10 border-purple-500/20 text-purple-400',
            ];
        @endphp
        @foreach($statCards as $card)
            <div
                class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4 hover:border-slate-700/60 transition-all shadow-md">
                <div
                    class="flex items-center justify-center w-12 h-12 rounded-xl border {{ $colorMap[$card['color']] }} shrink-0">
                    @if(!empty($card['paths']))
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5">
                            @foreach($card['paths'] as $path)
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                            @endforeach
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">
                        {{ is_numeric($card['value']) ? number_format($card['value']) : $card['value'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $card['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Registration Progress --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Registration Trend (Last 10 Days)</h3>
            @if($registrationTrend->isEmpty())
                <div class="flex flex-col items-center justify-center h-[220px] text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                        stroke="currentColor" class="w-12 h-12 mb-2 text-slate-700">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                    <p class="text-sm font-medium">No registrations recorded in the last 10 days</p>
                </div>
            @else
                <div id="registrationTrendChart"></div>
            @endif
        </div>
        {{-- Approval Status Distribution --}}
        <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Approval Status Distribution</h3>
            @if(array_sum($statusDistribution) === 0)
                <div class="flex flex-col items-center justify-center h-[220px] text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                        stroke="currentColor" class="w-12 h-12 mb-2 text-slate-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                    </svg>
                    <p class="text-sm font-medium">No student data to display distribution</p>
                </div>
            @else
                <div id="statusDistributionChart"></div>
            @endif
        </div>
    </div>

    {{-- Quick Links / Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
            class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 hover:border-slate-700/60 transition-all flex flex-col justify-between">
            <div>
                <h4 class="text-sm font-semibold text-slate-200 mb-2">Register Student</h4>
                <p class="text-xs text-slate-400 leading-relaxed mb-4">Add a new student profile and prepare their draft
                    registration for active examination sessions.</p>
            </div>
            <a href="{{ route('school.students.create') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer">
                Register Candidate →
            </a>
        </div>

        <div
            class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 hover:border-slate-700/60 transition-all flex flex-col justify-between">
            <div>
                <h4 class="text-sm font-semibold text-slate-200 mb-2">Download Hall Tickets</h4>
                <p class="text-xs text-slate-400 leading-relaxed mb-4">Retrieve generated examination hall tickets for
                    verified and approved candidates.</p>
            </div>
            <a href="{{ route('school.hall-tickets.index') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer">
                Access Hall Tickets →
            </a>
        </div>

        <div
            class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 hover:border-slate-700/60 transition-all flex flex-col justify-between">
            <div>
                <h4 class="text-sm font-semibold text-slate-200 mb-2">School Reports</h4>
                <p class="text-xs text-slate-400 leading-relaxed mb-4">Generate and export registered, submitted, approved,
                    and rejected candidate lists to Excel or PDF.</p>
            </div>
            <a href="{{ route('school.reports.index') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer">
                View Analytics Reports →
            </a>
        </div>
    </div>

    @push('scripts')
        @if(!$registrationTrend->isEmpty() || array_sum($statusDistribution) > 0)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const baseOptions = {
                        chart: { background: 'transparent', toolbar: { show: false } },
                        theme: { mode: 'dark' },
                        grid: { borderColor: '#1e293b' },
                        colors: ['#6366f1', '#22d3ee', '#10b981', '#f43f5e', '#a855f7', '#64748b'],
                    };

                    @if(!$registrationTrend->isEmpty())
                        // Registration Progress Trend Chart
                        const trendDates = @json($registrationTrend->pluck('date'));
                        const trendCounts = @json($registrationTrend->pluck('count'));
                        new ApexCharts(document.querySelector('#registrationTrendChart'), {
                            ...baseOptions,
                            chart: { ...baseOptions.chart, type: 'area', height: 220 },
                            series: [{ name: 'Registrations', data: trendCounts }],
                            xaxis: { categories: trendDates, labels: { style: { colors: '#64748b', fontSize: '11px' } } },
                            yaxis: { labels: { style: { colors: '#64748b' } }, min: 0 },
                            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
                            stroke: { curve: 'smooth', width: 2 },
                            dataLabels: { enabled: false },
                        }).render();
                    @endif

                        @if(array_sum($statusDistribution) > 0)
                            // Status Distribution Chart
                            const statusLabels = @json(array_keys($statusDistribution));
                            const statusValues = @json(array_values($statusDistribution));
                            new ApexCharts(document.querySelector('#statusDistributionChart'), {
                                ...baseOptions,
                                chart: { ...baseOptions.chart, type: 'donut', height: 220 },
                                series: statusValues,
                                labels: statusLabels,
                                legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
                                plotOptions: { pie: { donut: { size: '60%' } } },
                                dataLabels: { style: { fontSize: '11px' } },
                            }).render();
                        @endif
                                    });
            </script>
        @endif
    @endpush
@endsection
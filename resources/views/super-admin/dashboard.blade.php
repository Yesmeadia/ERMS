@extends('layouts.app')

@section('page_title', 'Super Admin Dashboard')

@section('content')
{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
    @php
    $statCards = [
        ['label' => 'Total Schools', 'value' => $stats['total_schools'], 'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h18v18H3V3z', 'color' => 'indigo'],
        ['label' => 'Total Students', 'value' => $stats['total_students'], 'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z', 'color' => 'sky'],
        ['label' => 'Pending Verification', 'value' => $stats['pending_verification'], 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z', 'color' => 'amber'],
        ['label' => 'Approved', 'value' => $stats['approved_registrations'], 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald'],
        ['label' => 'Rejected', 'value' => $stats['rejected_registrations'], 'icon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'rose'],
        ['label' => 'Hall Tickets Issued', 'value' => $stats['hall_tickets_issued'], 'icon' => null, 'paths' => [
            'M4 8.25A2.25 2.25 0 016.25 6h11.5A2.25 2.25 0 0120 8.25V10a2 2 0 010 4v1.75A2.25 2.25 0 0117.75 18H6.25A2.25 2.25 0 014 15.75V14a2 2 0 010-4V8.25z',
            'M12 6v12',
        ], 'color' => 'purple'],
        ['label' => 'Present Today', 'value' => $stats['total_present'], 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald'],
        ['label' => 'Absent Today', 'value' => $stats['total_absent'], 'icon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'rose'],
        ['label' => 'Attendance Rate', 'value' => $stats['attendance_percentage'] . '%', 'icon' => 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605', 'color' => 'indigo'],
    ];
    $colorMap = [
        'indigo' => 'bg-indigo-600/10 border-indigo-500/20 text-indigo-400',
        'sky' => 'bg-sky-600/10 border-sky-500/20 text-sky-400',
        'amber' => 'bg-amber-600/10 border-amber-500/20 text-amber-400',
        'emerald' => 'bg-emerald-600/10 border-emerald-500/20 text-emerald-400',
        'rose' => 'bg-rose-600/10 border-rose-500/20 text-rose-400',
        'purple' => 'bg-purple-600/10 border-purple-500/20 text-purple-400',
        'teal' => 'bg-teal-600/10 border-teal-500/20 text-teal-400',
        'pink' => 'bg-pink-600/10 border-pink-500/20 text-pink-400',
    ];
    @endphp
    @foreach($statCards as $card)
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 flex items-center gap-4 hover:border-slate-700/60 transition-all shadow-md">
        <div class="flex items-center justify-center w-12 h-12 rounded-xl border {{ $colorMap[$card['color']] }} shrink-0">
            @if(!empty($card['paths']))
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    @foreach($card['paths'] as $path)
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                    @endforeach
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                </svg>
            @endif
        </div>
        <div>
            <p class="text-2xl font-bold text-white">{{ is_numeric($card['value']) ? number_format($card['value']) : $card['value'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Registration Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Registration Trend --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 shadow-md">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Registration Trend</h3>
        <div id="registrationTrendChart"></div>
    </div>
    {{-- School-wise Registration --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 shadow-md">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">School-wise Registrations (Top 10)</h3>
        <div id="schoolWiseChart"></div>
    </div>
</div>

{{-- Attendance Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- School-wise Attendance --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 shadow-md">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">School-wise Attendance (Top 10)</h3>
        <div id="schoolAttendanceChart"></div>
    </div>
    {{-- Category-wise Attendance --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 shadow-md">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Category-wise Attendance</h3>
        <div id="categoryAttendanceChart"></div>
    </div>
    {{-- Gender-wise Attendance --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 shadow-md">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Gender-wise Attendance</h3>
        <div id="genderAttendanceChart"></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Class-wise --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 shadow-md">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Class-wise Registrations</h3>
        <div id="classWiseChart"></div>
    </div>
    {{-- Category-wise --}}
    <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 shadow-md">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Category-wise Registrations</h3>
        <div id="categoryWiseChart"></div>
    </div>
</div>

{{-- Recent Activities --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-6 shadow-md">
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-sm font-semibold text-slate-300">Recent Activity</h3>
        <a href="{{ route('admin.activity-logs') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">View all →</a>
    </div>
    @forelse($recentActivities as $activity)
    <div class="flex items-start gap-3 py-3 border-b border-slate-800/60 last:border-0">
        <div class="w-8 h-8 rounded-full bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0 text-xs font-bold">
            {{ mb_substr($activity->causer?->name ?? 'S', 0, 1) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm text-slate-300 truncate">{{ $activity->description }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ $activity->causer?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}</p>
        </div>
    </div>
    @empty
    <p class="text-slate-500 text-sm text-center py-6">No recent activity</p>
    @endforelse
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseOptions = {
        chart: { background: 'transparent', toolbar: { show: false } },
        theme: { mode: 'dark' },
        grid: { borderColor: '#1e293b' },
        colors: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#22d3ee', '#a855f7'],
    };

    // Registration Trend
    const trendDates = @json($registrationTrend->pluck('date'));
    const trendCounts = @json($registrationTrend->pluck('count'));
    new ApexCharts(document.querySelector('#registrationTrendChart'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'area', height: 220 },
        series: [{ name: 'Registrations', data: trendCounts }],
        xaxis: { categories: trendDates, labels: { style: { colors: '#64748b', fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: '#64748b' } } },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
    }).render();

    // School-wise
    const schoolNames = @json($schoolWise->pluck('school_name'));
    const schoolCounts = @json($schoolWise->pluck('count'));
    new ApexCharts(document.querySelector('#schoolWiseChart'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'bar', height: 220 },
        series: [{ name: 'Students', data: schoolCounts }],
        xaxis: { categories: schoolNames, labels: { show: false } },
        yaxis: { labels: { style: { colors: '#64748b' } } },
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
        dataLabels: { enabled: false },
    }).render();

    // School Attendance Chart
    const schoolAttendNames = @json($schoolAttendance->pluck('school_name'));
    const schoolAttendTotals = @json($schoolAttendance->pluck('total_issued'));
    const schoolAttendPresents = @json($schoolAttendance->pluck('present_count'));
    new ApexCharts(document.querySelector('#schoolAttendanceChart'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'bar', height: 220 },
        series: [
            { name: 'Tickets Issued', data: schoolAttendTotals },
            { name: 'Present Today', data: schoolAttendPresents }
        ],
        xaxis: { categories: schoolAttendNames, labels: { show: false } },
        yaxis: { labels: { style: { colors: '#64748b' } } },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        dataLabels: { enabled: false },
    }).render();

    // Category Attendance Chart
    const catAttendNames = @json($categoryAttendance->pluck('category_name'));
    const catAttendTotals = @json($categoryAttendance->pluck('total_issued'));
    const catAttendPresents = @json($categoryAttendance->pluck('present_count'));
    new ApexCharts(document.querySelector('#categoryAttendanceChart'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'bar', height: 220 },
        series: [
            { name: 'Tickets Issued', data: catAttendTotals },
            { name: 'Present Today', data: catAttendPresents }
        ],
        xaxis: { categories: catAttendNames, labels: { style: { colors: '#64748b', fontSize: '9px' } } },
        yaxis: { labels: { style: { colors: '#64748b' } } },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        dataLabels: { enabled: false },
    }).render();

    // Gender Attendance Chart
    const genderLabels = @json($genderAttendance->pluck('gender'));
    const genderTotals = @json($genderAttendance->pluck('total_issued'));
    const genderPresents = @json($genderAttendance->pluck('present_count'));
    new ApexCharts(document.querySelector('#genderAttendanceChart'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'bar', height: 220 },
        series: [
            { name: 'Tickets Issued', data: genderTotals },
            { name: 'Present Today', data: genderPresents }
        ],
        xaxis: { categories: genderLabels, labels: { style: { colors: '#64748b', fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: '#64748b' } } },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
        dataLabels: { enabled: false },
    }).render();

    // Class-wise
    const classNames = @json($classWise->pluck('class_name'));
    const classCounts = @json($classWise->pluck('count'));
    new ApexCharts(document.querySelector('#classWiseChart'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'donut', height: 220 },
        series: classCounts,
        labels: classNames,
        legend: { labels: { colors: '#94a3b8' } },
        plotOptions: { pie: { donut: { size: '60%' } } },
        dataLabels: { style: { fontSize: '11px' } },
    }).render();

    // Category-wise
    const catNames = @json($categoryWise->pluck('category_name'));
    const catCounts = @json($categoryWise->pluck('count'));
    new ApexCharts(document.querySelector('#categoryWiseChart'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'bar', height: 220 },
        series: [{ name: 'Students', data: catCounts }],
        xaxis: { categories: catNames, labels: { style: { colors: '#64748b', fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: '#64748b' } } },
        plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '50%' } },
        dataLabels: { enabled: false },
    }).render();
});
</script>
@endpush
@endsection

@extends('layouts.app')

@section('page_title', 'Reports')

@section('content')
<div class="mb-6">
    <p class="text-sm text-slate-400">Generate and export detailed registration reports.</p>
</div>

{{-- Report Type Selector & Filters --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5 mb-6">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Report Type</label>
            <select name="type" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="school_wise" {{ $reportType === 'school_wise' ? 'selected' : '' }}>School-wise Registration</option>
                <option value="class_wise" {{ $reportType === 'class_wise' ? 'selected' : '' }}>Class-wise Registration</option>
                <option value="category_wise" {{ $reportType === 'category_wise' ? 'selected' : '' }}>Category-wise Registration</option>
                <option value="examination_wise" {{ $reportType === 'examination_wise' ? 'selected' : '' }}>Examination-wise Registration</option>
                <option value="approved" {{ $reportType === 'approved' ? 'selected' : '' }}>Approved Students</option>
                <option value="rejected" {{ $reportType === 'rejected' ? 'selected' : '' }}>Rejected Students</option>
                <option value="hall_ticket" {{ $reportType === 'hall_ticket' ? 'selected' : '' }}>Hall Ticket Issued</option>
                <option value="attendance" {{ $reportType === 'attendance' ? 'selected' : '' }}>Attendance Report</option>
                <option value="attendance_category" {{ $reportType === 'attendance_category' ? 'selected' : '' }}>Attendance by Category</option>
                <option value="attendance_gender" {{ $reportType === 'attendance_gender' ? 'selected' : '' }}>Attendance Gender-wise</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Examination Session</label>
            <select name="examination_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Sessions</option>
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" {{ $examinationId == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                @endforeach
            </select>
        </div>
        @if($reportType === 'attendance')
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Assigned School</label>
            <select name="school_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Schools</option>
                @foreach(\App\Models\School::where('status', true)->get() as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Category</label>
            <select name="category_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
                <option value="">All Categories</option>
                @foreach(\App\Models\CategoryMaster::where('status', true)->get() as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5 font-semibold">Date</label>
            <input type="date" name="date" value="{{ request('date') }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-200 text-sm focus:outline-none focus:border-indigo-500/50">
        </div>
        @endif
        <div>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors cursor-pointer">Generate Report</button>
        </div>
    </form>
</div>

{{-- Export Buttons --}}
@if(count($reportData['rows']) > 0)
<div class="flex flex-wrap gap-3 mb-6">
    <a href="{{ route('admin.reports.export', ['type' => $reportType, 'examination_id' => $examinationId, 'school_id' => request('school_id'), 'category_id' => request('category_id'), 'date' => request('date'), 'format' => 'excel']) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-xl transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        Export Excel
    </a>
    <a href="{{ route('admin.reports.export', ['type' => $reportType, 'examination_id' => $examinationId, 'school_id' => request('school_id'), 'category_id' => request('category_id'), 'date' => request('date'), 'format' => 'csv']) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-sm font-medium rounded-xl transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        Export CSV
    </a>
    <a href="{{ route('admin.reports.export', ['type' => $reportType, 'examination_id' => $examinationId, 'school_id' => request('school_id'), 'category_id' => request('category_id'), 'date' => request('date'), 'format' => 'pdf']) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-sm font-medium rounded-xl transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        Export PDF
    </a>
</div>
@endif

{{-- Report Data Table --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-800/60">
        <h3 class="text-sm font-semibold text-slate-200">{{ ucwords(str_replace('_', ' ', $reportType)) }} Report</h3>
        <p class="text-xs text-slate-500 mt-0.5">{{ count($reportData['rows']) }} record(s) found</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-800/60">
                    @foreach($reportData['headings'] as $heading)
                    <th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
                @forelse($reportData['rows'] as $row)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    @foreach($row as $cell)
                    <td class="px-6 py-4 text-slate-300 text-sm">{{ $cell }}</td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($reportData['headings']) }}" class="px-6 py-16 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-700 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                        </svg>
                        <p class="text-slate-500 font-medium">No data available for this report</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(!empty($reportCharts))
<div class="mb-6">
    <div class="flex items-center justify-between gap-4 mb-4">
        <div>
            <h3 class="text-sm font-semibold text-slate-200">All Report Charts</h3>
            <p class="text-xs text-slate-500 mt-0.5">A chart preview for each report type using the current examination filter.</p>
        </div>
        <span class="text-xs text-slate-500">{{ count($reportCharts) }} chart(s)</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach([
            'school_wise' => 'School-wise Registration',
            'class_wise' => 'Class-wise Registration',
            'category_wise' => 'Category-wise Registration',
            'examination_wise' => 'Examination-wise Registration',
            'approved' => 'Approved Students',
            'rejected' => 'Rejected Students',
            'hall_ticket' => 'Hall Ticket Issued',
            'attendance' => 'Attendance Summary',
            'attendance_category' => 'Attendance by Category',
            'attendance_gender' => 'Attendance Gender-wise',
        ] as $chartKey => $chartLabel)
            @if(!empty($reportCharts[$chartKey]))
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-200">{{ $chartLabel }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $reportCharts[$chartKey]['title'] }}</p>
                        </div>
                        <span class="text-xs text-slate-500">{{ count($reportCharts[$chartKey]['categories'] ?? $reportCharts[$chartKey]['labels'] ?? []) }} item(s)</span>
                    </div>
                    <div id="reportChart-{{ $chartKey }}" class="min-h-[300px]"></div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endif

@if(!empty($reportCharts))
@push('scripts')
    <script @nonce>
        document.addEventListener('DOMContentLoaded', function () {
            const reportCharts = @json($reportCharts);

            const renderChart = (chartElement, chartData) => {
                if (!chartElement || !chartData) {
                    return;
                }

                const chartType = chartData.chartType ?? 'bar';
                const baseOptions = {
                    chart: {
                        background: 'transparent',
                        toolbar: { show: false },
                        type: chartType,
                        height: 300,
                        stacked: !!chartData.stacked,
                    },
                    theme: { mode: 'dark' },
                    colors: ['#6366f1', '#22c55e', '#f59e0b', '#0ea5e9', '#ef4444', '#a855f7'],
                    dataLabels: { enabled: false },
                    grid: { borderColor: '#1e293b' },
                    tooltip: { theme: 'dark' },
                    legend: { show: false },
                };

                let chartOptions = baseOptions;

                if (chartType === 'donut' || chartType === 'pie') {
                    chartOptions = {
                        ...baseOptions,
                        series: chartData.series,
                        labels: chartData.labels ?? chartData.categories ?? [],
                        legend: { show: true, position: 'bottom', labels: { colors: '#94a3b8' } },
                        plotOptions: chartType === 'donut' ? { pie: { donut: { size: '62%' } } } : {},
                    };
                } else if (chartType === 'radar') {
                    chartOptions = {
                        ...baseOptions,
                        series: chartData.series,
                        xaxis: {
                            categories: chartData.categories ?? [],
                            labels: { style: { colors: '#64748b', fontSize: '11px' } },
                        },
                        stroke: { width: 2 },
                        fill: { opacity: 0.15 },
                        legend: { show: true, position: 'bottom', labels: { colors: '#94a3b8' } },
                    };
                } else if (chartType === 'line' || chartType === 'area') {
                    chartOptions = {
                        ...baseOptions,
                        series: chartData.series,
                        chart: { ...baseOptions.chart, type: chartType, height: 300 },
                        stroke: { curve: 'smooth', width: 3 },
                        fill: chartType === 'area' ? { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } } : { opacity: 1 },
                        xaxis: {
                            categories: chartData.categories ?? [],
                            labels: { style: { colors: '#64748b', fontSize: '11px' } },
                        },
                        yaxis: { labels: { style: { colors: '#64748b' } } },
                        legend: { show: true, position: 'bottom', labels: { colors: '#94a3b8' } },
                    };
                } else {
                    chartOptions = {
                        ...baseOptions,
                        series: chartData.series,
                        chart: { ...baseOptions.chart, type: 'bar', height: 300, stacked: !!chartData.stacked },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 6,
                                barHeight: '55%',
                            },
                        },
                        xaxis: {
                            categories: chartData.categories ?? [],
                            labels: { style: { colors: '#64748b', fontSize: '11px' } },
                        },
                        yaxis: { labels: { style: { colors: '#64748b' } } },
                        legend: { show: (chartData.series || []).length > 1, position: 'bottom', labels: { colors: '#94a3b8' } },
                        fill: { opacity: 0.95 },
                    };
                }

                new ApexCharts(chartElement, chartOptions).render();
            };

            Object.entries(reportCharts).forEach(([chartKey, chartData]) => {
                const chartElement = document.querySelector('#reportChart-' + chartKey);
                renderChart(chartElement, chartData);
            });
        });
    </script>
@endpush
@endif
@endsection

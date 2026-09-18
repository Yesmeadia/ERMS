@extends('layouts.app')

@section('page_title', 'Live Monitor: ' . $exam->name)

@section('content')
    <div class="space-y-6 pb-16">
        <!-- Top Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.online-exams.show', $exam) }}"
                        class="text-xs text-indigo-400 hover:text-indigo-300 font-medium inline-flex items-center gap-1.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        <span>Back to Examination Details</span>
                    </a>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white">Live Proctoring Monitor</h1>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Live Feed
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-mono mt-1">
                    {{ $exam->name }} ({{ $exam->code }}) &bull; Category: {{ $exam->category->name }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                    <span>Auto-refreshing (5s)</span>
                </div>
                <button type="button" onclick="fetchLiveStats()"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-colors flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Live Statistics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 sm:gap-4">
            <div onclick="setFilter('ALL')" 
                 class="bg-slate-900/80 border border-slate-800 hover:border-slate-600 rounded-2xl p-3.5 cursor-pointer transition-all hover:bg-slate-800/40">
                <span class="text-[11px] uppercase tracking-wider text-slate-400 block mb-1">Enrolled</span>
                <span id="statEnrolled"
                    class="text-xl sm:text-2xl font-bold text-white font-mono">{{ $stats['total_enrolled'] ?? $totalEnrolled }}</span>
            </div>
            <div onclick="setFilter('ATTENDED')" 
                 class="bg-slate-900/80 border border-slate-800 hover:border-sky-500/50 rounded-2xl p-3.5 cursor-pointer transition-all hover:bg-slate-800/40">
                <span class="text-[11px] uppercase tracking-wider text-sky-400 font-semibold block mb-1">All Attended</span>
                <span id="statAttended"
                    class="text-xl sm:text-2xl font-bold text-sky-400 font-mono">{{ $stats['attended'] ?? 0 }}</span>
            </div>
            <div onclick="setFilter('IN_PROGRESS')" 
                 class="bg-slate-900/80 border border-slate-800 hover:border-emerald-500/50 rounded-2xl p-3.5 cursor-pointer transition-all hover:bg-slate-800/40">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] uppercase tracking-wider text-emerald-400 font-semibold block">Active Now</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                <span id="statActive"
                    class="text-xl sm:text-2xl font-bold text-emerald-400 font-mono">{{ $stats['in_progress'] ?? 0 }}</span>
            </div>
            <div onclick="setFilter('COMPLETED')" 
                 class="bg-slate-900/80 border border-slate-800 hover:border-indigo-500/50 rounded-2xl p-3.5 cursor-pointer transition-all hover:bg-slate-800/40">
                <span class="text-[11px] uppercase tracking-wider text-indigo-400 block mb-1">Completed</span>
                <span id="statCompleted"
                    class="text-xl sm:text-2xl font-bold text-indigo-400 font-mono">{{ $stats['completed'] ?? 0 }}</span>
            </div>
            <div onclick="setFilter('TIMED_OUT')" 
                 class="bg-slate-900/80 border border-slate-800 hover:border-orange-500/50 rounded-2xl p-3.5 cursor-pointer transition-all hover:bg-slate-800/40">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] uppercase tracking-wider text-orange-400 font-semibold block">Timed Out</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-orange-400/80" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span id="statTimedOut"
                    class="text-xl sm:text-2xl font-bold text-orange-400 font-mono">{{ $stats['timed_out'] ?? 0 }}</span>
            </div>
            <div onclick="setFilter('TERMINATED')" 
                 class="bg-slate-900/80 border border-slate-800 hover:border-rose-500/50 rounded-2xl p-3.5 cursor-pointer transition-all hover:bg-slate-800/40">
                <span class="text-[11px] uppercase tracking-wider text-rose-400 block mb-1">Terminated</span>
                <span id="statTerminated"
                    class="text-xl sm:text-2xl font-bold text-rose-400 font-mono">{{ $stats['terminated'] ?? 0 }}</span>
            </div>
            <div onclick="setFilter('VIOLATIONS')" 
                 class="bg-slate-900/80 border border-slate-800 hover:border-amber-500/50 rounded-2xl p-3.5 cursor-pointer transition-all hover:bg-slate-800/40">
                <span class="text-[11px] uppercase tracking-wider text-amber-400 block mb-1">Violations</span>
                <span id="statViolations"
                    class="text-xl sm:text-2xl font-bold text-amber-400 font-mono">{{ $stats['violations_total'] ?? 0 }}</span>
            </div>
        </div>

        <!-- Filter Pills & Search Bar -->
        <div class="space-y-3">
            <!-- Quick Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <button type="button" onclick="setFilter('ALL')" data-status="ALL"
                    class="filter-tab-btn px-3 py-1.5 rounded-xl font-medium border transition-all bg-indigo-600 text-white border-indigo-500 flex items-center gap-1.5 whitespace-nowrap shadow-sm">
                    <span>All Candidates</span>
                    <span id="badgeTabAll" class="px-1.5 py-0.2 rounded-full bg-white/20 text-[10px] font-mono font-bold">0</span>
                </button>
                <button type="button" onclick="setFilter('ATTENDED')" data-status="ATTENDED"
                    class="filter-tab-btn px-3 py-1.5 rounded-xl font-medium border transition-all bg-slate-900 text-slate-400 border-slate-800 hover:text-white flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                    <span>All Attended</span>
                    <span id="badgeTabAttended" class="px-1.5 py-0.2 rounded-full bg-sky-500/20 text-sky-400 text-[10px] font-mono font-bold">0</span>
                </button>
                <button type="button" onclick="setFilter('IN_PROGRESS')" data-status="IN_PROGRESS"
                    class="filter-tab-btn px-3 py-1.5 rounded-xl font-medium border transition-all bg-slate-900 text-slate-400 border-slate-800 hover:text-white flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Active Now</span>
                    <span id="badgeTabActive" class="px-1.5 py-0.2 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">0</span>
                </button>
                <button type="button" onclick="setFilter('NOT_STARTED')" data-status="NOT_STARTED"
                    class="filter-tab-btn px-3 py-1.5 rounded-xl font-medium border transition-all bg-slate-900 text-slate-400 border-slate-800 hover:text-white flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                    <span>Not Started</span>
                    <span id="badgeTabNotStarted" class="px-1.5 py-0.2 rounded-full bg-slate-700/40 text-slate-300 text-[10px] font-mono font-bold">0</span>
                </button>
                <button type="button" onclick="setFilter('COMPLETED')" data-status="COMPLETED"
                    class="filter-tab-btn px-3 py-1.5 rounded-xl font-medium border transition-all bg-slate-900 text-slate-400 border-slate-800 hover:text-white flex items-center gap-1.5 whitespace-nowrap">
                    <span>Completed</span>
                    <span id="badgeTabCompleted" class="px-1.5 py-0.2 rounded-full bg-indigo-500/20 text-indigo-400 text-[10px] font-mono font-bold">0</span>
                </button>
                <button type="button" onclick="setFilter('TIMED_OUT')" data-status="TIMED_OUT"
                    class="filter-tab-btn px-3 py-1.5 rounded-xl font-medium border transition-all bg-slate-900 text-slate-400 border-slate-800 hover:text-white flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                    <span>Timed Out</span>
                    <span id="badgeTabTimedOut" class="px-1.5 py-0.2 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-mono font-bold">0</span>
                </button>
                <button type="button" onclick="setFilter('TERMINATED')" data-status="TERMINATED"
                    class="filter-tab-btn px-3 py-1.5 rounded-xl font-medium border transition-all bg-slate-900 text-slate-400 border-slate-800 hover:text-white flex items-center gap-1.5 whitespace-nowrap">
                    <span>Terminated</span>
                    <span id="badgeTabTerminated" class="px-1.5 py-0.2 rounded-full bg-rose-500/20 text-rose-400 text-[10px] font-mono font-bold">0</span>
                </button>
                <button type="button" onclick="setFilter('OFFLINE')" data-status="OFFLINE"
                    class="filter-tab-btn px-3 py-1.5 rounded-xl font-medium border transition-all bg-slate-900 text-slate-400 border-slate-800 hover:text-white flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                    <span>Offline / Inactive</span>
                    <span id="badgeTabOffline" class="px-1.5 py-0.2 rounded-full bg-slate-700/50 text-slate-300 text-[10px] font-mono font-bold">0</span>
                </button>
            </div>

            <!-- Search, Filter & View Switcher Bar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <input type="text" id="liveSearchInput" oninput="filterSessionsTable()"
                        placeholder="Filter by student name or registration number..."
                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                </div>
                <select id="statusFilter" onchange="filterSessionsTable()"
                    class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <option value="ALL">All Candidates</option>
                    <option value="ATTENDED">All Attended Students</option>
                    <option value="IN_PROGRESS">Active / In Progress</option>
                    <option value="NOT_STARTED">Not Started (Awaiting Login)</option>
                    <option value="COMPLETED">Completed (Submitted)</option>
                    <option value="TIMED_OUT">Timed Out (Exam Expired)</option>
                    <option value="TERMINATED">Terminated</option>
                    <option value="OFFLINE">Offline / Inactive</option>
                    <option value="VIOLATIONS">With Violations</option>
                </select>

                <!-- View Mode Switcher -->
                <div class="flex items-center bg-slate-950 p-1 rounded-xl border border-slate-800 shrink-0">
                    <button type="button" id="tableViewBtn" onclick="switchViewMode('table')"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all bg-indigo-600 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <span>Table View</span>
                    </button>
                    <button type="button" id="gridViewBtn" onclick="switchViewMode('grid')"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all text-slate-400 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <span>Live Video Grid</span>
                        <span id="gridActiveCountBadge" class="px-1.5 py-0.2 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- View 1: Live Sessions Table -->
        <div id="tableViewContainer" class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase tracking-wider text-[10px]">
                            <th class="py-3.5 px-4">Student</th>
                            <th class="py-3.5 px-4">School</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Current Q</th>
                            <th class="py-3.5 px-4 text-center">Heartbeat</th>
                            <th class="py-3.5 px-4 text-center">Camera</th>
                            <th class="py-3.5 px-4 text-center">Fullscreen</th>
                            <th class="py-3.5 px-4 text-center">Violations</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsTableBody" class="divide-y divide-slate-800/60 text-slate-300">
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-500">
                                Connecting to live stream...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- View 2: Live Video Grid -->
        <div id="gridViewContainer" class="hidden space-y-4">
            <div id="videoCardsGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Dynamically filled with student stream cards -->
            </div>
        </div>
    </div>

    <!-- Terminate Session Modal -->
    <div id="terminateModal"
        class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-rose-500/50 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center gap-3 text-rose-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <h3 class="text-base font-bold text-white">Manual Proctor Termination</h3>
            </div>
            <p class="text-xs text-slate-300">
                You are about to terminate the active examination session for <strong id="termStudentName"
                    class="text-white"></strong> (<span id="termRegNo" class="font-mono text-indigo-300"></span>).
            </p>
            <div>
                <label class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1">Reason for
                    Termination *</label>
                <input type="text" id="terminateReasonInput"
                    placeholder="e.g. Unauthorised proctor assistance, external materials observed"
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="button" onclick="closeTerminateModal()"
                    class="w-1/2 py-2.5 px-4 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="submitTermination()"
                    class="w-1/2 py-2.5 px-4 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-lg transition-colors">
                    Terminate Session
                </button>
            </div>
        </div>
    </div>

    <!-- Event Timeline Modal -->
    <div id="eventsModal"
        class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-white">Session Audit Trail</h3>
                        <span id="eventModalViolationsBadge" class="px-2 py-0.5 rounded-full text-xs font-bold font-mono bg-slate-800 text-slate-300">
                            0 Violations
                        </span>
                    </div>
                    <p id="eventModalStudent" class="text-xs text-slate-400 mt-0.5 truncate"></p>
                </div>
                <button type="button" onclick="document.getElementById('eventsModal').classList.add('hidden')"
                    class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors shrink-0"
                    title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="eventsListContainer" class="max-h-96 overflow-y-auto space-y-2 pr-1 text-xs">
                <p class="text-slate-500 text-center py-4">Loading audit trail...</p>
            </div>
        </div>
    </div>

    <!-- Live Camera Inspector Modal (WebRTC P2P Stream) -->
    <div id="cameraInspectorModal"
        class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 flex items-center justify-center p-3 sm:p-6 hidden">
        <div class="bg-slate-900 border border-indigo-500/40 rounded-3xl max-w-4xl w-full flex flex-col max-h-[95vh] shadow-2xl overflow-hidden">
            
            <!-- Inspector Header -->
            <div class="p-4 sm:px-6 sm:py-4 border-b border-slate-800 flex items-center justify-between gap-3 bg-slate-950/60">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center shrink-0 text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <div class="truncate">
                        <h3 id="inspectorStudentName" class="text-sm sm:text-base font-bold text-white truncate">Student Name</h3>
                        <p class="text-xs text-slate-400 font-mono truncate">
                            <span id="inspectorRegNo" class="text-indigo-300">Reg: 0000</span> &bull; 
                            <span id="inspectorSchool" class="text-slate-400">School</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span id="inspectorLiveBadge"
                          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 border border-amber-500/30 text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        <span id="inspectorLiveText">Initiating WebRTC...</span>
                    </span>

                    <button type="button" onclick="closeCameraInspector()"
                            class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors"
                            title="Close Inspector">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Inspector Video Stage -->
            <div class="relative bg-black flex-1 min-h-[300px] sm:min-h-[420px] flex items-center justify-center overflow-hidden">
                <video id="inspectorVideo" autoplay playsinline muted class="w-full h-full max-h-[60vh] object-contain"></video>

                <!-- Loading / Connecting Overlay -->
                <div id="inspectorLoadingOverlay" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/90 text-center p-6 space-y-3 z-10">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center animate-spin">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Negotiating WebRTC Direct Stream</p>
                        <p id="inspectorLoadingSubtext" class="text-xs text-slate-400 font-mono mt-1">Connecting to candidate's camera feed...</p>
                    </div>
                </div>

                <!-- Video Controls Floating Toolbar -->
                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between pointer-events-none z-20">
                    <div class="pointer-events-auto flex items-center gap-2 bg-slate-950/80 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-800 text-[11px] font-mono text-slate-300">
                        <span id="inspectorIceState" class="text-emerald-400">ICE: starting</span>
                        <span class="text-slate-600">|</span>
                        <span id="inspectorResolutionText">-- x --</span>
                    </div>

                    <div class="pointer-events-auto flex items-center gap-2">
                        <button type="button" id="inspectorAudioToggleBtn" onclick="toggleInspectorAudio()"
                                class="px-3 py-1.5 rounded-xl bg-slate-900/90 hover:bg-slate-800 backdrop-blur border border-slate-700 text-xs font-semibold text-slate-300 hover:text-white transition-all flex items-center gap-1.5 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.414 0-.75-.336-.75-.75V10.5c0-.414.336-.75.75-.75h4.24z" />
                            </svg>
                            <span id="inspectorAudioLabel">Unmute Audio</span>
                        </button>
                        <button type="button" onclick="captureStreamSnapshot()"
                                class="px-3 py-1.5 rounded-xl bg-slate-900/90 hover:bg-slate-800 backdrop-blur border border-slate-700 text-xs font-semibold text-white transition-all flex items-center gap-1.5 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                            </svg>
                            Capture Snapshot
                        </button>
                        <button type="button" onclick="toggleInspectorFullscreen()"
                                class="p-2 rounded-xl bg-slate-900/90 hover:bg-slate-800 backdrop-blur border border-slate-700 text-slate-300 hover:text-white transition-all shadow-lg"
                                title="Toggle Fullscreen">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inspector Footer Controls -->
            <div class="p-4 sm:px-6 bg-slate-950/80 border-t border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button type="button" onclick="reconnectInspectorStream()"
                            class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-colors flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Reconnect Stream
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="terminateFromInspector()"
                            class="px-3.5 py-1.5 rounded-xl bg-rose-600/20 hover:bg-rose-600/40 text-rose-300 border border-rose-500/30 text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Terminate Session
                    </button>
                    <button type="button" onclick="closeCameraInspector()"
                            class="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Snapshot Evidence Modal -->
    <div id="snapshotModal"
        class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-indigo-500/40 rounded-3xl max-w-lg w-full p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-white">Captured Live Evidence Snapshot</h3>
                    <p id="snapshotModalMeta" class="text-xs text-slate-400 font-mono mt-0.5"></p>
                </div>
                <button type="button" onclick="document.getElementById('snapshotModal').classList.add('hidden')"
                        class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors"
                        title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="rounded-2xl overflow-hidden border border-slate-800 bg-black">
                <img id="snapshotPreviewImg" src="" alt="Live Stream Snapshot" class="w-full object-contain max-h-[50vh]">
            </div>
            <div class="flex items-center justify-between gap-3 pt-2">
                <span class="text-[11px] text-slate-400">Timestamp: <span id="snapshotTimestamp" class="font-mono text-slate-200"></span></span>
                <div class="flex items-center gap-2">
                    <a id="snapshotDownloadBtn" href="" download="proctoring-snapshot.jpg"
                       class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Image
                    </a>
                    <button type="button" onclick="document.getElementById('snapshotModal').classList.add('hidden')"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hashed Video Recordings Modal -->
    <div id="recordingsModal"
        class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-2xl w-full p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-white">Recorded Proctoring Video Chunks</h3>
                        <span id="recordingsCountBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            0 Chunks
                        </span>
                    </div>
                    <p id="recordingsModalMeta" class="text-xs text-slate-400 font-mono mt-0.5"></p>
                </div>
                <button type="button" onclick="closeRecordingsModal()"
                        class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors"
                        title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Active Chunk Video Player -->
            <div id="recordingPlayerContainer" class="rounded-2xl overflow-hidden border border-slate-800 bg-black aspect-video flex items-center justify-center">
                <video id="recordingVideoPlayer" controls class="w-full h-full object-contain hidden"></video>
                <div id="recordingPlayerPlaceholder" class="text-center p-6 text-slate-500 text-xs">
                    Select a recorded chunk below to stream playback.
                </div>
            </div>

            <!-- Chunks List -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-[11px] text-slate-400 uppercase tracking-wider font-semibold px-1">
                    <span>Hashed Video Chunks (Private Storage)</span>
                    <span id="recordingsTotalSize" class="font-mono text-indigo-300"></span>
                </div>
                <div id="recordingsList" class="max-h-52 overflow-y-auto space-y-1.5 pr-1">
                    <!-- Populated via AJAX -->
                </div>
            </div>

            <div class="flex items-center justify-end pt-2 border-t border-slate-800">
                <button type="button" onclick="closeRecordingsModal()"
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script @nonce>
        const POLL_URL = "{{ route('admin.online-exams.live.poll', $exam) }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const TOTAL_QUESTIONS = {{ $totalQuestions }};
        let allSessions = @json($initialSessions ?? []);
        let activeTermSessionId = null;

        async function fetchLiveStats() {
            try {
                const res = await fetch(POLL_URL, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                if (!res.ok) {
                    console.warn('[LiveMonitor] Poll returned HTTP', res.status, '- session may have expired.');
                    return;
                }
                const data = await res.json();
                if (data.success) {
                    updateStatsCards(data.stats);
                    allSessions = data.sessions || [];
                    filterSessionsTable();

                    // Auto-close inspector if monitored candidate has submitted or finished
                    if (currentInspectorSessionId) {
                        const inspectedSession = allSessions.find(s => s.session_id === currentInspectorSessionId);
                        if (inspectedSession && (inspectedSession.is_completed || ['SUBMITTED', 'COMPLETED', 'TERMINATED', 'EXPIRED'].includes(inspectedSession.status))) {
                            const overlay = document.getElementById('inspectorLoadingOverlay');
                            const subtext = document.getElementById('inspectorLoadingSubtext');
                            const badge = document.getElementById('inspectorLiveBadge');
                            const badgeText = document.getElementById('inspectorLiveText');
                            if (overlay) overlay.classList.remove('hidden');
                            if (subtext) subtext.innerHTML = `<span class="text-indigo-400 font-semibold">Candidate has ${inspectedSession.status_label.toLowerCase()} the examination.</span><br><span class="text-slate-400 text-xs">Live session closed. Returning to dashboard...</span>`;
                            if (badge) badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-500/10 border border-slate-500/30 text-slate-400';
                            if (badgeText) badgeText.textContent = 'Session Closed';
                            cleanupInspectorPeer();
                            setTimeout(() => {
                                closeCameraInspector();
                            }, 2500);
                        }
                    }
                } else {
                    console.warn('[LiveMonitor] Poll returned success:false', data);
                }
            } catch (err) {
                console.warn('[LiveMonitor] Polling error:', err);
            }
        }

        function setFilter(status) {
            const select = document.getElementById('statusFilter');
            if (select) select.value = status;
            filterSessionsTable();
        }

        function updateStatsCards(stats) {
            if (document.getElementById('statEnrolled')) document.getElementById('statEnrolled').textContent = stats.total_enrolled;
            if (document.getElementById('statAttended')) document.getElementById('statAttended').textContent = stats.attended ?? (allSessions.filter(s => s.status !== 'NOT_STARTED').length);
            if (document.getElementById('statActive')) document.getElementById('statActive').textContent = stats.in_progress;
            if (document.getElementById('statTimedOut')) document.getElementById('statTimedOut').textContent = stats.timed_out ?? 0;
            if (document.getElementById('statCompleted')) document.getElementById('statCompleted').textContent = stats.completed;
            if (document.getElementById('statTerminated')) document.getElementById('statTerminated').textContent = stats.terminated;
            if (document.getElementById('statViolations')) document.getElementById('statViolations').textContent = stats.violations_total;
            updateTabBadges();
        }

        function updateTabBadges() {
            const total = allSessions.length;
            const attended = allSessions.filter(s => s.status !== 'NOT_STARTED').length;
            const notStarted = allSessions.filter(s => s.status === 'NOT_STARTED').length;
            const active = allSessions.filter(s => ['READY', 'IN_PROGRESS', 'QUESTION_ACTIVE', 'ANSWERED'].includes(s.status)).length;
            const timedOut = allSessions.filter(s => ['EXPIRED', 'QUESTION_TIMEOUT'].includes(s.status) || s.is_timed_out).length;
            const completed = allSessions.filter(s => ['SUBMITTED', 'EXPIRED', 'COMPLETED'].includes(s.status) || s.is_completed).length;
            const terminated = allSessions.filter(s => s.status === 'TERMINATED').length;
            const offline = allSessions.filter(s => !s.is_online && !['SUBMITTED', 'COMPLETED', 'TERMINATED', 'EXPIRED', 'NOT_STARTED'].includes(s.status)).length;

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };
            setVal('badgeTabAll', total);
            setVal('badgeTabAttended', attended);
            setVal('badgeTabNotStarted', notStarted);
            setVal('badgeTabActive', active);
            setVal('badgeTabTimedOut', timedOut);
            setVal('badgeTabCompleted', completed);
            setVal('badgeTabTerminated', terminated);
            setVal('badgeTabOffline', offline);
        }

        function filterSessionsTable() {
            const searchEl = document.getElementById('liveSearchInput');
            const filterEl = document.getElementById('statusFilter');
            const query = searchEl ? searchEl.value.toLowerCase() : '';
            const status = filterEl ? filterEl.value : 'ALL';

            // Highlight active quick-filter tab
            document.querySelectorAll('.filter-tab-btn').forEach(btn => {
                if (btn.dataset.status === status) {
                    btn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-500', 'shadow-sm');
                    btn.classList.remove('bg-slate-900', 'text-slate-400', 'border-slate-800');
                } else {
                    btn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-500', 'shadow-sm');
                    btn.classList.add('bg-slate-900', 'text-slate-400', 'border-slate-800');
                }
            });

            const filtered = allSessions.filter(s => {
                const matchesQuery = s.student_name.toLowerCase().includes(query) || s.registration_number.toLowerCase().includes(query);
                let matchesStatus = true;
                if (status === 'ATTENDED') {
                    matchesStatus = s.status !== 'NOT_STARTED';
                } else if (status === 'NOT_STARTED') {
                    matchesStatus = s.status === 'NOT_STARTED';
                } else if (status === 'IN_PROGRESS') {
                    matchesStatus = ['READY', 'IN_PROGRESS', 'QUESTION_ACTIVE', 'ANSWERED'].includes(s.status);
                } else if (status === 'TIMED_OUT') {
                    matchesStatus = ['EXPIRED', 'QUESTION_TIMEOUT'].includes(s.status) || s.is_timed_out;
                } else if (status === 'COMPLETED') {
                    matchesStatus = ['SUBMITTED', 'EXPIRED', 'COMPLETED'].includes(s.status) || s.is_completed;
                } else if (status === 'TERMINATED') {
                    matchesStatus = s.status === 'TERMINATED';
                } else if (status === 'OFFLINE') {
                    matchesStatus = !s.is_online && !['SUBMITTED', 'COMPLETED', 'TERMINATED', 'EXPIRED', 'NOT_STARTED'].includes(s.status);
                } else if (status === 'VIOLATIONS') {
                    matchesStatus = s.violations_count > 0;
                }
                return matchesQuery && matchesStatus;
            });

            renderSessionsTable(filtered);
            renderVideoGrid(filtered);
            updateTabBadges();
        }

        // View Mode Switcher
        let currentViewMode = 'table';

        function switchViewMode(mode) {
            currentViewMode = mode;
            const tableContainer = document.getElementById('tableViewContainer');
            const gridContainer = document.getElementById('gridViewContainer');
            const tableBtn = document.getElementById('tableViewBtn');
            const gridBtn = document.getElementById('gridViewBtn');

            if (mode === 'grid') {
                tableContainer.classList.add('hidden');
                gridContainer.classList.remove('hidden');
                gridBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all bg-indigo-600 text-white shadow-sm';
                tableBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all text-slate-400 hover:text-white';
            } else {
                gridContainer.classList.add('hidden');
                tableContainer.classList.remove('hidden');
                tableBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all bg-indigo-600 text-white shadow-sm';
                gridBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all text-slate-400 hover:text-white';
            }
            filterSessionsTable();
        }

        function renderSessionsTable(sessions) {
            const tbody = document.getElementById('sessionsTableBody');
            if (!sessions || sessions.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-slate-500">No active student sessions matching filter.</td></tr>`;
                return;
            }

            tbody.innerHTML = sessions.map(s => {
                const isFinished = ['SUBMITTED', 'COMPLETED', 'TERMINATED', 'EXPIRED'].includes(s.status);
                const isTimedOut = ['EXPIRED', 'QUESTION_TIMEOUT'].includes(s.status) || s.is_timed_out;
                const isCamActive = !isFinished && (s.camera_status === 'active' || s.camera_status === 'ACTIVE');
                const isCamStopped = !isFinished && (s.camera_status === 'STOPPED' || s.camera_status === 'inactive');
                const cameraColor = isCamActive ? 'text-emerald-400' : (isCamStopped ? 'text-rose-400' : 'text-slate-500');
                const cameraLabel = isCamActive ? 'Active' : (isCamStopped ? '⚠ Stopped' : (isFinished ? '○ Disconnected' : '○ Off'));
                const fullColor = s.fullscreen_status ? 'text-emerald-400' : 'text-rose-400';
                const safeName = (s.student_name || '').replace(/'/g, "\\'");
                const safeSchool = (s.school_name || '').replace(/'/g, "\\'");

                return `
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-medium text-white">${s.student_name}</div>
                            <div class="font-mono text-[11px] text-slate-400">${s.registration_number}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-300">${s.school_name}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border ${s.status_color}">
                                ${s.status_label}
                            </span>
                            ${isTimedOut ? `<div class="text-[9px] text-orange-400 font-mono mt-0.5 flex items-center justify-center gap-1 font-semibold"><svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span>Timer Expired</span></div>` : ''}
                            ${s.termination_reason ? `<div class="text-[10px] text-rose-400/80 truncate max-w-[150px] mt-0.5" title="${s.termination_reason}">${s.termination_reason}</div>` : ''}
                        </td>
                        <td class="py-3 px-4 text-center font-mono">
                            ${isFinished ? '-' : `${s.current_question_index} / ${TOTAL_QUESTIONS}`}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="w-2 h-2 rounded-full ${s.is_online ? 'bg-emerald-400' : 'bg-slate-600'}"></span>
                                <span class="text-[10px] text-slate-400 font-mono">${s.last_heartbeat_ago}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-[11px] ${cameraColor}" title="Camera Status: ${s.camera_status}">
                            ${cameraLabel}
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-[11px]">
                            ${s.fullscreen_status ? `
                                <span class="inline-flex items-center gap-1 text-emerald-400 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    <span>Yes</span>
                                </span>
                            ` : `
                                <span class="inline-flex items-center gap-1 text-rose-400 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    <span>No</span>
                                </span>
                            `}
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold ${s.violations_count > 0 ? 'text-rose-400' : 'text-slate-400'}">
                            ${s.violations_count}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                ${s.recordings_count > 0 ? `
                                    <button type="button" onclick="openRecordingsModal(${s.session_id}, '${safeName}', '${s.registration_number}')"
                                            class="px-2 py-1 rounded-lg bg-rose-600/20 hover:bg-rose-600/40 text-rose-300 border border-rose-500/30 text-[10px] font-semibold transition-colors flex items-center gap-1"
                                            title="View ${s.recordings_count} Recorded Video Chunks">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Rec (${s.recordings_count})
                                    </button>
                                ` : ''}
                                ${(!isFinished && s.session_id) ? `
                                    <button type="button" onclick="openCameraInspector(${s.session_id}, '${safeName}', '${s.registration_number}', '${safeSchool}')"
                                            class="px-2.5 py-1 rounded-lg ${isCamActive ? 'bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700'} text-[10px] font-semibold transition-colors flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full ${isCamActive ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500'}"></span>
                                        Live Cam
                                    </button>
                                ` : ''}
                                <button type="button" onclick="openEventsModal(${s.session_id}, '${safeName} (${s.registration_number})')"
                                        class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] transition-colors">
                                    Events
                                </button>
                                ${!isFinished ? `
                                    <button type="button" onclick="openTerminateModal(${s.session_id}, '${safeName}', '${s.registration_number}')"
                                            class="px-2 py-1 rounded-lg bg-rose-600/20 hover:bg-rose-600/40 text-rose-300 border border-rose-500/30 text-[10px] transition-colors">
                                        Terminate
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderVideoGrid(sessions) {
            const grid = document.getElementById('videoCardsGrid');
            if (!grid) return;

            if (!sessions || sessions.length === 0) {
                grid.innerHTML = `<div class="col-span-full py-16 text-center text-slate-500 text-xs">No candidate sessions match current filters.</div>`;
                return;
            }

            // Update badge in view switcher
            const activeCamsCount = sessions.filter(s => (s.camera_status === 'active' || s.camera_status === 'ACTIVE')).length;
            const badgeEl = document.getElementById('gridActiveCountBadge');
            if (badgeEl) badgeEl.textContent = activeCamsCount;

            grid.innerHTML = sessions.map(s => {
                const isFinished = ['SUBMITTED', 'COMPLETED', 'TERMINATED', 'EXPIRED'].includes(s.status);
                const isCamActive = !isFinished && (s.camera_status === 'active' || s.camera_status === 'ACTIVE');
                const isCamStopped = !isFinished && (s.camera_status === 'STOPPED' || s.camera_status === 'inactive');
                const safeName = (s.student_name || '').replace(/'/g, "\\'");
                const safeSchool = (s.school_name || '').replace(/'/g, "\\'");
                const hasSnapshot = Boolean(s.has_snapshot);
                const recCount = s.recordings_count || 0;

                const statusBadge = isCamActive 
                    ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>LIVE</span>`
                    : (isCamStopped 
                        ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-500/20 text-rose-400 border border-rose-500/30">Stopped</span>`
                        : `<span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border ${s.status_color}">${s.status_label}</span>`);

                return `
                    <div class="bg-slate-900/90 border border-slate-800 hover:border-indigo-500/40 rounded-2xl overflow-hidden shadow-xl flex flex-col transition-all group">
                        <!-- Card Header -->
                        <div class="p-3 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between gap-2">
                            <div class="truncate">
                                <div class="font-semibold text-white text-xs truncate" title="${s.student_name}">${s.student_name}</div>
                                <div class="font-mono text-[10px] text-indigo-300 truncate">${s.registration_number}</div>
                            </div>
                            ${statusBadge}
                        </div>

                        <!-- Video Stream Preview / Camera State Container -->
                        <div class="relative bg-black aspect-video flex items-center justify-center overflow-hidden">
                            <div class="w-full h-full flex items-center justify-center bg-slate-950">
                                ${hasSnapshot ? `
                                    <div class="relative w-full h-full">
                                        <img src="${s.snapshot_url}${s.snapshot_url.includes('?') ? '&' : '?'}_cb=${Date.now()}"
                                             alt="${s.student_name}"
                                             class="w-full h-full object-cover"
                                             loading="lazy" />
                                        <div class="absolute top-2 left-2 flex items-center gap-1.5 pointer-events-none">
                                            ${!isFinished ? `
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-black/80 backdrop-blur-xs text-emerald-400 border border-emerald-500/40 shadow-sm">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>LIVE FEED
                                                </span>
                                            ` : `
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-950/90 backdrop-blur-xs text-slate-300 border border-slate-700 shadow-sm">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>EXAM SUBMITTED
                                                </span>
                                            `}
                                            ${recCount > 0 ? `
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-rose-950/90 text-rose-300 border border-rose-600/40 shadow-sm" title="${recCount} hashed chunks recorded">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>REC (${recCount})
                                                </span>
                                            ` : ''}
                                        </div>
                                    </div>
                                ` : (isCamActive ? `
                                    <div class="flex flex-col items-center justify-center p-4 text-center space-y-2">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                        </div>
                                        <span class="text-[10px] text-emerald-400 font-mono">Camera Connecting...</span>
                                    </div>
                                ` : (isCamStopped ? `
                                    <div class="flex flex-col items-center justify-center p-4 text-center space-y-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25zM3 3l18 18" />
                                        </svg>
                                        <span class="text-[10px] font-semibold text-rose-400">Camera Stopped</span>
                                    </div>
                                ` : `
                                    <div class="flex flex-col items-center justify-center p-4 text-center space-y-1 text-slate-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                        <span class="text-[10px]">${s.status_label}</span>
                                    </div>
                                `))}
                            </div>

                            <!-- Click to Inspect Overlay Button -->
                            ${(!isFinished && s.session_id) ? `
                                <button type="button" onclick="openCameraInspector(${s.session_id}, '${safeName}', '${s.registration_number}', '${safeSchool}')"
                                        class="absolute inset-0 bg-indigo-950/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 text-xs font-semibold text-white backdrop-blur-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Inspect Camera Live</span>
                                </button>
                            ` : ''}
                        </div>

                        <!-- Card Meta Footer -->
                        <div class="p-3 bg-slate-950/40 flex items-center justify-between text-[11px] font-mono border-t border-slate-800/80">
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <span>Q:</span>
                                <span class="text-white font-bold">${isFinished ? '-' : `${s.current_question_index} / ${TOTAL_QUESTIONS}`}</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                ${recCount > 0 ? `
                                    <button type="button" onclick="openRecordingsModal(${s.session_id}, '${safeName}', '${s.registration_number}')"
                                            class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-semibold transition-colors flex items-center gap-1"
                                            title="View Hashed Video Recordings">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Rec (${recCount})
                                    </button>
                                ` : ''}
                                ${(!isFinished && s.session_id) ? `
                                    <button type="button" onclick="openCameraInspector(${s.session_id}, '${safeName}', '${s.registration_number}', '${safeSchool}')"
                                            class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-200 hover:text-white text-[10px] font-semibold transition-colors">
                                        Inspect
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function openRecordingsModal(sessionId, studentName, regNo) {
            document.getElementById('recordingsModalMeta').textContent = `${studentName} (${regNo})`;
            document.getElementById('recordingsCountBadge').textContent = 'Loading...';
            document.getElementById('recordingsTotalSize').textContent = '';
            const listEl = document.getElementById('recordingsList');
            listEl.innerHTML = '<p class="text-center py-4 text-slate-500 text-xs">Loading recorded chunks...</p>';

            const player = document.getElementById('recordingVideoPlayer');
            const placeholder = document.getElementById('recordingPlayerPlaceholder');
            player.pause();
            player.src = '';
            player.classList.add('hidden');
            placeholder.classList.remove('hidden');

            document.getElementById('recordingsModal').classList.remove('hidden');

            try {
                const res = await fetch(`/admin/online-exams/{{ $exam->id }}/live/recordings/${sessionId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!data.success || !data.recordings || data.recordings.length === 0) {
                    listEl.innerHTML = '<p class="text-center py-4 text-slate-500 text-xs">No video chunks recorded yet for this session.</p>';
                    document.getElementById('recordingsCountBadge').textContent = '0 Chunks';
                    return;
                }

                document.getElementById('recordingsCountBadge').textContent = `${data.total_chunks} Chunks`;
                const totalMb = (data.total_size_bytes / (1024 * 1024)).toFixed(2);
                document.getElementById('recordingsTotalSize').textContent = `Total: ${totalMb} MB`;

                listEl.innerHTML = data.recordings.map(r => `
                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800/80 flex items-center justify-between gap-3 text-xs hover:border-slate-700 transition-colors">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600/20 text-indigo-400 font-bold font-mono text-[10px] flex items-center justify-center shrink-0">
                                #${r.chunk_index}
                            </span>
                            <div class="truncate">
                                <div class="font-mono text-[11px] text-white flex items-center gap-1.5 truncate">
                                    <span>Chunk ${r.chunk_index}</span>
                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400">${r.file_size_human}</span>
                                    ${r.is_final ? '<span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-400">Final</span>' : ''}
                                </div>
                                <div class="font-mono text-[9px] text-slate-500 truncate" title="SHA-256: ${r.sha256_hash}">
                                    SHA-256: ${r.sha256_hash.substring(0, 16)}...
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="playRecordingChunk('${r.stream_url}')"
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-semibold transition-colors shrink-0 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                            </svg>
                            <span>Play</span>
                        </button>
                    </div>
                `).join('');

                // Auto-play first chunk
                if (data.recordings.length > 0) {
                    playRecordingChunk(data.recordings[0].stream_url);
                }
            } catch (err) {
                listEl.innerHTML = '<p class="text-center py-4 text-rose-400 text-xs">Failed loading recordings.</p>';
            }
        }

        function playRecordingChunk(streamUrl) {
            const player = document.getElementById('recordingVideoPlayer');
            const placeholder = document.getElementById('recordingPlayerPlaceholder');
            player.src = streamUrl;
            player.classList.remove('hidden');
            placeholder.classList.add('hidden');
            player.load();
            player.play().catch(err => {
                console.warn('[Recordings Player] Play error:', err);
            });
        }

        function closeRecordingsModal() {
            const player = document.getElementById('recordingVideoPlayer');
            player.pause();
            player.src = '';
            document.getElementById('recordingsModal').classList.add('hidden');
        }

        const WEBRTC_ICE_SERVERS_URL = "{{ route('admin.online-exams.live.webrtc.ice-servers', $exam) }}";
        const RTC_CONFIG = {
            iceServers: @json(\App\Http\Controllers\OnlineExamWebRTCController::getIceServersConfig())
        };

        let currentInspectorSessionId = null;
        let currentInspectorStudent = {};
        let inspectorPeerConnection = null;
        let inspectorPollInterval = null;
        let inspectorBufferedCandidates = [];
        let isPollingInspector = false;

        async function openCameraInspector(sessionId, studentName, regNo, schoolName) {
            if (!sessionId || sessionId === 'null') {
                console.warn('[WebRTC Admin] Cannot open inspector: student has no active session yet.');
                return;
            }
            currentInspectorSessionId = sessionId;
            currentInspectorStudent = { name: studentName, regNo: regNo, school: schoolName };

            // Update UI elements
            document.getElementById('inspectorStudentName').textContent = studentName;
            document.getElementById('inspectorRegNo').textContent = `Reg: ${regNo}`;
            document.getElementById('inspectorSchool').textContent = schoolName;

            const modal = document.getElementById('cameraInspectorModal');
            const overlay = document.getElementById('inspectorLoadingOverlay');
            const badge = document.getElementById('inspectorLiveBadge');
            const badgeText = document.getElementById('inspectorLiveText');
            const video = document.getElementById('inspectorVideo');

            overlay.classList.remove('hidden');
            document.getElementById('inspectorLoadingSubtext').textContent = "Discovering network routes (ICE gathering)... this may take up to 5 seconds.";
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 border border-amber-500/30 text-amber-400';
            badgeText.textContent = 'Connecting P2P...';
            document.getElementById('inspectorIceState').textContent = 'ICE: starting';
            document.getElementById('inspectorResolutionText').textContent = '-- x --';

            video.srcObject = null;
            modal.classList.remove('hidden');

            await startInspectorWebRtc(sessionId);
        }

        async function startInspectorWebRtc(sessionId) {
            cleanupInspectorPeer();

            inspectorPeerConnection = new RTCPeerConnection(RTC_CONFIG);
            const pc = inspectorPeerConnection;
            inspectorBufferedCandidates = [];

            // Add receiver transceiver for video and audio
            pc.addTransceiver('video', { direction: 'recvonly' });
            pc.addTransceiver('audio', { direction: 'recvonly' });

            pc.ontrack = (event) => {
                const video = document.getElementById('inspectorVideo');
                if (video) {
                    if (event.streams && event.streams[0]) {
                        video.srcObject = event.streams[0];
                    } else {
                        let stream = video.srcObject;
                        if (!stream) {
                            stream = new MediaStream();
                            video.srcObject = stream;
                        }
                        stream.addTrack(event.track);
                    }
                    video.muted = true;
                    video.play().catch(console.warn);
                    document.getElementById('inspectorLoadingOverlay').classList.add('hidden');
                    const badge = document.getElementById('inspectorLiveBadge');
                    const badgeText = document.getElementById('inspectorLiveText');
                    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 border border-emerald-500/30 text-emerald-400';
                    badgeText.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>LIVE P2P Stream';

                    video.onloadedmetadata = () => {
                        document.getElementById('inspectorResolutionText').textContent = `${video.videoWidth}x${video.videoHeight}`;
                    };
                }
            };

            // NOTE: We use vanilla (non-trickle) ICE — no separate candidate signals.
            // On shared-hosting HTTP polling (1.5s delay), trickle candidates arrive
            // too late and the peer connection times out. Instead we wait for ICE
            // gathering to complete so all candidates are bundled inside the offer SDP.
            pc.onicecandidate = () => {}; // intentionally no-op; gathering handled below

            pc.oniceconnectionstatechange = () => {
                const el = document.getElementById('inspectorIceState');
                if (el) el.textContent = `ICE: ${pc.iceConnectionState}`;
                if (pc.iceConnectionState === 'connected' || pc.iceConnectionState === 'completed') {
                    document.getElementById('inspectorLoadingOverlay').classList.add('hidden');
                } else if (pc.iceConnectionState === 'failed') {
                    document.getElementById('inspectorLoadingSubtext').textContent = 'Direct P2P connection failed. Click Reconnect to retry.';
                }
            };

            try {
                const offer = await pc.createOffer({ offerToReceiveVideo: true, offerToReceiveAudio: true });
                await pc.setLocalDescription(offer);

                // Wait for ICE gathering to complete (vanilla ICE)
                await new Promise((resolve) => {
                    if (pc.iceGatheringState === 'complete') return resolve();
                    const checkDone = () => {
                        if (pc.iceGatheringState === 'complete') {
                            pc.removeEventListener('icegatheringstatechange', checkDone);
                            resolve();
                        }
                    };
                    pc.addEventListener('icegatheringstatechange', checkDone);
                    // Safety timeout: proceed after 4s even if gathering stalls
                    setTimeout(resolve, 4000);
                });

                if (currentInspectorSessionId !== sessionId) return; // inspector was closed during gathering

                // Send the complete SDP (with all candidates embedded)
                await sendAdminWebRtcSignal(sessionId, 'offer', pc.localDescription);

                // Start polling for answer from student
                if (inspectorPollInterval) clearInterval(inspectorPollInterval);
                pollStudentWebRtcSignals(sessionId);
                inspectorPollInterval = setInterval(() => pollStudentWebRtcSignals(sessionId), 1500);
            } catch (err) {
                console.error('[WebRTC Admin] Error creating offer:', err);
                document.getElementById('inspectorLoadingSubtext').textContent = 'Offer creation error: ' + (err.message || err);
            }
        }

        async function sendAdminWebRtcSignal(sessionId, type, payload) {
            try {
                await fetch(`/admin/online-exams/{{ $exam->id }}/live/webrtc/${sessionId}/signal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        type: type,
                        payload: payload
                    })
                });
            } catch (err) {
                console.warn('[WebRTC Admin] Failed sending signal:', err);
            }
        }

        async function pollStudentWebRtcSignals(sessionId) {
            if (isPollingInspector || currentInspectorSessionId !== sessionId) return;
            isPollingInspector = true;
            try {
                const res = await fetch(`/admin/online-exams/{{ $exam->id }}/live/webrtc/${sessionId}/signals`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && Array.isArray(data.signals) && data.signals.length > 0) {
                        for (const sig of data.signals) {
                            await handleStudentSignal(sig);
                        }
                    }
                }
            } catch (err) {
                // Background polling error
            } finally {
                isPollingInspector = false;
            }
        }

        async function handleStudentSignal(sig) {
            const pc = inspectorPeerConnection;
            if (!pc) return;

            if (sig.type === 'answer') {
                try {
                    // The student answer also uses vanilla ICE — all candidates are
                    // embedded in the SDP, so no separate candidate signals are needed.
                    await pc.setRemoteDescription(new RTCSessionDescription(sig.payload));
                } catch (err) {
                    console.error('[WebRTC Admin] Failed setting remote answer:', err);
                }
            } else if (sig.type === 'candidate') {
                // Kept for backwards-compat but vanilla ICE makes these redundant.
                if (pc.remoteDescription && pc.remoteDescription.type) {
                    try { await pc.addIceCandidate(new RTCIceCandidate(sig.payload)); } catch (e) {}
                } else {
                    inspectorBufferedCandidates.push(sig.payload);
                }
            } else if (sig.type === 'close') {
                document.getElementById('inspectorLoadingOverlay').classList.remove('hidden');
                document.getElementById('inspectorLoadingSubtext').textContent = 'Candidate camera session disconnected.';
            }
        }

        function cleanupInspectorPeer() {
            if (inspectorPollInterval) {
                clearInterval(inspectorPollInterval);
                inspectorPollInterval = null;
            }
            if (inspectorPeerConnection) {
                try {
                    inspectorPeerConnection.close();
                } catch (e) {}
                inspectorPeerConnection = null;
            }
            inspectorBufferedCandidates = [];
            const video = document.getElementById('inspectorVideo');
            if (video) {
                video.srcObject = null;
            }
        }

        function closeCameraInspector() {
            if (currentInspectorSessionId) {
                sendAdminWebRtcSignal(currentInspectorSessionId, 'close', null);
            }
            cleanupInspectorPeer();
            currentInspectorSessionId = null;
            document.getElementById('cameraInspectorModal').classList.add('hidden');
        }

        function reconnectInspectorStream() {
            if (currentInspectorSessionId) {
                startInspectorWebRtc(currentInspectorSessionId);
            }
        }

        function captureStreamSnapshot() {
            const video = document.getElementById('inspectorVideo');
            if (!video || !video.videoWidth) {
                alert('Live video stream not ready for snapshot capture yet.');
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
            const now = new Date();
            const timeStr = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();

            document.getElementById('snapshotPreviewImg').src = dataUrl;
            document.getElementById('snapshotModalMeta').textContent = `${currentInspectorStudent.name} (${currentInspectorStudent.regNo})`;
            document.getElementById('snapshotTimestamp').textContent = timeStr;
            const dlBtn = document.getElementById('snapshotDownloadBtn');
            dlBtn.href = dataUrl;
            dlBtn.download = `proctor-proof-${currentInspectorStudent.regNo}-${Date.now()}.jpg`;

            document.getElementById('snapshotModal').classList.remove('hidden');
        }

        function toggleInspectorAudio() {
            const video = document.getElementById('inspectorVideo');
            const label = document.getElementById('inspectorAudioLabel');
            if (!video) return;
            video.muted = !video.muted;
            if (label) {
                label.textContent = video.muted ? 'Unmute Audio' : 'Mute Audio';
            }
        }

        function toggleInspectorFullscreen() {
            const video = document.getElementById('inspectorVideo');
            if (!video) return;
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(console.warn);
            } else {
                if (video.requestFullscreen) video.requestFullscreen();
                else if (video.webkitRequestFullscreen) video.webkitRequestFullscreen();
            }
        }

        function terminateFromInspector() {
            if (!currentInspectorSessionId) return;
            const id = currentInspectorSessionId;
            const name = currentInspectorStudent.name;
            const regNo = currentInspectorStudent.regNo;
            closeCameraInspector();
            openTerminateModal(id, name, regNo);
        }

        function openTerminateModal(sessionId, name, regNo) {
            activeTermSessionId = sessionId;
            document.getElementById('termStudentName').textContent = name;
            document.getElementById('termRegNo').textContent = regNo;
            document.getElementById('terminateReasonInput').value = '';
            document.getElementById('terminateModal').classList.remove('hidden');
        }

        function closeTerminateModal() {
            document.getElementById('terminateModal').classList.add('hidden');
            activeTermSessionId = null;
        }

        async function submitTermination() {
            const reason = document.getElementById('terminateReasonInput').value.trim();
            if (!reason) {
                alert('Please specify a reason for termination.');
                return;
            }

            try {
                const res = await fetch(`/admin/online-exams/{{ $exam->id }}/live/terminate/${activeTermSessionId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reason: reason })
                });

                const data = await res.json();
                if (data.success) {
                    closeTerminateModal();
                    fetchLiveStats();
                } else {
                    alert(data.message || 'Termination failed.');
                }
            } catch (err) {
                console.error('Termination error:', err);
            }
        }

        async function openEventsModal(sessionId, studentLabel) {
            document.getElementById('eventModalStudent').textContent = studentLabel || 'Candidate';
            const container = document.getElementById('eventsListContainer');
            container.innerHTML = '<p class="text-slate-500 text-center py-4">Loading audit trail...</p>';
            document.getElementById('eventsModal').classList.remove('hidden');

            // Handle uninitiated or null sessions gracefully without failing network request
            if (!sessionId || sessionId === 'null' || sessionId === 'undefined' || sessionId === 0) {
                const violHeader = document.getElementById('eventModalViolationsBadge');
                if (violHeader) {
                    violHeader.textContent = '0 Violations';
                    violHeader.className = 'px-2 py-0.5 rounded-full text-xs font-bold font-mono bg-slate-800 text-slate-400';
                }
                container.innerHTML = `
                    <div class="text-center py-8 space-y-2">
                        <div class="inline-flex p-3 rounded-2xl bg-slate-800/80 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-300">Candidate Not Started</p>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">This student has not logged in or begun the exam yet. Session events and security logs will appear here once examination starts.</p>
                    </div>
                `;
                return;
            }

            try {
                const res = await fetch(`/admin/online-exams/{{ $exam->id }}/live/events/${sessionId}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!res.ok) {
                    const errData = await res.json().catch(() => null);
                    const errMsg = (errData && errData.message) ? errData.message : `HTTP ${res.status}: Failed to load events`;
                    container.innerHTML = `<p class="text-rose-400 text-center py-4">${errMsg}</p>`;
                    return;
                }

                const data = await res.json();
                if (data.success && Array.isArray(data.events) && data.events.length > 0) {
                    const violHeader = document.getElementById('eventModalViolationsBadge');
                    if (violHeader && data.violations_count !== undefined) {
                        violHeader.textContent = `${data.violations_count} / ${data.max_violations} Violations`;
                        violHeader.className = `px-2 py-0.5 rounded-full text-xs font-bold font-mono ${data.violations_count > 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-800 text-slate-400'}`;
                    }

                    container.innerHTML = data.events.map(e => {
                        const isViolation = e.is_violation;
                        let meta = e.metadata;
                        if (typeof meta === 'string') {
                            try { meta = JSON.parse(meta); } catch (ignore) { meta = {}; }
                        }
                        meta = meta || {};

                        const isDebounced = meta.is_debounced;
                        const isStrike = isViolation && !isDebounced;
                        const strikeNum = meta.strike_number ? `#${meta.strike_number}` : '';

                        let badgeHtml = '';
                        let borderClass = 'border-slate-800';
                        let titleColor = 'text-slate-200';

                        if (isStrike) {
                            borderClass = 'border-rose-500/40 bg-rose-950/20';
                            titleColor = 'text-rose-400';
                            badgeHtml = `
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    STRIKE ${strikeNum}
                                </span>
                            `;
                        } else if (isDebounced) {
                            borderClass = 'border-amber-500/30 bg-amber-950/20';
                            titleColor = 'text-amber-300';
                            badgeHtml = `
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    DEBOUNCED (Alt+Tab)
                                </span>
                            `;
                        }

                        let metaDisplay = '';
                        try {
                            metaDisplay = meta && Object.keys(meta).length ? JSON.stringify(meta) : '';
                        } catch (ignore) {}

                        let timeDisplay = '';
                        if (e.event_time) {
                            try {
                                timeDisplay = new Date(e.event_time).toLocaleTimeString();
                            } catch (ignore) {
                                timeDisplay = e.event_time;
                            }
                        }

                        return `
                            <div class="p-2.5 rounded-xl bg-slate-950 border ${borderClass} flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold ${titleColor}">${e.event_type}</span>
                                        ${badgeHtml}
                                    </div>
                                    ${metaDisplay ? `<div class="text-[10px] text-slate-400 font-mono mt-1">${metaDisplay}</div>` : ''}
                                </div>
                                <span class="text-[10px] text-slate-500 font-mono shrink-0">${timeDisplay}</span>
                            </div>
                        `;
                    }).join('');

                    // Refresh stats in background so main table reflects verified count immediately
                    fetchLiveStats();
                } else if (data.success) {
                    container.innerHTML = '<p class="text-slate-500 text-center py-4">No events recorded yet for this session.</p>';
                } else {
                    container.innerHTML = `<p class="text-rose-400 text-center py-4">${data.message || 'Error loading events.'}</p>`;
                }
            } catch (err) {
                console.error('Error loading events:', err);
                container.innerHTML = `<p class="text-rose-400 text-center py-4">Error loading events: ${err.message || 'Network error'}</p>`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            filterSessionsTable();
            fetchLiveStats();
            setInterval(fetchLiveStats, 5000);

            window.addEventListener('beforeunload', () => {
                cleanupInspectorPeer();
            });
        });
    </script>
@endsection
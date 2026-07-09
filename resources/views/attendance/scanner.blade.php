@extends('layouts.app')
@section('page_title', 'Attendance Scanner')
@section('content')

    <style>
        @keyframes scanLaser {
            0% {
                top: 0%;
                opacity: 0.8;
            }

            50% {
                top: 100%;
                opacity: 1;
            }

            100% {
                top: 0%;
                opacity: 0.8;
            }
        }

        .animate-scanner-laser {
            animation: scanLaser 2.5s infinite linear;
        }

        /* Responsive reader container */
        #reader {
            width: 100% !important;
        }

        /* Override html5-qrcode internal styles for responsive fit */
        #reader video {
            width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
        }

        #reader img {
            display: none !important;
        }

        /* Hide the default html5-qrcode header/footer shading boxes */
        #reader__scan_region {
            border: none !important;
        }

        #reader__dashboard {
            display: none !important;
        }

        #reader__camera_selection,
        #reader__camera_permission_button {
            display: none !important;
        }

        /* Prevent Alpine-controlled modals from flashing visible on page load */
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="max-w-4xl mx-auto" x-data="scannerApp()">
        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Scanner Section (Left/Center) --}}
            <div class="lg:col-span-2 flex flex-col items-center">
                <div
                    class="w-full bg-slate-900/60 border border-slate-800/60 rounded-3xl p-6 flex flex-col items-center shadow-xl">
                    <div class="text-center mb-4">
                        <h2 class="text-lg font-bold text-white">QR Camera Scanner</h2>
                        <p class="text-xs text-slate-400 mt-1">Center a student's hall ticket QR code inside the viewfinder
                        </p>
                    </div>

                    {{-- Camera Viewfinder Container (responsive square when inactive, dynamic when active) --}}
                    <div class="relative w-full rounded-2xl overflow-hidden border-2 border-indigo-500/20 bg-slate-950 shadow-2xl transition-all duration-300"
                        :class="scanning ? 'h-auto' : 'aspect-square min-h-[300px]'"
                        role="region"
                        aria-label="QR code scanner viewfinder"
                        :aria-busy="scanning"
                    >
                        {{-- Camera Feed --}}
                        <div id="reader" :class="scanning ? 'relative w-full' : 'absolute inset-0'" aria-hidden="true"></div>

                        {{-- Viewfinder Reticle (Hidden if stopped) --}}
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center"
                            x-show="scanning">
                            <div class="relative border-2 border-indigo-400/30 rounded-xl aspect-square"
                                style="width: 70%; max-width: 70%; max-height: 70%;">
                                {{-- Laser line --}}
                                <div
                                    class="absolute inset-x-0 h-0.5 bg-gradient-to-r from-transparent via-indigo-400 to-transparent shadow-[0_0_12px_#818cf8] animate-scanner-laser">
                                </div>
                                {{-- Viewfinder corners --}}
                                <div
                                    class="absolute -top-0.5 -left-0.5 w-6 h-6 border-t-4 border-l-4 border-indigo-400 rounded-tl-lg">
                                </div>
                                <div
                                    class="absolute -top-0.5 -right-0.5 w-6 h-6 border-t-4 border-r-4 border-indigo-400 rounded-tr-lg">
                                </div>
                                <div
                                    class="absolute -bottom-0.5 -left-0.5 w-6 h-6 border-b-4 border-l-4 border-indigo-400 rounded-bl-lg">
                                </div>
                                <div
                                    class="absolute -bottom-0.5 -right-0.5 w-6 h-6 border-b-4 border-r-4 border-indigo-400 rounded-br-lg">
                                </div>
                            </div>
                        </div>

                        {{-- Stopped overlay --}}
                        <div class="absolute inset-0 bg-slate-950/90 backdrop-blur-sm flex flex-col items-center justify-center gap-4 text-center p-6"
                            x-show="!scanning">
                            <div
                                class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 text-slate-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-300">Scanner Inactive</p>
                                <p class="text-xs text-slate-500 mt-1">Press "Start Camera" to begin scanning</p>
                            </div>
                        </div>
                    </div>

                    {{-- Controls --}}
                    <div class="w-full max-w-sm mt-6 space-y-4">
                        <div>
                            <label for="camera-select" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Camera
                                Source</label>
                            <select id="camera-select" x-model="selectedCameraId"
                                @change="if(scanning) { stopScanner().then(() => startScanner()); }"
                                aria-label="Select camera source"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-indigo-500">
                                <option value="environment">Rear Camera (Default)</option>
                                <option value="user">Front Camera (Selfie)</option>
                                <template x-for="camera in cameras" :key="camera.id">
                                    <option :value="camera.id" x-text="camera.label || 'Camera ' + camera.id"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex gap-3" role="group" aria-label="Scanner controls">
                            <button @click="startScanner()" x-show="!scanning"
                                aria-label="Start camera and begin scanning"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
                                Start Camera
                            </button>
                            <button @click="stopScanner()" x-show="scanning"
                                aria-label="Stop camera and end scanning"
                                class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm px-4 py-2.5 rounded-xl transition-all cursor-pointer">
                                Stop Camera
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Session Panel (Right) --}}
            <div class="lg:col-span-1">
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-3xl p-6 shadow-xl h-full flex flex-col">
                    <h3 class="text-md font-bold text-white mb-4">Scanner Operations</h3>

                    <div class="bg-slate-950 border border-slate-800/60 rounded-2xl p-4 text-center mb-6">
                        <span class="text-xs text-slate-500 uppercase tracking-widest font-semibold block mb-1">Your Scans
                            Today</span>
                        <span class="text-3xl font-extrabold text-indigo-400" id="scan-counter">...</span>
                    </div>

                    <div class="flex-1 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="p-3 bg-slate-800/20 border border-slate-800 rounded-xl flex gap-3 items-center">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981]"></div>
                                <div>
                                    <span class="text-xs font-semibold text-slate-300 block">System Connection Status</span>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">Online & verified with
                                        database</span>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-800/20 border border-slate-800 rounded-xl flex gap-3 items-center">
                                <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-[0_0_8px_#6366f1]"></div>
                                <div>
                                    <span class="text-xs font-semibold text-slate-300 block">Security Signatures</span>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">Cryptographic verification
                                        enabled</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-800/60 mt-6">
                            <a href="{{ route('attendance.history') }}"
                                class="w-full inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm px-4 py-2.5 rounded-xl transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                View Scan History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Visually-hidden live region: announces scan results to screen readers --}}
        <div
            role="status"
            aria-live="polite"
            aria-atomic="true"
            class="sr-only"
            x-text="state === 'success' ? 'Attendance marked successfully for ' + (successData.student_name || '') :
                    state === 'duplicate' ? 'Duplicate scan detected. Attendance already recorded.' :
                    state === 'error' ? 'Scanner error: ' + message :
                    state === 'verification' ? 'Student verified. Please confirm identity.' : ''"
        ></div>

        {{-- MODAL OVERLAYS (Verification, Success, Error states) --}}

        {{-- 1. Student Verification Screen Modal --}}
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
            x-show="state === 'verification'" style="display: none;" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="dialog" aria-modal="true" aria-labelledby="modal-verification-title">
            <div
                class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full overflow-hidden shadow-2xl relative flex flex-col max-h-[90vh]">
                <div class="p-6 text-center border-b border-slate-800/60 shrink-0">
                    <h3 id="modal-verification-title" class="text-md font-bold text-white">Student Verification</h3>
                    <p class="text-xs text-slate-400 mt-1">Please confirm student identity details below</p>
                </div>

                <div class="p-6 flex flex-col items-center overflow-y-auto min-h-0">
                    {{-- Photo + Name inline --}}
                    <div class="flex items-center gap-4 w-full mb-5">
                        {{-- Show photo when available, otherwise show a silhouette placeholder --}}
                        <img
                            x-show="student.photo_url"
                            :src="student.photo_url"
                            :alt="'Photo of ' + (student.name || 'student')"
                            class="w-14 h-14 rounded-xl object-cover border border-slate-700/60 shadow-md shrink-0"
                        >
                        <div
                            x-show="!student.photo_url"
                            class="w-14 h-14 rounded-xl bg-slate-800 border border-slate-700/60 shrink-0 flex items-center justify-center text-slate-500"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="text-left">
                            <h4 class="text-base font-bold text-white leading-tight" x-text="student.name"></h4>
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mt-1.5">
                                Reg: <span x-text="student.registration_number"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Key value fields --}}
                    <div class="w-full grid grid-cols-2 gap-4 text-left border-t border-slate-800/60 pt-4">
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">Hall Ticket No.</span>
                            <p class="text-sm font-semibold text-slate-300" x-text="student.hall_ticket_number"></p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">Class</span>
                            <p class="text-sm font-semibold text-slate-300" x-text="student.class"></p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">School Name</span>
                            <p class="text-sm font-semibold text-slate-300 leading-tight" x-text="student.school"></p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">Examination Session</span>
                            <p class="text-sm font-semibold text-indigo-400 leading-tight" x-text="student.exam_name"></p>
                        </div>
                    </div>
                </div>


                <div class="p-6 bg-slate-950 border-t border-slate-800/60 flex gap-3 shrink-0">
                    <button @click="resetScanner()"
                        class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm px-4 py-2.5 rounded-xl transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button @click="markPresent()"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
                        Mark Present
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. Duplicate Scan Screen Modal --}}
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
            x-show="state === 'duplicate'" style="display: none;" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="dialog" aria-modal="true" aria-labelledby="modal-duplicate-title">
            <div
                class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full overflow-hidden shadow-2xl text-center flex flex-col max-h-[90vh]">
                <div class="p-8 overflow-y-auto min-h-0 flex-1">
                    <div
                        class="w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-4 shadow-[0_0_15px_rgba(245,158,11,0.1)]" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 id="modal-duplicate-title" class="text-xl font-black text-white" x-text="message"></h3>
                    <p class="text-xs text-slate-400 mt-1">This student has already marked attendance today.</p>

                    <div
                        class="bg-slate-950 border border-slate-800/60 rounded-2xl p-4 mt-6 text-left flex gap-3 items-center">
                        {{-- Photo or silhouette placeholder --}}
                        <img
                            x-show="student.photo_url"
                            :src="student.photo_url"
                            class="w-12 h-12 rounded-xl object-cover shrink-0"
                        >
                        <div
                            x-show="!student.photo_url"
                            class="w-12 h-12 rounded-xl bg-slate-800 shrink-0 flex items-center justify-center text-slate-500"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-200 text-sm" x-text="student.name"></h4>
                            <p class="text-xs text-slate-500 mt-0.5" x-text="student.hall_ticket_number"></p>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-slate-950 border-t border-slate-800/60 shrink-0">
                    <button @click="resetScanner()"
                        class="w-full bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm px-4 py-2.5 rounded-xl transition-all cursor-pointer">
                        Scan Next Student
                    </button>
                </div>
            </div>
        </div>

        {{-- 3. Attendance Success Screen Modal --}}
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
            x-show="state === 'success'" style="display: none;" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="dialog" aria-modal="true" aria-labelledby="modal-success-title">
            <div
                class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full overflow-hidden shadow-2xl text-center flex flex-col max-h-[90vh]">
                <div class="p-8 overflow-y-auto min-h-0 flex-1">
                    <div
                        class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto mb-4 shadow-[0_0_15px_rgba(16,185,129,0.1)]" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <h3 id="modal-success-title" class="text-xl font-black text-white" x-text="message"></h3>
                    <p class="text-xs text-slate-400 mt-1">Record logged successfully.</p>

                    <div class="bg-slate-950 border border-slate-800/60 rounded-2xl p-4 mt-6 text-left space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Student Name:</span>
                            <span class="font-semibold text-slate-300" x-text="successData.student_name"></span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Hall Ticket No:</span>
                            <span class="font-semibold text-slate-300" x-text="successData.hallticket_no"></span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Scan Time:</span>
                            <span class="font-semibold text-emerald-400" x-text="successData.scan_time"></span>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-slate-950 border-t border-slate-800/60 shrink-0">
                    <button @click="resetScanner()"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
                        Scan Next Student
                    </button>
                </div>
            </div>
        </div>

        {{-- 4. Generic Error Modal --}}
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
            x-show="state === 'error'" style="display: none;" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="alertdialog" aria-modal="true" aria-labelledby="modal-error-title" :aria-describedby="'modal-error-desc'">
            <div
                class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full overflow-hidden shadow-2xl text-center flex flex-col max-h-[90vh]">
                <div class="p-6 sm:p-8 overflow-y-auto min-h-0 flex-1">
                    <div
                        class="w-14 h-14 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-4 shadow-[0_0_15px_rgba(244,63,94,0.1)]" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 id="modal-error-title" class="text-lg font-bold text-white">Scanner Error</h3>
                    <p id="modal-error-desc" class="text-xs text-slate-300 mt-2 leading-relaxed" x-text="message"></p>

                </div>

                <div class="p-6 bg-slate-950 border-t border-slate-800/60 shrink-0">
                    <button @click="resetScanner()"
                        class="w-full bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm px-4 py-2.5 rounded-xl transition-all cursor-pointer">
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Load html5-qrcode from CDN --}}
        <script @nonce src="https://cdn.jsdelivr.net/npm/html5-qrcode/html5-qrcode.min.js" type="text/javascript"></script>

        <script @nonce>
            function scannerApp() {
                return {
                    scanning: false,
                    isProcessing: false, // Race condition guard: prevents duplicate QR processing
                    cameras: [],
                    selectedCameraId: '',
                    html5QrCode: null,
                    state: 'idle', // idle, verification, duplicate, success, error
                    message: '',
                    student: {},
                    successData: {},
                    examId: null,


                    init() {
                        if (!window.isSecureContext) {
                            this.state = 'error';
                            this.message = "Security Policy Error: Camera access requires a secure connection (HTTPS). Since this page is loaded over insecure HTTP, the browser has blocked camera access. Please use HTTPS.";
                            return;
                        }

                        try {
                            this.html5QrCode = new Html5Qrcode("reader");
                            this.loadCameras();
                            this.updateLocalCounter();
                        } catch (e) {
                            console.error("Failed to initialize Html5Qrcode", e);
                            this.state = 'error';
                            this.message = "Failed to initialize the scanner. Please ensure camera permission is granted in your browser settings.";
                        }
                    },

                    // Memory leak prevention: stop camera if Alpine destroys this component
                    destroy() {
                        if (this.scanning && this.html5QrCode) {
                            this.html5QrCode.stop().catch(() => {});
                        }
                    },
                    loadCameras() {
                        this.selectedCameraId = 'environment';

                        Html5Qrcode.getCameras().then(devices => {
                            this.cameras = devices;
                        }).catch(err => {
                            console.warn("Error enumerating cameras initially", err);
                        });

                        // Auto-start scanner on desktop view only to prevent uninvited permission prompts on mobile
                        const isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) || window.innerWidth < 768;
                        if (!isMobile) {
                            this.startScanner();
                        }
                    },

                    startScanner() {
                        // F2: Prevent multiple concurrent scanner initialisations
                        if (this.scanning) return;

                        const config = {
                            fps: 15,
                            // Use a dynamic qrbox function matching the video stream resolution
                            qrbox: (width, height) => {
                                const minDim = Math.min(width, height);
                                const size = Math.floor(minDim * 0.70);
                                return { width: size, height: size };
                            },
                            // Use the modern BarcodeDetector API when available (much faster + accurate)
                            experimentalFeatures: {
                                useBarCodeDetectorIfSupported: true
                            },
                            // Scan all supported barcode formats for max compatibility
                            supportedScanTypes: [
                                Html5QrcodeScanType.SCAN_TYPE_CAMERA
                            ]
                        };

                        // When a named facing mode is selected ('environment' / 'user'),
                        // wrap it in { facingMode: "..." } so the browser resolves it to a
                        // real camera. Passing the raw string causes OverconstrainedError because
                        // html5-qrcode treats any string as a literal device ID.
                        // For actual device IDs (from getCameras()) pass them through unchanged.
                        let cameraSource = this.selectedCameraId;
                        if (cameraSource === 'environment' || cameraSource === 'user') {
                            cameraSource = { facingMode: cameraSource };
                        }

                        this.html5QrCode.start(
                            cameraSource,
                            config,
                            (decodedText, decodedResult) => {
                                this.onQrCodeSuccess(decodedText);
                            },
                            (errorMessage) => {
                                // Per-frame decode failures are normal; ignore them
                            }
                        ).then(() => {
                            this.scanning = true;
                            // Enumerate cameras after permission is granted so labels are visible
                            if (this.cameras.length === 0) {
                                Html5Qrcode.getCameras().then(devices => {
                                    this.cameras = devices;
                                }).catch(e => console.warn("Failed to get cameras after start", e));
                            }
                        }).catch(err => {
                            console.error("Unable to start scanner:", err);
                            // Provide an actionable error with HTTP/HTTPS hint
                            const isInsecure = !window.isSecureContext;
                            this.state = 'error';
                            this.message = isInsecure
                                ? "Camera requires HTTPS. Open this page over a secure (https://) connection."
                                : "Could not start camera. Please grant camera permission in your browser settings and try again.";
                        });
                    },

                    stopScanner() {
                        if (!this.scanning) return Promise.resolve();

                        return this.html5QrCode.stop().then(() => {
                            this.scanning = false;
                            // F6: Release camera hardware, then re-create the instance.
                            // clear() destroys the internal renderer — a new Html5Qrcode
                            // instance is required before the next startScanner() call.
                            try {
                                this.html5QrCode.clear();
                                this.html5QrCode = new Html5Qrcode("reader");
                            } catch (e) { /* safe to ignore */ }
                        }).catch(err => {
                            console.error("Unable to stop scanner", err);
                        });
                    },

                    onQrCodeSuccess(decodedText) {
                        // Race condition guard: ignore subsequent frame hits while a scan is being processed
                        if (this.isProcessing) return;
                        this.isProcessing = true;
                        // Pause scanner and call server to verify QR payload
                        this.stopScanner().then(() => {
                            this.verifyQrPayload(decodedText);
                        });
                    },

                    verifyQrPayload(payload) {
                        fetch('{{ route('attendance.verify-scan') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ payload: payload })
                        })
                            .then(res => {
                                // F3 + F9: Validate HTTP status before parsing JSON.
                                // A 419 means the CSRF session has expired — surface a
                                // human-readable message rather than crashing on HTML.
                                if (res.status === 419) {
                                    throw new Error('session_expired');
                                }
                                if (!res.ok) {
                                    throw new Error('server_error_' + res.status);
                                }
                                return res.json();
                            })
                            .then(data => {
                                if (data.status === 'success') {
                                    this.state = 'verification';
                                    this.student = data.student;
                                    this.examId = data.exam_id;
                                } else if (data.status === 'duplicate') {
                                    this.state = 'duplicate';
                                    this.message = data.message;
                                    this.student = data.student;
                                } else {
                                    this.state = 'error';
                                    this.message = data.message || "Invalid QR payload.";
                                }
                            })
                            .catch(err => {
                                console.error("Network verification error", err);
                                if (err.message === 'session_expired') {
                                    this.state = 'error';
                                    this.message = "Your session has expired. Please refresh the page and log in again before scanning.";
                                } else {
                                    this.state = 'error';
                                    this.message = "System offline or verification timed out.";
                                }
                            });
                    },

                    markPresent() {
                        fetch('{{ route('attendance.mark-present') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                student_id: this.student.id,
                                exam_id: this.examId
                            })
                        })
                            .then(res => {
                                // F3 + F9: Validate HTTP status before parsing JSON.
                                if (res.status === 419) {
                                    throw new Error('session_expired');
                                }
                                if (!res.ok) {
                                    throw new Error('server_error_' + res.status);
                                }
                                return res.json();
                            })
                            .then(data => {
                                if (data.status === 'success') {
                                    this.state = 'success';
                                    this.message = data.message;
                                    this.successData = data.data;
                                    this.updateLocalCounter();
                                } else {
                                    this.state = 'error';
                                    this.message = data.message || "Could not mark present.";
                                }
                            })
                            .catch(err => {
                                console.error("Error marking present", err);
                                if (err.message === 'session_expired') {
                                    this.state = 'error';
                                    this.message = "Your session has expired. Please refresh the page and log in again.";
                                } else {
                                    this.state = 'error';
                                    this.message = "Connection error. Attendance was not logged.";
                                }
                            });
                    },

                    resetScanner() {
                        this.state = 'idle';
                        this.student = {};
                        this.successData = {};
                        this.isProcessing = false; // Release the processing lock for the next scan
                        // Delay start to let Alpine finish any in-flight x-show transitions
                        // before the scanner reinitialises and changes the DOM.
                        setTimeout(() => { this.startScanner(); }, 200);
                    },

                    updateLocalCounter() {
                        // F1: Fetch only the count from the dedicated lightweight API endpoint
                        // instead of scraping the full attendance history HTML page.
                        fetch('{{ route('attendance.count') }}')
                            .then(res => {
                                if (!res.ok) throw new Error('count_fetch_failed');
                                return res.json();
                            })
                            .then(data => {
                                if (typeof data.count !== 'undefined') {
                                    document.querySelector('#scan-counter').textContent = data.count;
                                }
                            })
                            .catch(err => console.error("Error updating scan count", err));
                    }
                };
            }
        </script>
    @endpush
@endsection
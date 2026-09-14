<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Terminated | YES INDIA ERMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between antialiased relative">
    <x-public-background />

    <div class="max-w-xl w-full mx-auto space-y-6 my-auto py-10 px-4 relative z-10">
        <!-- Logo -->
        <div class="text-center">
            <img src="{{ asset('logo-w.png') }}" alt="YES Genius"
                class="h-10 sm:h-12 w-auto max-w-[200px] object-contain mx-auto mb-2 filter drop-shadow-[0_2px_10px_rgba(0,0,0,0.3)]">
        </div>

        <!-- Termination Notice Card -->
        <div class="bg-slate-900/90 border border-rose-500/40 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-rose-950/40 text-center space-y-6">
            <!-- Icon -->
            <div class="w-16 h-16 rounded-3xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-400 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl font-bold text-white">Examination Session Terminated</h1>
                <p class="text-xs text-rose-300 leading-relaxed">
                    Your examination session was forcibly terminated due to repeated anti-cheating or security policy violations.
                </p>
            </div>

            @if($session)
            <!-- Incident Details -->
            <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-4 text-left space-y-2.5 text-xs">
                <div class="flex justify-between pb-2 border-b border-slate-800/80">
                    <span class="text-slate-400">Candidate Name:</span>
                    <strong class="text-white">{{ $session->student->name }}</strong>
                </div>
                <div class="flex justify-between pb-2 border-b border-slate-800/80">
                    <span class="text-slate-400">Registration Number:</span>
                    <span class="font-mono text-indigo-300 font-semibold">{{ $session->student->registration_number }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b border-slate-800/80">
                    <span class="text-slate-400">Examination:</span>
                    <span class="text-slate-200 text-right">{{ $session->exam->name }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b border-slate-800/80">
                    <span class="text-slate-400">Termination Reason:</span>
                    <span class="text-rose-400 font-medium text-right">{{ $session->termination_reason ?: 'Excessive security violations' }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b border-slate-800/80">
                    <span class="text-slate-400">Total Violations Logged:</span>
                    <span class="font-mono text-rose-400 font-bold">{{ $session->violations_count }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Timestamp:</span>
                    <span class="font-mono text-slate-300">{{ $session->finished_at ? $session->finished_at->format('d M Y, H:i:s') : now()->format('d M Y, H:i:s') }}</span>
                </div>
            </div>
            @endif

            <p class="text-[11px] text-slate-400 leading-relaxed">
                All event logs, timestamps, and camera state records have been forwarded to the Board of Examiners for administrative review. If you believe this occurred in error, contact your institution's examination coordinator.
            </p>

            <div>
                <a href="{{ route('home') }}"
                   class="inline-flex items-center justify-center w-full py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors">
                    Return to Portal
                </a>
            </div>
        </div>
    </div>

    <!-- Official ERMS Public Footer Component -->
    <x-public-footer page="exam-terminated" />
</body>
</html>

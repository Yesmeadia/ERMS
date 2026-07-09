<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERMS - Examination Registration Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Tailwind CSS & Fonts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #090d16;
            margin: 0;
            padding: 0;
        }

        .auth-container {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 100vh;
            width: 100%;
        }

        @media (min-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr 1fr;
                /* Split 50/50 on tablets and desktops */
            }
        }

        .auth-left {
            background-color: #090d16;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2rem;
            min-height: 100vh;
            box-sizing: border-box;
        }

        @media (min-width: 640px) {
            .auth-left {
                padding: 3rem;
            }
        }

        .auth-right {
            background-color: #05080c;
            border-left: 1px solid #1e293b;
            display: none;
            /* Hidden on mobile */
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        @media (min-width: 768px) {
            .auth-right {
                display: flex;
                /* Visible on tablets and desktops */
            }
        }

        /* Seamless technical blueprint line grids */
        .auth-right::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(0, 212, 255, 0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 212, 255, 0.015) 1px, transparent 1px);
            background-size: 50px 50px;
            background-position: center;
            z-index: 1;
        }

        /* Floating Academic Disciplinary Streams */
        .academic-nodes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 2;
            pointer-events: none;
            opacity: 0.18;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            font-weight: bold;
            color: #00d4ff;
        }

        .node {
            position: absolute;
            bottom: -80px;
            white-space: nowrap;
            animation: logicFloat 16s linear infinite;
        }

        /* Staggered positioning across 24 data stream channels */
        .n1 {
            left: 2%;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        .n2 {
            left: 6%;
            animation-delay: 4s;
            animation-duration: 22s;
        }

        .n3 {
            left: 10%;
            animation-delay: 8s;
            animation-duration: 15s;
        }

        .n4 {
            left: 15%;
            animation-delay: 2s;
            animation-duration: 25s;
        }

        .n5 {
            left: 19%;
            animation-delay: 11s;
            animation-duration: 19s;
        }

        .n6 {
            left: 23%;
            animation-delay: 5s;
            animation-duration: 21s;
        }

        .n7 {
            left: 28%;
            animation-delay: 1s;
            animation-duration: 17s;
        }

        .n8 {
            left: 32%;
            animation-delay: 7s;
            animation-duration: 24s;
        }

        .n9 {
            left: 36%;
            animation-delay: 13s;
            animation-duration: 20s;
        }

        .n10 {
            left: 41%;
            animation-delay: 3s;
            animation-duration: 16s;
        }

        .n11 {
            left: 45%;
            animation-delay: 9s;
            animation-duration: 23s;
        }

        .n12 {
            left: 49%;
            animation-delay: 14s;
            animation-duration: 18s;
        }

        .n13 {
            left: 53%;
            animation-delay: 0.5s;
            animation-duration: 21s;
        }

        .n14 {
            left: 57%;
            animation-delay: 6s;
            animation-duration: 15s;
        }

        .n15 {
            left: 61%;
            animation-delay: 11s;
            animation-duration: 26s;
        }

        .n16 {
            left: 66%;
            animation-delay: 2.5s;
            animation-duration: 19s;
        }

        .n17 {
            left: 70%;
            animation-delay: 8s;
            animation-duration: 22s;
        }

        .n18 {
            left: 74%;
            animation-delay: 13.5s;
            animation-duration: 17s;
        }

        .n19 {
            left: 78%;
            animation-delay: 4.5s;
            animation-duration: 24s;
        }

        .n20 {
            left: 83%;
            animation-delay: 10s;
            animation-duration: 20s;
        }

        .n21 {
            left: 87%;
            animation-delay: 1s;
            animation-duration: 16s;
        }

        .n22 {
            left: 91%;
            animation-delay: 5.5s;
            animation-duration: 23s;
        }

        .n23 {
            left: 95%;
            animation-delay: 12s;
            animation-duration: 18s;
        }

        .n24 {
            left: 98%;
            animation-delay: 3.5s;
            animation-duration: 21s;
        }

        /* LOGIC FLOATING ANIMATION */
        @keyframes logicFloat {
            0% {
                transform: translateY(0) scale(0.9);
                opacity: 0;
            }

            15% {
                opacity: 1;
            }

            85% {
                opacity: 1;
            }

            100% {
                transform: translateY(-115vh) scale(1.05);
                opacity: 0;
            }
        }

        .auth-form-wrapper {
            margin-top: auto;
            margin-bottom: auto;
            padding-top: 2rem;
            padding-bottom: 2rem;
            width: 100%;
        }

        .auth-form-content {
            width: 100%;
            max-width: 24rem;
            /* max-w-sm */
            margin-left: auto;
            margin-right: auto;
        }
    </style>
    <!-- Alpine.js (via CDN for stability) -->
    <script @nonce defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="h-full antialiased text-slate-100 flex overflow-x-hidden">
    <div class="auth-container">
        <!-- LEFT COLUMN: Auth Form & Logo -->
        <div class="auth-left">

            <!-- Content Area (Forms) -->
            <div class="auth-form-wrapper">
                <div class="auth-form-content">
                    @yield('content')
                </div>
            </div>

            <!-- Footer Copyright -->
            <div>
                <p class="text-xs text-slate-500">&copy; {{ date('Y') }} YES INDIA FOUNDATION. All rights reserved.</p>
            </div>
        </div>

        <!-- RIGHT COLUMN: Deep Slate Branding & Matrix Rain -->
        <div class="auth-right select-none">
            <!-- Background Neon Glowing Orbs -->
            <div
                class="absolute -top-40 -right-40 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl pointer-events-none">
            </div>
            <div
                class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none">
            </div>

            <!-- Academic Matrix Rain Nodes -->
            <div class="academic-nodes">
                <div class="node n1">∫e^x dx = e^x + C</div>
                <div class="node n5">x = [-b ± √(b² - 4ac)] / 2a</div>
                <div class="node n9">lim (x→0) sin(x)/x = 1</div>
                <div class="node n13">A = πr² // calculus</div>
                <div class="node n17">f'(x) = n·xⁿ⁻¹</div>
                <div class="node n21">e^(iπ) + 1 = 0</div>

                <div class="node n2">c = 299,792,458 m/s</div>
                <div class="node n6">∇ × B = μ₀J + μ₀ε₀(∂E/∂t)</div>
                <div class="node n10">G = 6.674×10⁻¹¹ N·m²/kg²</div>
                <div class="node n14">h = 6.626×10⁻³⁴ J·s</div>
                <div class="node n18">F = G(m₁m₂)/r²</div>
                <div class="node n22">E_k = 1/2 m·v²</div>

                <div class="node n3">[Og] 118 | Oganesson</div>
                <div class="node n7">1s² 2s² 2p⁶ 3s¹ [Na]</div>
                <div class="node n11">Au [Z=79] Group 11</div>
                <div class="node n15">H₂O + CO₂ → H₂CO₃</div>
                <div class="node n19">PV = nRT // Ideal Gas</div>
                <div class="node n23">[U] 92 | Uranium</div>

                <div class="node n4">Q: Deepest trench? A: Mariana</div>
                <div class="node n8">Q: DNA shape? A: Double Helix</div>
                <div class="node n12">Q: Magna Carta? A: 1215 AD</div>
                <div class="node n16">Q: Largest desert? A: Antarctica</div>
                <div class="node n20">Q: Speed of sound? A: 343m/s</div>
                <div class="node n24">Q: First element? A: Hydrogen</div>
            </div>

            <!-- Center Branding Wrapper -->
            <div class="w-full max-w-md relative z-10 text-center px-6">
                <div class="mb-8 flex justify-center">
                    @if(file_exists(public_path('logo-w.png')))
                        <img src="{{ asset('logo-w.png') }}" alt="Logo"
                            class="max-w-[200px] w-full h-auto filter drop-shadow-[0_0_25px_rgba(0,212,255,0.15)]">
                    @elseif(file_exists(public_path('logo.png')))
                        <img src="{{ asset('logo.png') }}" alt="Logo"
                            class="max-w-[200px] w-full h-auto filter drop-shadow-[0_0_25px_rgba(0,212,255,0.15)]">
                    @else
                        <div
                            class="flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 font-bold text-3xl">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('components.password-strength-policy')
</body>

</html>
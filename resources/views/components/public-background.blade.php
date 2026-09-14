<style>
    /* ── PUBLIC BACKGROUND: CHECKED GRID, GLOWING ORBS & ACADEMIC MATRIX RAIN ── */
    .bg-wrap {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .bg-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
        background-size: 52px 52px;
    }

    .orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
        animation: orbDrift 20s ease-in-out infinite alternate;
        pointer-events: none;
    }

    .orb-1 {
        width: 560px;
        height: 560px;
        background: rgba(99, 102, 241, 0.13);
        top: -160px;
        left: -120px;
        animation-duration: 18s;
    }

    .orb-2 {
        width: 500px;
        height: 500px;
        background: rgba(168, 85, 247, 0.10);
        top: 25%;
        right: -140px;
        animation-duration: 24s;
        animation-delay: -9s;
    }

    .orb-3 {
        width: 420px;
        height: 420px;
        background: rgba(241, 196, 15, 0.06);
        bottom: -100px;
        left: 28%;
        animation-duration: 22s;
        animation-delay: -5s;
    }

    @keyframes orbDrift {
        0% {
            transform: translate(0, 0) scale(1);
        }

        50% {
            transform: translate(40px, -50px) scale(1.1);
        }

        100% {
            transform: translate(-25px, 35px) scale(0.95);
        }
    }

    /* Academic Matrix Rain Nodes */
    .rain-wrap {
        position: absolute;
        inset: 0;
        overflow: hidden;
        opacity: 0.12;
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        font-weight: 700;
        color: #818cf8;
        pointer-events: none;
    }

    .rain-item {
        position: absolute;
        bottom: -60px;
        white-space: nowrap;
        animation: rainFall linear infinite;
    }

    @keyframes rainFall {
        from {
            transform: translateY(0);
            opacity: 0;
        }

        10% {
            opacity: 1;
        }

        90% {
            opacity: 1;
        }

        to {
            transform: translateY(-110vh);
            opacity: 0;
        }
    }

    .ri-1 { left: 2%; animation-duration: 22s; }
    .ri-2 { left: 8%; animation-duration: 26s; animation-delay: -5s; }
    .ri-3 { left: 16%; animation-duration: 20s; animation-delay: -9s; }
    .ri-4 { left: 24%; animation-duration: 28s; animation-delay: -2s; }
    .ri-5 { left: 32%; animation-duration: 19s; animation-delay: -12s; }
    .ri-6 { left: 40%; animation-duration: 24s; animation-delay: -6s; }
    .ri-7 { left: 48%; animation-duration: 21s; animation-delay: -1s; }
    .ri-8 { left: 56%; animation-duration: 25s; animation-delay: -8s; }
    .ri-9 { left: 63%; animation-duration: 18s; animation-delay: -14s; }
    .ri-10 { left: 70%; animation-duration: 23s; animation-delay: -3s; }
    .ri-11 { left: 77%; animation-duration: 27s; animation-delay: -7s; }
    .ri-12 { left: 83%; animation-duration: 17s; animation-delay: -11s; }
    .ri-13 { left: 89%; animation-duration: 29s; animation-delay: -4s; }
    .ri-14 { left: 94%; animation-duration: 16s; animation-delay: -16s; }
    .ri-15 { left: 5%; animation-duration: 31s; animation-delay: -18s; }
    .ri-16 { left: 45%; animation-duration: 15s; animation-delay: -10s; }
</style>

<div class="bg-wrap" aria-hidden="true">
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="rain-wrap">
        <div class="rain-item ri-1">∫e^x dx = e^x + C</div>
        <div class="rain-item ri-2">c = 299,792,458 m/s</div>
        <div class="rain-item ri-3">Au [Z=79] Group 11</div>
        <div class="rain-item ri-4">x = [-b ± √(b²-4ac)] / 2a</div>
        <div class="rain-item ri-5">Magna Carta · 1215 AD</div>
        <div class="rain-item ri-6">∇ × B = μ₀J + μ₀ε₀(∂E/∂t)</div>
        <div class="rain-item ri-7">H₂O + CO₂ → H₂CO₃</div>
        <div class="rain-item ri-8">lim (x→0) sin(x)/x = 1</div>
        <div class="rain-item ri-9">e^(iπ) + 1 = 0</div>
        <div class="rain-item ri-10">F = G·m₁m₂ / r²</div>
        <div class="rain-item ri-11">pV = nRT · Ideal Gas</div>
        <div class="rain-item ri-12">DNA: A-T, G-C Pairing</div>
        <div class="rain-item ri-13">λ = h / (mv) de Broglie</div>
        <div class="rain-item ri-14">Renaissance · 14th–17th C</div>
        <div class="rain-item ri-15">∑(1/n²) = π²/6 Euler</div>
        <div class="rain-item ri-16">Photosynthesis: 6CO₂+6H₂O</div>
    </div>
</div>

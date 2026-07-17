<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="YES Genius National Level Talent Search — A nationwide academic competition to discover, celebrate and nurture academic and cognitive abilities among students across India. Held at 40+ centres nationwide.">
    <title>YES Genius National Level Talent Search | YES India Foundation</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script @nonce defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #060810;
            --surface: rgba(12, 16, 28, 0.75);
            --border: rgba(255, 255, 255, 0.06);
            --border-hover: rgba(99, 102, 241, 0.35);
            --text: #e2e8f0;
            --muted: #64748b;
            --indigo: #6366f1;
            --purple: #a855f7;
            --amber: #f59e0b;
            --cyan: #06b6d4;
            --gold: #f1c40f;
        }

        html {
            background: var(--bg);
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 99px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }

        /* ── BACKGROUND ── */
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

        /* Academic rain */
        .rain-wrap {
            position: absolute;
            inset: 0;
            overflow: hidden;
            opacity: 0.10;
            font-family: 'Courier New', monospace;
            font-size: 10px;
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

        .ri-1 {
            left: 2%;
            animation-duration: 22s;
        }

        .ri-2 {
            left: 8%;
            animation-duration: 26s;
            animation-delay: -5s;
        }

        .ri-3 {
            left: 16%;
            animation-duration: 20s;
            animation-delay: -9s;
        }

        .ri-4 {
            left: 24%;
            animation-duration: 28s;
            animation-delay: -2s;
        }

        .ri-5 {
            left: 32%;
            animation-duration: 19s;
            animation-delay: -12s;
        }

        .ri-6 {
            left: 40%;
            animation-duration: 24s;
            animation-delay: -6s;
        }

        .ri-7 {
            left: 48%;
            animation-duration: 21s;
            animation-delay: -1s;
        }

        .ri-8 {
            left: 56%;
            animation-duration: 25s;
            animation-delay: -8s;
        }

        .ri-9 {
            left: 63%;
            animation-duration: 18s;
            animation-delay: -14s;
        }

        .ri-10 {
            left: 70%;
            animation-duration: 23s;
            animation-delay: -3s;
        }

        .ri-11 {
            left: 77%;
            animation-duration: 27s;
            animation-delay: -7s;
        }

        .ri-12 {
            left: 83%;
            animation-duration: 17s;
            animation-delay: -11s;
        }

        .ri-13 {
            left: 89%;
            animation-duration: 29s;
            animation-delay: -4s;
        }

        .ri-14 {
            left: 94%;
            animation-duration: 16s;
            animation-delay: -16s;
        }

        .ri-15 {
            left: 5%;
            animation-duration: 31s;
            animation-delay: -18s;
        }

        .ri-16 {
            left: 45%;
            animation-duration: 15s;
            animation-delay: -10s;
        }

        /* ── LAYOUT ── */
        .page {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── HERO ── */
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 90px 24px 70px;
            animation: fadeUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 18px;
            border-radius: 100px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.28);
            font-size: 12px;
            font-weight: 700;
            color: #a5b4fc;
            letter-spacing: 0.04em;
            margin-bottom: 30px;
        }

        .hero-badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #818cf8;
            animation: pulse 1.8s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.4;
                transform: scale(0.7);
            }
        }

        .hero-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .hero-logo-item {
            height: 52px;
            width: auto;
            object-fit: contain;
            opacity: 0.88;
            transition: opacity 0.25s, transform 0.25s;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
        }

        .hero-logo-item:hover {
            opacity: 1;
            transform: scale(1.04);
        }

        .hero-logos-divider {
            width: 1px;
            height: 36px;
            background: rgba(255, 255, 255, 0.12);
        }

        .hero-title {
            font-size: clamp(2.4rem, 5.5vw, 4.4rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.06;
            letter-spacing: -0.03em;
            margin-bottom: 24px;
            max-width: 860px;
        }

        .hero-gradient {
            background:  #fbbf24;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-desc {
            font-size: 16.5px;
            color: #94a3b8;
            line-height: 1.75;
            font-weight: 400;
            max-width: 640px;
            margin-bottom: 42px;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-primary {
            padding: 13px 30px;
            border-radius: 13px;
            background: linear-gradient(135deg, #6366f1, #4f52d6);
            color: #fff;
            font-size: 14.5px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 0 28px rgba(99, 102, 241, 0.38);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5);
        }

        .btn-ghost {
            padding: 13px 30px;
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            font-size: 14.5px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            transform: translateY(-2px);
        }

        /* ── HERO QUICK STATS ── */
        .hero-stats {
            display: flex;
            align-items: center;
            gap: 0;
            margin-top: 56px;
            border-radius: 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            backdrop-filter: blur(20px);
            overflow: hidden;
            max-width: 720px;
            width: 100%;
        }

        .hero-stat {
            flex: 1;
            padding: 22px 16px;
            text-align: center;
            border-right: 1px solid var(--border);
        }

        .hero-stat:last-child {
            border-right: none;
        }

        .hero-stat-num {
            font-size: clamp(1.5rem, 2.5vw, 2rem);
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .hero-stat-lbl {
            font-size: 10.5px;
            color: var(--muted);
            font-weight: 600;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* ── SECTION BASE ── */
        .section {
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .section-sep {
            margin-bottom: 80px;
        }

        .section-tag {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .section-tag-line {
            flex: 1;
            max-width: 60px;
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
        }

        .section-tag span {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .section-title {
            font-size: clamp(1.5rem, 3vw, 2.2rem);
            font-weight: 800;
            color: #fff;
            text-align: center;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }

        .section-sub {
            text-align: center;
            color: #64748b;
            font-size: 15px;
            margin-bottom: 52px;
            max-width: 560px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ── ABOUT SECTION ── */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            align-items: center;
            margin-bottom: 80px;
        }

        .about-text {
            color: #94a3b8;
            font-size: 15.5px;
            line-height: 1.8;
        }

        .about-text p {
            margin-bottom: 16px;
        }

        .about-text p:last-child {
            margin-bottom: 0;
        }

        .about-text strong {
            color: #e2e8f0;
            font-weight: 700;
        }

        .about-card {
            background: linear-gradient(145deg, rgba(245, 158, 11, 0.04) 0%, rgba(8, 12, 22, 0.95) 60%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(245, 158, 11, 0.15);
            border-radius: 28px;
            padding: 36px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.45);
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
        }

        .about-card:hover {
            border-color: rgba(245, 158, 11, 0.35);
            box-shadow: 0 40px 90px rgba(245, 158, 11, 0.1);
            transform: translateY(-4px);
        }

        /* background decorative gold orb */
        .about-card-orb {
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.14), transparent 70%);
            top: -60px;
            right: -60px;
            pointer-events: none;
            z-index: 0;
            animation: aboutOrbPulse 8s ease-in-out infinite alternate;
        }

        @keyframes aboutOrbPulse {
            0% { opacity: 0.5; transform: scale(0.9) translate(10px, -10px); }
            100% { opacity: 1; transform: scale(1.1) translate(-10px, 10px); }
        }

        /* Corner accents for gold card */
        .about-corner {
            position: absolute;
            width: 20px;
            height: 20px;
            pointer-events: none;
            z-index: 3;
            border-color: rgba(245, 158, 11, 0.3);
        }
        .about-corner-tl { top: 12px; left: 12px; border-top: 1.5px solid; border-left: 1.5px solid; border-radius: 3px 0 0 0; }
        .about-corner-tr { top: 12px; right: 12px; border-top: 1.5px solid; border-right: 1.5px solid; border-radius: 0 3px 0 0; }
        .about-corner-bl { bottom: 12px; left: 12px; border-bottom: 1.5px solid; border-left: 1.5px solid; border-radius: 0 0 0 3px; }
        .about-corner-br { bottom: 12px; right: 12px; border-bottom: 1.5px solid; border-right: 1.5px solid; border-radius: 0 0 3px 0; }

        .about-feature {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
            transition: all 0.25s ease;
        }

        .about-feature:last-child {
            margin-bottom: 0;
        }

        .about-feature:hover {
            transform: translateX(6px);
        }

        .about-feature-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.25s ease;
        }

        .about-feature:hover .about-feature-icon {
            transform: scale(1.1) rotate(4deg);
        }

        .about-feature-title {
            font-size: 15px;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }

        .about-feature-desc {
            font-size: 12.5px;
            color: #64748b;
            line-height: 1.55;
        }

        /* ── WINNERS SECTION ── */
        .winners-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 36px;
        }

        .winner-tab {
            padding: 7px 18px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid var(--border);
            background: var(--surface);
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .winner-tab.active,
        .winner-tab:hover {
            border-color: rgba(99, 102, 241, 0.4);
            color: #a5b4fc;
            background: rgba(99, 102, 241, 0.1);
        }

        .winners-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .winner-card {
            background: var(--surface);
            backdrop-filter: blur(18px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 0;
            position: relative;
            overflow: hidden;
            transition: border-color 0.25s, transform 0.25s, box-shadow 0.25s;
        }

        .winner-card-slider {
            position: relative;
            width: 100%;
            height: 140px;
            overflow: hidden;
        }

        .winner-card-slider-track {
            display: flex;
            width: 500%;
            height: 100%;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .winner-card-img {
            width: 20%;
            height: 100%;
            object-fit: cover;
            flex-shrink: 0;
            display: block;
            filter: brightness(0.82) saturate(1.1);
            transition: filter 0.3s, transform 0.35s;
        }

        .winner-card:hover .winner-card-img {
            filter: brightness(0.95) saturate(1.2);
            transform: scale(1.03);
        }

        .winner-card-body {
            padding: 18px 20px 20px;
        }

        .winner-card:hover {
            border-color: var(--border-hover);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
        }

        .winner-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            pointer-events: none;
        }

        .winner-rank {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 900;
            margin-bottom: 14px;
            flex-shrink: 0;
        }

        .rank-1 {
            background: rgba(241, 196, 15, 0.15);
            border: 1px solid rgba(241, 196, 15, 0.35);
            color: #f1c40f;
        }

        .rank-2 {
            background: rgba(148, 163, 184, 0.15);
            border: 1px solid rgba(148, 163, 184, 0.35);
            color: #94a3b8;
        }

        .rank-3 {
            background: rgba(180, 113, 60, 0.15);
            border: 1px solid rgba(180, 113, 60, 0.35);
            color: #cd7f32;
        }

        .winner-name {
            font-size: 16px;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }

        .winner-school {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .winner-score {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
        }

        .winner-cat-tag {
            display: inline-flex;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .no-winners {
            grid-column: 1/-1;
            text-align: center;
            padding: 60px 20px;
            background: var(--surface);
            border: 1px dashed var(--border);
            border-radius: 20px;
            color: var(--muted);
            font-size: 14px;
        }

        .winners-fallback-container {
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--border);
            width: 100%;
            display: block;
        }

        .winners-fallback-slider {
            position: relative;
            width: 100%;
            overflow: hidden;
        }

        .winners-fallback-slider-track {
            display: flex;
            width: 500%;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .winners-fallback-img {
            width: 20%;
            height: auto;
            flex-shrink: 0;
            display: block;
        }

        @media (max-width: 900px) {
            .winners-fallback-container {
                margin-left: -16px;
                margin-right: -16px;
                width: calc(100% + 32px);
                border-radius: 0;
                border-left: none;
                border-right: none;
            }
        }

        /* ── GALLERY PREVIEW — Modern Bento Grid ── */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: 240px 200px;
            gap: 16px;
            padding: 4px;
        }

        .gallery-item {
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: var(--surface);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .gallery-item:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 30px 60px rgba(99, 102, 241, 0.25);
            border-color: rgba(99, 102, 241, 0.4);
            z-index: 5;
        }

        /* Modern Asymmetric Bento cell positions */
        .gi-feat  { grid-column: 1 / 3; grid-row: 1 / 3; } /* Large featured box on left */
        .gi-tr    { grid-column: 3;     grid-row: 1; }
        .gi-tl    { grid-column: 4;     grid-row: 1; }
        .gi-bl    { grid-column: 3;     grid-row: 2; }
        .gi-bm    { grid-column: 4;     grid-row: 2; }

        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            filter: brightness(0.85) contrast(1.05);
        }

        .gallery-item:hover .gallery-img {
            transform: scale(1.08);
            filter: brightness(1) contrast(1.05);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(6, 8, 16, 0.9) 0%, rgba(6, 8, 16, 0.3) 50%, transparent 100%);
            opacity: 0.85;
            transition: opacity 0.3s, background 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 24px;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
            background: linear-gradient(to top, rgba(6, 8, 16, 0.95) 0%, rgba(6, 8, 16, 0.4) 60%, transparent 100%);
        }

        .gallery-badge {
            align-self: flex-start;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.9);
            transform: translateY(4px);
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .gallery-item:hover .gallery-badge {
            transform: translateY(0);
            opacity: 1;
            background: rgba(99, 102, 241, 0.25);
            border-color: rgba(99, 102, 241, 0.4);
            color: #fff;
        }

        .gallery-caption {
            font-size: 14.5px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.01em;
            transform: translateY(6px);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-caption {
            transform: translateY(0);
        }

        .gallery-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }

        .gallery-cta-wrap {
            text-align: center;
            margin-top: 32px;
        }

        .btn-outline-indigo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 32px;
            border-radius: 13px;
            border: 1px solid rgba(99, 102, 241, 0.4);
            color: #818cf8;
            font-size: 14.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            background: rgba(99, 102, 241, 0.07);
        }

        .btn-outline-indigo:hover {
            background: rgba(99, 102, 241, 0.18);
            border-color: rgba(99, 102, 241, 0.7);
            color: #c7d2fe;
            transform: translateY(-2px);
        }

        /* ── PUBLIC SERVICES ── */
        .utilities-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            position: relative;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
        }

        /* ── Hall Ticket Panel ── */
        .util-panel {
            position: relative;
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 460px;
        }

        .util-panel-cyan {
            background: linear-gradient(145deg, rgba(6,182,212,0.09) 0%, rgba(8,12,22,0.95) 55%);
            border-right: 1px solid rgba(255,255,255,0.05);
        }

        .util-panel-purple {
            background: linear-gradient(145deg, rgba(168,85,247,0.09) 0%, rgba(8,12,22,0.95) 55%);
        }

        /* diagonal split line */
        .util-divider {
            position: absolute;
            top: 0;
            right: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(to bottom, transparent, rgba(99,102,241,0.35) 40%, rgba(168,85,247,0.35) 60%, transparent);
            z-index: 2;
        }

        /* background decorative orb */
        .util-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            pointer-events: none;
            z-index: 0;
        }

        .util-orb-cyan {
            width: 340px;
            height: 340px;
            background: radial-gradient(circle, rgba(6,182,212,0.18), transparent 70%);
            bottom: -80px;
            left: -60px;
            animation: utilOrbPulse 6s ease-in-out infinite alternate;
        }

        .util-orb-purple {
            width: 340px;
            height: 340px;
            background: radial-gradient(circle, rgba(168,85,247,0.18), transparent 70%);
            bottom: -80px;
            right: -60px;
            animation: utilOrbPulse 6s ease-in-out infinite alternate-reverse;
        }

        @keyframes utilOrbPulse {
            0% { opacity: 0.6; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.15); }
        }

        /* decorative scan-line strip */
        .util-scanlines {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 3px,
                rgba(255,255,255,0.012) 3px,
                rgba(255,255,255,0.012) 4px
            );
            pointer-events: none;
            z-index: 0;
        }

        /* corner accent brackets */
        .util-corner {
            position: absolute;
            width: 24px;
            height: 24px;
            pointer-events: none;
            z-index: 3;
        }
        .util-corner-tl { top: 14px; left: 14px; border-top: 2px solid; border-left: 2px solid; border-radius: 4px 0 0 0; }
        .util-corner-tr { top: 14px; right: 14px; border-top: 2px solid; border-right: 2px solid; border-radius: 0 4px 0 0; }
        .util-corner-bl { bottom: 14px; left: 14px; border-bottom: 2px solid; border-left: 2px solid; border-radius: 0 0 0 4px; }
        .util-corner-br { bottom: 14px; right: 14px; border-bottom: 2px solid; border-right: 2px solid; border-radius: 0 0 4px 0; }
        .corner-cyan { border-color: rgba(6,182,212,0.3); }
        .corner-purple { border-color: rgba(168,85,247,0.3); }

        /* top coloured glow strip */
        .util-top-bar {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            z-index: 4;
        }
        .util-top-bar-cyan  { background: linear-gradient(90deg, transparent, rgba(6,182,212,0.9), transparent); }
        .util-top-bar-purple { background: linear-gradient(90deg, transparent, rgba(168,85,247,0.9), transparent); }

        /* utility badge pill */
        .util-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 13px;
            border-radius: 100px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            width: fit-content;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }
        .util-badge-cyan   { background: rgba(6,182,212,0.12); border: 1px solid rgba(6,182,212,0.3); color: #67e8f9; }
        .util-badge-purple { background: rgba(168,85,247,0.12); border: 1px solid rgba(168,85,247,0.3); color: #d8b4fe; }

        /* large icon shape */
        .util-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }
        .util-panel:hover .util-icon { transform: scale(1.12) rotate(-4deg); }
        .util-icon-cyan   { background: rgba(6,182,212,0.15); border: 1px solid rgba(6,182,212,0.3); color: #22d3ee; }
        .util-icon-purple { background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.3); color: #c084fc; }

        .util-title {
            font-size: 22px;
            font-weight: 900;
            color: #f1f5f9;
            margin-bottom: 10px;
            letter-spacing: -0.025em;
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }

        .util-desc {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        /* decorative floating stat chips */
        .util-stat-row {
            display: flex;
            gap: 10px;
            margin-bottom: 26px;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
        }
        .util-stat-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 13px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
        }
        .util-stat-chip-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .dot-cyan   { background: #22d3ee; box-shadow: 0 0 6px rgba(34,211,238,0.8); }
        .dot-purple { background: #c084fc; box-shadow: 0 0 6px rgba(192,132,252,0.8); }
        .dot-green  { background: #4ade80; box-shadow: 0 0 6px rgba(74,222,128,0.8); }

        /* ── CARD stays for gateway section ── */
        .card {
            background: var(--surface);
            backdrop-filter: blur(18px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px;
            position: relative;
            overflow: hidden;
            transition: border-color 0.25s, transform 0.25s, box-shadow 0.25s;
        }

        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            background: radial-gradient(ellipse at top left, rgba(99, 102, 241, 0.07), transparent 60%);
            pointer-events: none;
        }

        .card:hover {
            border-color: var(--border-hover);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
        }

        .card-top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.6), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card:hover .card-top-bar {
            opacity: 1;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            flex-shrink: 0;
            transition: transform 0.25s;
        }

        .card:hover .card-icon {
            transform: scale(1.08);
        }

        .card-icon-indigo {
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.22);
            color: #818cf8;
        }

        .card-icon-purple {
            background: rgba(168, 85, 247, 0.12);
            border: 1px solid rgba(168, 85, 247, 0.22);
            color: #c084fc;
        }

        .card-icon-cyan {
            background: rgba(6, 182, 212, 0.12);
            border: 1px solid rgba(6, 182, 212, 0.22);
            color: #22d3ee;
        }

        .card-icon-amber {
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.22);
            color: #fbbf24;
        }

        .card-title {
            font-size: 17px;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }

        .card-desc {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.65;
        }

        /* ── verify input (used inside util-panel-cyan) ── */
        .verify-input-wrap {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: relative;
            z-index: 1;
        }

        .verify-input-row {
            display: flex;
            gap: 0;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(6,182,212,0.2);
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(12px);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .verify-input-row:focus-within {
            border-color: rgba(6,182,212,0.55);
            box-shadow: 0 0 0 3px rgba(6,182,212,0.1);
        }

        .verify-input {
            flex: 1;
            padding: 14px 18px;
            background: transparent;
            border: none;
            color: #e2e8f0;
            font-size: 13.5px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            outline: none;
        }

        .verify-input::placeholder {
            color: #334155;
            text-transform: none;
            letter-spacing: 0;
            font-weight: 400;
        }

        .btn-cyan {
            padding: 14px 22px;
            background: linear-gradient(135deg, rgba(6,182,212,0.9), rgba(8,145,178,0.9));
            border: none;
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.02em;
        }

        .btn-cyan:hover {
            background: linear-gradient(135deg, rgba(6,182,212,1), rgba(8,145,178,1));
            box-shadow: 0 0 20px rgba(6,182,212,0.4);
        }

        /* results CTA button */
        .btn-purple-solid {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 28px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(168,85,247,0.85), rgba(126,34,206,0.85));
            border: 1px solid rgba(168,85,247,0.4);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.25s;
            position: relative;
            z-index: 1;
            margin-top: auto;
            letter-spacing: 0.01em;
            box-shadow: 0 8px 28px rgba(168,85,247,0.2);
        }
        .btn-purple-solid:hover {
            background: linear-gradient(135deg, rgba(168,85,247,1), rgba(126,34,206,1));
            box-shadow: 0 12px 36px rgba(168,85,247,0.45);
            transform: translateY(-2px);
        }
        .btn-purple-solid svg { transition: transform 0.2s; }
        .btn-purple-solid:hover svg { transform: translateX(4px); }

        /* decorative QR grid motif for results panel */
        .util-qr-motif {
            position: absolute;
            bottom: 28px;
            right: 28px;
            width: 80px;
            height: 80px;
            opacity: 0.08;
            display: grid;
            grid-template-columns: repeat(7,1fr);
            grid-template-rows: repeat(7,1fr);
            gap: 3px;
            z-index: 0;
            pointer-events: none;
        }
        .util-qr-motif span {
            border-radius: 2px;
            background: #c084fc;
        }



        .btn-purple {
            padding: 12px 20px;
            border-radius: 12px;
            display: block;
            text-align: center;
            background: rgba(168, 85, 247, 0.15);
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: #c084fc;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-purple:hover {
            background: rgba(168, 85, 247, 0.25);
            border-color: rgba(168, 85, 247, 0.5);
            transform: translateY(-1px);
        }

        /* ── Responsive: stack panels on small screens ── */
        @media (max-width: 740px) {
            .utilities-grid {
                grid-template-columns: 1fr;
            }
            .util-divider { display: none; }
            .util-panel-cyan { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.05); }
            .util-panel { padding: 40px 28px; min-height: auto; }
        }

        /* ── GATEWAYS ── */
        .gateway-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 40px 100px rgba(0,0,0,0.45);
        }

        /* individual gateway lane */
        .gw-lane {
            position: relative;
            padding: 44px 36px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 400px;
            transition: background 0.3s;
        }

        .gw-lane-school {
            background: linear-gradient(160deg, rgba(99,102,241,0.09) 0%, rgba(8,12,22,0.96) 60%);
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        .gw-lane-board {
            background: linear-gradient(160deg, rgba(99,102,241,0.18) 0%, rgba(14,18,36,0.97) 55%);
            border-right: 1px solid rgba(99,102,241,0.15);
            border-left: 1px solid rgba(99,102,241,0.15);
            z-index: 1;
        }
        .gw-lane-invig {
            background: linear-gradient(160deg, rgba(6,182,212,0.09) 0%, rgba(8,12,22,0.96) 60%);
            border-left: 1px solid rgba(255,255,255,0.05);
        }

        /* glow orb per lane */
        .gw-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
            z-index: 0;
            animation: gwOrbPulse 7s ease-in-out infinite alternate;
        }
        .gw-orb-indigo {
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(99,102,241,0.22), transparent 70%);
            bottom: -60px; right: -40px;
        }
        .gw-orb-board {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(99,102,241,0.3), transparent 70%);
            bottom: -80px; left: 50%; transform: translateX(-50%);
            animation-direction: alternate-reverse;
        }
        .gw-orb-cyan {
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(6,182,212,0.22), transparent 70%);
            bottom: -60px; left: -40px;
        }
        @keyframes gwOrbPulse {
            0%   { opacity: 0.5; transform: scale(1); }
            100% { opacity: 1;   transform: scale(1.2); }
        }
        .gw-lane-board .gw-orb-board { transform: translateX(-50%) scale(1); }

        /* top accent bar */
        .gw-top-bar {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            z-index: 4;
        }
        .gw-top-indigo { background: linear-gradient(90deg, transparent, rgba(99,102,241,0.8), transparent); }
        .gw-top-board  { background: linear-gradient(90deg, transparent, rgba(99,102,241,1), transparent); }
        .gw-top-cyan   { background: linear-gradient(90deg, transparent, rgba(6,182,212,0.8), transparent); }

        /* scanlines texture */
        .gw-scanlines {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 3px,
                rgba(255,255,255,0.01) 3px,
                rgba(255,255,255,0.01) 4px
            );
            pointer-events: none;
            z-index: 0;
        }

        /* corner accent brackets */
        .gw-corner {
            position: absolute;
            width: 18px; height: 18px;
            pointer-events: none;
            z-index: 3;
        }
        .gw-corner-tl { top: 12px; left: 12px; border-top: 1.5px solid; border-left: 1.5px solid; border-radius: 3px 0 0 0; }
        .gw-corner-tr { top: 12px; right: 12px; border-top: 1.5px solid; border-right: 1.5px solid; border-radius: 0 3px 0 0; }
        .gw-corner-bl { bottom: 12px; left: 12px; border-bottom: 1.5px solid; border-left: 1.5px solid; border-radius: 0 0 0 3px; }
        .gw-corner-br { bottom: 12px; right: 12px; border-bottom: 1.5px solid; border-right: 1.5px solid; border-radius: 0 0 3px 0; }
        .gw-accent-indigo { border-color: rgba(99,102,241,0.3); }
        .gw-accent-board  { border-color: rgba(99,102,241,0.55); }
        .gw-accent-cyan   { border-color: rgba(6,182,212,0.3); }

        /* role pill */
        .gw-role-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 13px;
            border-radius: 100px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            width: fit-content;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        .pill-indigo { background: rgba(99,102,241,0.13); border: 1px solid rgba(99,102,241,0.28); color: #a5b4fc; }
        .pill-board  { background: rgba(99,102,241,0.22); border: 1px solid rgba(99,102,241,0.5);  color: #c7d2fe; }
        .pill-cyan   { background: rgba(6,182,212,0.13);  border: 1px solid rgba(6,182,212,0.28);  color: #67e8f9; }

        /* large gateway icon */
        .gw-icon {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1);
        }
        .gw-lane:hover .gw-icon { transform: scale(1.14) rotate(-5deg); }
        .gw-icon-indigo { background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3); color: #818cf8; }
        .gw-icon-board  { background: rgba(99,102,241,0.25); border: 1px solid rgba(99,102,241,0.5); color: #c7d2fe; }
        .gw-icon-cyan   { background: rgba(6,182,212,0.15);  border: 1px solid rgba(6,182,212,0.3);  color: #22d3ee; }

        /* gateway title & desc */
        .gw-title {
            font-size: 18px;
            font-weight: 900;
            color: #f1f5f9;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }
        .gw-lane-board .gw-title { font-size: 20px; color: #fff; }

        .gw-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.68;
            margin-bottom: auto;
            padding-bottom: 28px;
            position: relative;
            z-index: 1;
        }
        .gw-lane-board .gw-desc { color: #7c8ca8; }

        /* feature chip list */
        .gw-features {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-bottom: 26px;
            position: relative;
            z-index: 1;
        }
        .gw-feature-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: #64748b;
            font-weight: 600;
        }
        .gw-feature-dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .fdot-indigo { background: #818cf8; }
        .fdot-board  { background: #c7d2fe; }
        .fdot-cyan   { background: #22d3ee; }

        /* CTA button */
        .gw-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 22px;
            border-radius: 13px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.25s;
            position: relative;
            z-index: 1;
            letter-spacing: 0.01em;
        }
        .gw-btn svg { transition: transform 0.2s; }
        .gw-btn:hover svg { transform: translateX(4px); }

        .gw-btn-indigo {
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.3);
            color: #a5b4fc;
        }
        .gw-btn-indigo:hover {
            background: rgba(99,102,241,0.25);
            border-color: rgba(99,102,241,0.6);
            color: #c7d2fe;
            box-shadow: 0 8px 24px rgba(99,102,241,0.2);
            transform: translateY(-2px);
        }
        .gw-btn-board {
            background: linear-gradient(135deg, rgba(99,102,241,0.85), rgba(79,82,214,0.85));
            border: 1px solid rgba(99,102,241,0.5);
            color: #fff;
            box-shadow: 0 8px 28px rgba(99,102,241,0.25);
        }
        .gw-btn-board:hover {
            background: linear-gradient(135deg, #6366f1, #4f52d6);
            box-shadow: 0 12px 36px rgba(99,102,241,0.45);
            transform: translateY(-2px);
        }
        .gw-btn-cyan {
            background: rgba(6,182,212,0.12);
            border: 1px solid rgba(6,182,212,0.3);
            color: #22d3ee;
        }
        .gw-btn-cyan:hover {
            background: rgba(6,182,212,0.25);
            border-color: rgba(6,182,212,0.6);
            color: #67e8f9;
            box-shadow: 0 8px 24px rgba(6,182,212,0.2);
            transform: translateY(-2px);
        }

        /* STARRED badge on board lane */
        .gw-star-badge {
            position: absolute;
            top: 18px;
            right: 18px;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 11px;
            border-radius: 100px;
            background: rgba(99,102,241,0.25);
            border: 1px solid rgba(99,102,241,0.5);
            color: #c7d2fe;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            z-index: 5;
        }

        @media (max-width: 860px) {
            .gateway-grid {
                grid-template-columns: 1fr;
            }
            .gw-lane-school { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.05); }
            .gw-lane-board  { border-right: none; border-left: none; border-top: 1px solid rgba(99,102,241,0.15); border-bottom: 1px solid rgba(99,102,241,0.15); }
            .gw-lane-invig  { border-left: none; border-top: 1px solid rgba(255,255,255,0.05); }
            .gw-lane { min-height: auto; padding: 36px 28px; }
        }

        /* ── STATS BAR ── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border-radius: 22px;
            overflow: hidden;
            background: var(--surface);
            backdrop-filter: blur(18px);
            border: 1px solid var(--border);
        }

        .stat-item {
            padding: 34px 20px;
            text-align: center;
            border-right: 1px solid var(--border);
            position: relative;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-num {
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.04em;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-lbl {
            font-size: 10.5px;
            color: var(--muted);
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        /* ── FOOTER: styles are in x-public-footer component ── */

        /* ── [x-cloak] ── */
        [x-cloak] {
            display: none !important;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .about-grid {
                grid-template-columns: 1fr;
            }

            .winners-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .gateway-grid {
                grid-template-columns: 1fr;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: 220px 220px 220px;
                gap: 12px;
            }

            .gi-feat  { grid-column: 1 / 3; grid-row: 1; }
            .gi-tr    { grid-column: 1;     grid-row: 2; }
            .gi-tl    { grid-column: 2;     grid-row: 2; }
            .gi-bl    { grid-column: 1;     grid-row: 3; }
            .gi-bm    { grid-column: 2;     grid-row: 3; }
        }

        @media (max-width: 680px) {
            .hero {
                padding: 70px 16px 50px;
            }

            /* Nav responsive styles moved to x-public-nav component */

            .utilities-grid {
                grid-template-columns: 1fr;
            }

            .winners-grid {
                grid-template-columns: 1fr;
            }

            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }

            .stat-item:nth-child(2) {
                border-right: none;
            }

            .stat-item:nth-child(3),
            .stat-item:nth-child(4) {
                border-top: 1px solid var(--border);
            }

            .gallery-grid {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }

            .gi-feat, .gi-tr, .gi-tl, .gi-bl, .gi-bm {
                grid-column: 1;
                grid-row: auto;
                min-height: 200px;
            }

            .hero-stats {
                flex-direction: column;
            }

            .hero-stat {
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .hero-stat:last-child {
                border-bottom: none;
            }

            .section {
                padding: 0 16px;
            }
        }

        /* @media nav 440px override moved to x-public-nav component */
    </style>
</head>

<body>
    <!-- Background -->
    <div class="bg-wrap">
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

    <div class="page" x-data="{ menuOpen: false }">

        <!-- NAV COMPONENT -->
        <x-public-nav page="home" />

        <!-- HERO -->
        <div class="hero">
            <div class="hero-logos">
                @if(file_exists(public_path('logo-w.png')))
                    <img src="{{ asset('logo-w.png') }}" alt="YES" class="hero-logo-item">
                @endif
                @if(file_exists(public_path('logo-w.png')) && file_exists(public_path('logo1.png')))
                    <div class="hero-logos-divider"></div>
                @endif
                @if(file_exists(public_path('logo1.png')))
                    <img src="{{ asset('logo1.png') }}" alt="YES India" class="hero-logo-item">
                @endif
            </div>

            @if($activeExam)
                <div class="hero-badge">
                    <span class="hero-badge-dot"
                        style="background-color: {{ $activeExam->status === 'Registration Started' ? '#10b981' : ($activeExam->status === 'Examination Ongoing' ? '#3b82f6' : ($activeExam->status === 'result published' ? '#a855f7' : '#94a3b8')) }}; {{ in_array($activeExam->status, ['Registration Started', 'Examination Ongoing']) ? 'animation: pulse 1.8s ease-in-out infinite;' : '' }}"></span>
                    {{ $activeExam->name }} &nbsp;|&nbsp; {{ strtoupper($activeExam->status) }}
                </div>
            @else
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    Nationwide Academic Competition &nbsp;·&nbsp; India
                </div>
            @endif

            <h1 class="hero-title">
                Discover · Celebrate<br>
                <span class="hero-gradient">Nurture Academic</span><br>
                Brilliance
            </h1>

            <p class="hero-desc">
                A nationwide talent-identification program recognising exceptional students from
                <strong style="color:#e2e8f0;">YES India Schools & Yaseen Colleges of Integrated Studies</strong> across
                <strong style="color:#e2e8f0;">40+ centres</strong>. Winners are celebrated at
                the prestigious <strong style="color:#fbbf24;">Genius Jam</strong>.
            </p>

            <div class="hero-actions">
                <a href="#about" class="btn-primary">Explore Programme</a>
                <a href="#utilities" class="btn-ghost">Verify Hall Ticket</a>
            </div>
        </div>

        <main>
            <!-- ── ABOUT ─────────────────────────────── -->
            <div class="section section-sep" id="about">
                <div class="section-tag">
                    <div class="section-tag-line"></div>
                    <span>About The Programme</span>
                    <div class="section-tag-line"></div>
                </div>
                <h2 class="section-title">What is YES Genius?</h2>
                <p class="section-sub">A national-level assessment and recognition platform for academic excellence.</p>

                <div class="about-grid">
                    <div class="about-text">
                        <p>
                            <strong>YES Genius Examination</strong> is a <strong>nationwide academic competition
                                and talent-identification program</strong> designed to discover, celebrate, and nurture academic and cognitive abilities among students across India.
                        </p>
                        <p>
                            It functions as a <strong>national-level assessment and recognition platform</strong>
                            where students from participating <strong>YES India Schools & Yaseen Colleges of Integrated Studies</strong> compete to showcase their intellectual prowess across multiple subject categories.
                        </p>
                        <p>
                            The examination is held at <strong>40+ centres nationwide</strong>, ensuring
                            accessibility for students from every corner of the country. Every centre is
                            equipped with trained invigilators and standardised assessment protocols.
                        </p>
                        <p>
                            Outstanding performers are awarded and celebrated at the prestigious
                            <strong style="color:#fbbf24;">Genius Jam</strong> — a grand event that honours
                            the brightest minds and inspires the next generation of Indian scholars.
                        </p>
                    </div>

                    <div class="about-card">
                        <div class="about-card-orb"></div>
                        <div class="about-corner about-corner-tl"></div>
                        <div class="about-corner about-corner-tr"></div>
                        <div class="about-corner about-corner-bl"></div>
                        <div class="about-corner about-corner-br"></div>

                        <div class="about-feature">
                            <div class="about-feature-icon" style="background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); color:#fbbf24;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:22px;height:22px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="about-feature-title">Talent Identification</div>
                                <div class="about-feature-desc">Discover and spotlight exceptional academic and cognitive abilities in students across India.</div>
                            </div>
                        </div>

                        <div class="about-feature">
                            <div class="about-feature-icon" style="background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.3); color:#818cf8;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:22px;height:22px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="about-feature-title">40+ Exam Centres</div>
                                <div class="about-feature-desc">Standardised examination centres across India, all with trained invigilators and real-time attendance scanning.</div>
                            </div>
                        </div>

                        <div class="about-feature">
                            <div class="about-feature-icon" style="background:rgba(168,85,247,0.12); border:1px solid rgba(168,85,247,0.3); color:#c084fc;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:22px;height:22px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                </svg>
                            </div>
                            <div>
                                <div class="about-feature-title">Genius Jam Awards</div>
                                <div class="about-feature-desc">Winners are celebrated at Genius Jam — a prestigious national award ceremony honouring India's brightest young minds.</div>
                            </div>
                        </div>

                        <div class="about-feature">
                            <div class="about-feature-icon" style="background:rgba(6,182,212,0.12); border:1px solid rgba(6,182,212,0.3); color:#22d3ee;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:22px;height:22px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="about-feature-title">YES India Schools & Yaseen Colleges of Integrated Studies</div>
                                <div class="about-feature-desc">Open exclusively to students from participating YES India Schools & Yaseen Colleges of Integrated Studies.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── PREVIOUS YEAR WINNERS ─────────────── -->
            <div class="section section-sep" id="winners">
                <div class="section-tag">
                    <div class="section-tag-line"></div>
                    <span>Hall of Fame</span>
                    <div class="section-tag-line"></div>
                </div>
                <h2 class="section-title">Previous Year Winners</h2>
                <p class="section-sub">Celebrating the brightest minds from YES India Schools & Yaseen Colleges of Integrated Studies, recognized at Genius
                    Jam.</p>

                @if($winners->isNotEmpty())
                    @php
                        $catColors = [
                            0 => ['tag' => 'bg:rgba(99,102,241,0.15);color:#a5b4fc;border:rgba(99,102,241,0.3)', 'score' => 'background:rgba(99,102,241,0.12);color:#a5b4fc;'],
                            1 => ['tag' => 'bg:rgba(168,85,247,0.15);color:#d8b4fe;border:rgba(168,85,247,0.3)', 'score' => 'background:rgba(168,85,247,0.12);color:#d8b4fe;'],
                            2 => ['tag' => 'bg:rgba(6,182,212,0.15);color:#67e8f9;border:rgba(6,182,212,0.3)', 'score' => 'background:rgba(6,182,212,0.12);color:#67e8f9;'],
                            3 => ['tag' => 'bg:rgba(16,185,129,0.15);color:#6ee7b7;border:rgba(16,185,129,0.3)', 'score' => 'background:rgba(16,185,129,0.12);color:#6ee7b7;'],
                            4 => ['tag' => 'bg:rgba(245,158,11,0.15);color:#fcd34d;border:rgba(245,158,11,0.3)', 'score' => 'background:rgba(245,158,11,0.12);color:#fcd34d;'],
                        ];
                        $ci = 0;
                    @endphp

                    @foreach($winners as $categoryName => $topStudents)
                        @php $palette = $catColors[$ci % count($catColors)];
                        $ci++; @endphp
                        <div style="margin-bottom: 48px;">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                                <div style="height:1px;flex:1;max-width:40px;background:rgba(255,255,255,0.08);"></div>
                                <span
                                    style="font-size:11px;font-weight:700;color:#64748b;letter-spacing:0.12em;text-transform:uppercase;">Category</span>
                                <h3 style="font-size:18px;font-weight:800;color:#f1f5f9;letter-spacing:-0.01em;">
                                    {{ $categoryName }}</h3>
                                <div style="height:1px;flex:1;background:rgba(255,255,255,0.08);"></div>
                            </div>
                            <div class="winners-grid">
                                @foreach($topStudents as $idx => $winner)
                                    @php
                                        $rank = $idx + 1;
                                    @endphp
                                    <div class="winner-card">
                                        <div x-data="{ 
                                            activeSlide: {{ ($idx + $rank) % 5 }},
                                            init() {
                                                setInterval(() => {
                                                    this.activeSlide = (this.activeSlide + 1) % 5;
                                                }, 3000 + Math.random() * 1000);
                                            }
                                        }" class="winner-card-slider">
                                            <div class="winner-card-slider-track" :style="'transform: translateX(-' + (activeSlide * 20) + '%)'">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <img src="{{ asset('gallery/banner-0' . $i . '.jpeg') }}" 
                                                         x-on:error="$event.target.src = '{{ asset('gallery/banner-0' . $i . '.jpg') }}'"
                                                         alt="Winners" 
                                                         class="winner-card-img" 
                                                    >
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="winners-fallback-container">
                        <div x-data="{ 
                            activeSlide: 0,
                            init() {
                                setInterval(() => {
                                    this.activeSlide = (this.activeSlide + 1) % 5;
                                }, 3000);
                            }
                        }" class="winners-fallback-slider">
                            <div class="winners-fallback-slider-track" :style="'transform: translateX(-' + (activeSlide * 20) + '%)'">
                                @for($i = 1; $i <= 5; $i++)
                                    <img src="{{ asset('gallery/banner-0' . $i . '.jpeg') }}" 
                                         x-on:error="$event.target.src = '{{ asset('gallery/banner-0' . $i . '.jpg') }}'"
                                         alt="Previous Winners" 
                                         class="winners-fallback-img" 
                                    >
                                @endfor
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- ── GALLERY PREVIEW ─────────────────── -->
            <div class="section section-sep" id="gallery-preview">
                <div class="section-tag">
                    <div class="section-tag-line"></div>
                    <span>Gallery</span>
                    <div class="section-tag-line"></div>
                </div>
                <h2 class="section-title">Moments of Excellence</h2>
                <p class="section-sub">A glimpse into the YES Genius journey — from exam halls to Genius Jam
                    celebrations.</p>

                <div class="gallery-grid">
                    @php
                        $galleryItems = [
                            ['file' => 'img1.jpeg', 'cls' => 'gi-feat'],
                            ['file' => 'img2.jpeg', 'cls' => 'gi-tr'],
                            ['file' => 'img3.jpeg', 'cls' => 'gi-tl'],
                            ['file' => 'img4.jpeg', 'cls' => 'gi-bl'],
                            ['file' => 'img5.jpeg', 'cls' => 'gi-bm'],
                        ];
                    @endphp
                    @foreach($galleryItems as $item)
                        <div class="gallery-item {{ $item['cls'] }}">
                            <img src="{{ asset('gallery/' . $item['file']) }}"
                                class="gallery-img" loading="lazy">
                        </div>
                    @endforeach
                </div>

                <div class="gallery-cta-wrap">
                    <a href="{{ route('gallery') }}" class="btn-outline-indigo">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor" style="width:16px;height:16px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        View Full Gallery
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" style="width:13px;height:13px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- ── PUBLIC SERVICES ───────────────────── -->
            <div class="section section-sep" id="utilities">
                <div class="section-tag">
                    <div class="section-tag-line"></div>
                    <span>Quick Access</span>
                    <div class="section-tag-line"></div>
                </div>
                <h2 class="section-title">Public Services</h2>
                <p class="section-sub">No login required — look up your hall ticket or check your results instantly.</p>

                <div class="utilities-grid">

                    <!-- ══ HALL TICKET PANEL ══ -->
                    <div class="util-panel util-panel-cyan" x-data="{ ticket: '' }">
                        <div class="util-top-bar util-top-bar-cyan"></div>
                        <div class="util-scanlines"></div>
                        <div class="util-orb util-orb-cyan"></div>

                        <!-- corner brackets -->
                        <div class="util-corner util-corner-tl corner-cyan"></div>
                        <div class="util-corner util-corner-tr corner-cyan"></div>
                        <div class="util-corner util-corner-bl corner-cyan"></div>
                        <div class="util-corner util-corner-br corner-cyan"></div>

                        <span class="util-badge util-badge-cyan">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:10px;height:10px;"><circle cx="12" cy="12" r="5"/></svg>
                            Live Verification
                        </span>
                        <div class="util-title">Hall Ticket<br>Verification</div>
                        <p class="util-desc">Enter a candidate's hall ticket number to instantly verify seating details, examination centre, and reporting time.</p>

                        <div class="util-stat-row">
                            <div class="util-stat-chip"><span class="util-stat-chip-dot dot-cyan"></span>Instant Lookup</div>
                            <div class="util-stat-chip"><span class="util-stat-chip-dot dot-green"></span>No Login Needed</div>
                        </div>

                        <form class="verify-input-wrap"
                            @submit.prevent="ticket.trim() ? (window.location.href='/verify/hall-ticket/'+encodeURIComponent(ticket.trim())) : null">
                            <div class="verify-input-row">
                                <input type="text" class="verify-input" x-model="ticket"
                                    placeholder="Enter Hall Ticket No. e.g. F89C23D4829E"
                                    autocomplete="off" id="hall-ticket-input">
                                <button type="submit" class="btn-cyan">Verify →</button>
                            </div>
                        </form>
                    </div>

                    <!-- ══ RESULTS PORTAL PANEL ══ -->
                    <div class="util-panel util-panel-purple">
                        <div class="util-top-bar util-top-bar-purple"></div>
                        <div class="util-scanlines"></div>
                        <div class="util-orb util-orb-purple"></div>

                        <!-- corner brackets -->
                        <div class="util-corner util-corner-tl corner-purple"></div>
                        <div class="util-corner util-corner-tr corner-purple"></div>
                        <div class="util-corner util-corner-bl corner-purple"></div>
                        <div class="util-corner util-corner-br corner-purple"></div>

                        <!-- decorative QR motif -->
                        <div class="util-qr-motif" aria-hidden="true">
                            @php
                                $qr = [1, 1, 1, 0, 1, 1, 1, 1, 0, 1, 0, 1, 0, 1, 1, 0, 1, 1, 0, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1, 1, 1];
                            @endphp
                            @foreach($qr as $cell)
                                <span style="{{ $cell ? '' : 'opacity:0;' }}"></span>
                            @endforeach
                        </div>

                        <span class="util-badge util-badge-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:10px;height:10px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Board Certified
                        </span>

                        <div class="util-title">Official Results<br>Portal</div>
                        <p class="util-desc">Students and parents can access board-certified, digitally authenticated marksheets once results are published. Fully printable and QR-verified.</p>

                        <div class="util-stat-row">
                            <div class="util-stat-chip"><span class="util-stat-chip-dot dot-purple"></span>QR Authenticated</div>
                            <div class="util-stat-chip"><span class="util-stat-chip-dot dot-green"></span>Printable PDF</div>
                        </div>

                        <a href="{{ route('results.check-form') }}" class="btn-purple-solid" id="check-results-btn">
                            Check Exam Results
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:15px;height:15px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                </div>
            </div>

            <!-- ── PORTAL GATEWAYS ───────────────── -->
            <div class="section section-sep" id="portals">
                <div class="section-tag">
                    <div class="section-tag-line"></div>
                    <span>Login Portals</span>
                    <div class="section-tag-line"></div>
                </div>
                <h2 class="section-title">Access Gateways</h2>
                <p class="section-sub">Choose your role to access the right portal for your work.</p>

                <div class="gateway-grid">

                    <!-- ══ SCHOOL ADMIN LANE ══ -->
                    <div class="gw-lane gw-lane-school">
                        <div class="gw-top-bar gw-top-indigo"></div>
                        <div class="gw-scanlines"></div>
                        <div class="gw-orb gw-orb-indigo"></div>

                        <div class="gw-corner gw-corner-tl gw-accent-indigo"></div>
                        <div class="gw-corner gw-corner-tr gw-accent-indigo"></div>
                        <div class="gw-corner gw-corner-bl gw-accent-indigo"></div>
                        <div class="gw-corner gw-corner-br gw-accent-indigo"></div>

                        <span class="gw-role-pill pill-indigo">School Admin</span>
                        <div class="gw-title">School Partner Portal</div>
                        <p class="gw-desc">Manage student registrations, import candidate data via Excel, monitor application statuses, and bulk-download hall tickets for your institution.</p>

                        <div class="gw-features">
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-indigo"></span>Student Registration</div>
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-indigo"></span>Bulk Hall Ticket Download</div>
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-indigo"></span>Application Status Tracking</div>
                        </div>

                        <a href="{{ route('login') }}" class="gw-btn gw-btn-indigo" id="school-login-btn">
                            Enter School Desk
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:13px;height:13px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                    <!-- ══ BOARD CONTROL (FEATURED) LANE ══ -->
                    <div class="gw-lane gw-lane-board">
                        <div class="gw-top-bar gw-top-board"></div>
                        <div class="gw-scanlines"></div>
                        <div class="gw-orb gw-orb-board"></div>

                        <div class="gw-corner gw-corner-tl gw-accent-board"></div>
                        <div class="gw-corner gw-corner-tr gw-accent-board"></div>
                        <div class="gw-corner gw-corner-bl gw-accent-board"></div>
                        <div class="gw-corner gw-corner-br gw-accent-board"></div>
                        <span class="gw-role-pill pill-board">Board · Super Admin</span>
                        <div class="gw-title">Board Control Panel</div>
                        <p class="gw-desc">Full system oversight — audit schools, manage exam schedules, authorise registrations, configure invigilators, track real-time activity logs, and process final results.</p>

                        <div class="gw-features">
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-board"></span>Full System Oversight</div>
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-board"></span>Real-time Activity Logs</div>
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-board"></span>Results Publishing</div>
                        </div>

                        <a href="{{ route('login') }}" class="gw-btn gw-btn-board" id="board-login-btn">
                            Board Sign In
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:13px;height:13px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                    <!-- ══ INVIGILATOR LANE ══ -->
                    <div class="gw-lane gw-lane-invig">
                        <div class="gw-top-bar gw-top-cyan"></div>
                        <div class="gw-scanlines"></div>
                        <div class="gw-orb gw-orb-cyan"></div>

                        <div class="gw-corner gw-corner-tl gw-accent-cyan"></div>
                        <div class="gw-corner gw-corner-tr gw-accent-cyan"></div>
                        <div class="gw-corner gw-corner-bl gw-accent-cyan"></div>
                        <div class="gw-corner gw-corner-br gw-accent-cyan"></div>

                        <span class="gw-role-pill pill-cyan">Invigilator</span>
                        <div class="gw-title">Invigilator Desk</div>
                        <p class="gw-desc">Access live attendance scanning tools, verify candidate hall tickets via barcode, mark on-site presence, and log examination session records in real time.</p>

                        <div class="gw-features">
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-cyan"></span>Barcode Hall Ticket Scan</div>
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-cyan"></span>Live Attendance Marking</div>
                            <div class="gw-feature-row"><span class="gw-feature-dot fdot-cyan"></span>Session Record Logging</div>
                        </div>

                        <a href="{{ route('login') }}" class="gw-btn gw-btn-cyan" id="invig-login-btn">
                            Invigilator Access
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:13px;height:13px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                </div>
            </div>

            <!-- ── STATS BAR ───────────────────────── -->
            <div class="section section-sep">
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="stat-num">40<span style="font-size:60%;color:#818cf8;">+</span></div>
                        <div class="stat-lbl">Exam Centres</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">17,000<span style="font-size:60%;color:#818cf8;">+</span></div>
                        <div class="stat-lbl">Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num" style="color:#c084fc;">National</div>
                        <div class="stat-lbl">Level Programme</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num"
                            style="color:#fbbf24;">Genius Jam
                        </div>
                        <div class="stat-lbl">Award Ceremony</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- FOOTER COMPONENT -->
        <x-public-footer page="home" />

    </div>
</body>

</html>
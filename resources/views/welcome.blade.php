<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="YES Genius Examination — A nationwide academic competition to discover, celebrate and nurture academic and cognitive abilities among students across India. Held at 40+ centres nationwide.">
    <title>YES Genius Examination — National Academic Competition</title>
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

        /* ── NAV ── */
        .header-wrap {
            position: sticky;
            top: 14px;
            z-index: 200;
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
            pointer-events: none;
        }

        .nav {
            pointer-events: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            height: 62px;
            background: rgba(10, 14, 26, 0.80);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 18px;
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.02);
            transition: border-color 0.25s, box-shadow 0.25s;
        }

        .nav:hover {
            border-color: rgba(99, 102, 241, 0.28);
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.5), 0 0 20px rgba(99, 102, 241, 0.1);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
        }

        .nav-logo {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            object-fit: contain;
            flex-shrink: 0;
            filter: drop-shadow(0 2px 8px rgba(99, 102, 241, 0.35));
        }

        .nav-logo-fallback {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.18);
            border: 1px solid rgba(99, 102, 241, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #818cf8;
            font-weight: 900;
            font-size: 16px;
            flex-shrink: 0;
        }

        .nav-name {
            font-size: 16px;
            font-weight: 900;
            background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.3px;
            white-space: nowrap;
        }

        .nav-sub {
            font-size: 8px;
            color: var(--muted);
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .nav-link {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            position: relative;
            padding: 8px 0;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: #fff;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 99px;
        }

        .nav-link:hover::after {
            transform: scaleX(1);
        }

        .nav-cta {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 11px;
            background: linear-gradient(135deg, #6366f1 0%, #4f52d6 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.28);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .nav-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(99, 102, 241, 0.48);
        }

        .nav-hamburger {
            display: none;
        }

        .mobile-drawer {
            display: none;
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
            background: linear-gradient(130deg, #818cf8 0%, #c084fc 45%, #fbbf24 100%);
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
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 32px;
            position: relative;
            overflow: hidden;
        }

        .about-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(241, 196, 15, 0.7), transparent);
        }

        .about-feature {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
        }

        .about-feature:last-child {
            margin-bottom: 0;
        }

        .about-feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .about-feature-title {
            font-size: 14px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 3px;
        }

        .about-feature-desc {
            font-size: 12.5px;
            color: #64748b;
            line-height: 1.5;
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

        .winner-card-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
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

        /* ── GALLERY PREVIEW — Lunchbox/Bento Layout ── */
        .gallery-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            grid-template-rows: 260px 200px;
            gap: 12px;
        }

        .gallery-item {
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            border: 1px solid var(--border);
            transition: transform 0.28s cubic-bezier(0.16,1,0.3,1), box-shadow 0.28s, border-color 0.28s;
        }

        .gallery-item:hover {
            transform: scale(1.02);
            box-shadow: 0 20px 48px rgba(0,0,0,0.55);
            border-color: rgba(99,102,241,0.3);
            z-index: 2;
        }

        /* Bento cell positions */
        .gi-feat  { grid-column: 1; grid-row: 1 / 3; }   /* large left — full height */
        .gi-tr    { grid-column: 2; grid-row: 1; }         /* top middle */
        .gi-tl    { grid-column: 3; grid-row: 1; }         /* top right */
        .gi-bl    { grid-column: 2; grid-row: 2; }         /* bottom middle */
        .gi-bm    { grid-column: 3; grid-row: 2; }         /* bottom right */

        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
            filter: brightness(0.92);
        }

        .gallery-item:hover .gallery-img {
            transform: scale(1.06);
            filter: brightness(1);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(6, 8, 16, 0.75), transparent 55%);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            align-items: flex-end;
            padding: 16px;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-caption {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
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
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

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

        .verify-input-wrap {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .verify-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            font-size: 13.5px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            outline: none;
            transition: border-color 0.2s;
        }

        .verify-input:focus {
            border-color: rgba(6, 182, 212, 0.5);
        }

        .verify-input::placeholder {
            color: #334155;
            text-transform: none;
            letter-spacing: 0;
            font-weight: 400;
        }

        .btn-cyan {
            padding: 12px 20px;
            border-radius: 12px;
            background: rgba(6, 182, 212, 0.15);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #22d3ee;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            font-family: 'Outfit', sans-serif;
        }

        .btn-cyan:hover {
            background: rgba(6, 182, 212, 0.25);
            border-color: rgba(6, 182, 212, 0.5);
            transform: translateY(-1px);
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

        /* ── GATEWAYS ── */
        .gateway-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .gateway-card {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .gateway-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            width: fit-content;
        }

        .badge-indigo {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.25);
        }

        .badge-cyan {
            background: rgba(6, 182, 212, 0.15);
            color: #67e8f9;
            border: 1px solid rgba(6, 182, 212, 0.25);
        }

        .gateway-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid;
            transition: all 0.2s;
            margin-top: auto;
        }

        .gateway-link-indigo {
            color: #818cf8;
            border-color: rgba(99, 102, 241, 0.3);
        }

        .gateway-link-indigo:hover {
            background: var(--indigo);
            color: #fff;
            border-color: var(--indigo);
        }

        .gateway-link-cyan {
            color: #22d3ee;
            border-color: rgba(6, 182, 212, 0.3);
        }

        .gateway-link-cyan:hover {
            background: #0891b2;
            color: #fff;
            border-color: #0891b2;
        }

        .gateway-link svg {
            transition: transform 0.2s;
        }

        .gateway-link:hover svg {
            transform: translateX(4px);
        }

        .featured-card {
            background: linear-gradient(145deg, rgba(79, 82, 214, 0.15), rgba(13, 18, 30, 0.8));
            border-color: rgba(99, 102, 241, 0.3) !important;
        }

        .featured-card::before {
            background: radial-gradient(ellipse at top, rgba(99, 102, 241, 0.12), transparent 70%) !important;
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

        /* ── FOOTER ── */
        .footer {
            margin-top: auto;
            padding: 32px 24px;
            border-top: 1px solid var(--border);
            background: rgba(6, 8, 16, 0.6);
            backdrop-filter: blur(10px);
        }

        .footer-inner {
            max-width: 1240px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .footer-copy {
            font-size: 12.5px;
            color: var(--muted);
        }

        .footer-links {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .footer-links a {
            font-size: 12.5px;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #94a3b8;
        }

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
                grid-template-columns: 1fr 1fr;
                grid-template-rows: 220px 180px;
            }

            .gi-feat { grid-column: 1 / 3; grid-row: 1; }
            .gi-tr   { grid-column: 1;     grid-row: 2; }
            .gi-tl   { grid-column: 2;     grid-row: 2; }
            .gi-bl   { grid-column: 1;     grid-row: 3; }
            .gi-bm   { grid-column: 2;     grid-row: 3; }

            .gallery-grid { grid-template-rows: 220px 180px 180px; }
        }

        @media (max-width: 680px) {
            .hero {
                padding: 70px 16px 50px;
            }

            .nav-links {
                display: none;
            }

            .nav .nav-cta {
                display: none;
            }

            .nav-hamburger {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 38px;
                height: 38px;
                border-radius: 10px;
                border: 1px solid rgba(255, 255, 255, 0.09);
                background: rgba(255, 255, 255, 0.04);
                color: #94a3b8;
                cursor: pointer;
                transition: all 0.18s;
                flex-shrink: 0;
            }

            .nav-hamburger.is-open {
                background: rgba(99, 102, 241, 0.15);
                border-color: rgba(99, 102, 241, 0.35);
                color: #a5b4fc;
            }

            .mobile-drawer {
                display: block;
                position: fixed;
                top: 84px;
                left: 12px;
                right: 12px;
                background: rgba(10, 14, 26, 0.96);
                backdrop-filter: blur(24px);
                border: 1px solid rgba(255, 255, 255, 0.09);
                border-radius: 18px;
                padding: 16px;
                z-index: 190;
                box-shadow: 0 24px 48px rgba(0, 0, 0, 0.6);
                animation: drawerIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) both;
                pointer-events: auto;
            }

            @keyframes drawerIn {
                from {
                    opacity: 0;
                    transform: translateY(-8px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .mobile-drawer a {
                display: block;
                padding: 12px 16px;
                border-radius: 12px;
                color: #94a3b8;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.18s;
            }

            .mobile-drawer a:hover {
                background: rgba(255, 255, 255, 0.05);
                color: #fff;
            }

            .mobile-drawer .nav-cta {
                display: block;
                text-align: center;
                margin-top: 8px;
            }

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

        @media (max-width: 440px) {
            .nav {
                height: 56px;
                padding: 0 14px;
            }

            .nav-cta {
                padding: 7px 14px;
                font-size: 12px;
            }
        }
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

        <!-- NAV -->
        <div class="header-wrap" style="padding-top:14px;">
            <nav class="nav">
                <a href="/" class="nav-brand">
                    @if(file_exists(public_path('icon.png')))
                        <img src="{{ asset('icon.png') }}" alt="YES" class="nav-logo">
                    @else
                        <div class="nav-logo-fallback">Y</div>
                    @endif
                    <div>
                        <div class="nav-name">YES Genius</div>
                        <div class="nav-sub">National Level Talent Search</div>
                    </div>
                </a>

                <div class="nav-links">
                    <a href="#about" class="nav-link">About<span></span></a>
                    <a href="#winners" class="nav-link">Winners<span></span></a>
                    <a href="{{ route('gallery') }}" class="nav-link">Gallery<span></span></a>
                    <a href="#portals" class="nav-link">Portals<span></span></a>
                    <a href="{{ route('results.check-form') }}" class="nav-link">Result<span></span></a>
                </div>

                <div style="display:flex;align-items:center;gap:10px;">
                    <a href="{{ route('login') }}" class="nav-cta">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" style="width:14px;height:14px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        Sign In
                    </a>
                    <button class="nav-hamburger" :class="{ 'is-open': menuOpen }" @click="menuOpen = !menuOpen"
                        aria-label="Menu">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" style="width:18px;height:18px;" x-show="!menuOpen">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" style="width:18px;height:18px;" x-show="menuOpen">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </nav>

            <!-- Mobile Drawer -->
            <div class="mobile-drawer" x-show="menuOpen" @click.away="menuOpen = false" x-cloak>
                <a href="#about" @click="menuOpen=false">About</a>
                <a href="#winners" @click="menuOpen=false">Winners</a>
                <a href="{{ route('gallery') }}" @click="menuOpen=false">Gallery</a>
                <a href="#utilities" @click="menuOpen=false">Hall Ticket Verify</a>
                <a href="{{ route('results.check-form') }}" @click="menuOpen=false">Results Portal</a>
                <a href="#portals" @click="menuOpen=false">Portals</a>
                <a href="{{ route('login') }}" class="nav-cta" style="margin-top:8px;">Sign In →</a>
            </div>
        </div>

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
                <strong style="color:#e2e8f0;">YES India Schools</strong> across
                <strong style="color:#e2e8f0;">40+ centres</strong>. Winners are celebrated at
                the prestigious <strong style="color:#fbbf24;">Genius Jams</strong>.
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
                                and talent-identification program</strong> designed to discover, celebrate, and
                            nurture academic and cognitive abilities among students across India.
                        </p>
                        <p>
                            It functions as a <strong>national-level assessment and recognition platform</strong>
                            where students from participating <strong>YES India Schools</strong> compete to
                            showcase their intellectual prowess across multiple subject categories.
                        </p>
                        <p>
                            The examination is held at <strong>40+ centres nationwide</strong>, ensuring
                            accessibility for students from every corner of the country. Every centre is
                            equipped with trained invigilators and standardised assessment protocols.
                        </p>
                        <p>
                            Outstanding performers are awarded and celebrated at the prestigious
                            <strong style="color:#fbbf24;">Genius Jams</strong> — a grand event that honours
                            the brightest minds and inspires the next generation of Indian scholars.
                        </p>
                    </div>

                    <div class="about-card">
                        <div class="about-feature">
                            <div class="about-feature-icon"
                                style="background:rgba(241,196,15,0.12);border:1px solid rgba(241,196,15,0.3);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.7" stroke="#f1c40f" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="about-feature-title">Talent Identification</div>
                                <div class="about-feature-desc">Discover and spotlight exceptional academic and
                                    cognitive abilities in students across India.</div>
                            </div>
                        </div>
                        <div class="about-feature">
                            <div class="about-feature-icon"
                                style="background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.3);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.7" stroke="#818cf8" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="about-feature-title">40+ Exam Centres</div>
                                <div class="about-feature-desc">Standardised examination centres across India, all with
                                    trained invigilators and real-time attendance scanning.</div>
                            </div>
                        </div>
                        <div class="about-feature">
                            <div class="about-feature-icon"
                                style="background:rgba(168,85,247,0.12);border:1px solid rgba(168,85,247,0.3);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.7" stroke="#c084fc" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                </svg>
                            </div>
                            <div>
                                <div class="about-feature-title">Genius Jams Awards</div>
                                <div class="about-feature-desc">Winners are celebrated at Genius Jams — a prestigious
                                    national award ceremony honouring India's brightest young minds.</div>
                            </div>
                        </div>
                        <div class="about-feature">
                            <div class="about-feature-icon"
                                style="background:rgba(6,182,212,0.12);border:1px solid rgba(6,182,212,0.3);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.7" stroke="#22d3ee" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="about-feature-title">YES India Schools</div>
                                <div class="about-feature-desc">Open exclusively to students from participating YES
                                    India Schools, ensuring a standardised and fair competitive environment.</div>
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
                <p class="section-sub">Celebrating the brightest minds from YES India Schools, recognised at Genius
                    Jams.</p>

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
                                        $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : 'rank-3');
                                    @endphp
                                    <div class="winner-card">
                                        <div style="position:relative;overflow:hidden;border-radius:20px 20px 0 0;">
                                            <img src="{{ asset('gallery/im3.jpeg') }}" alt="Winners" class="winner-card-img">
                                            <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(6,8,16,0.1) 0%,rgba(6,8,16,0.65) 100%);"></div>
                                            <div style="position:absolute;bottom:10px;left:14px;">
                                                <div class="winner-rank {{ $rankClass }}" style="width:34px;height:34px;border-radius:9px;">
                                                    @if($rank === 1)
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M5.166 2.621v.858c-1.035.148-2.059.33-3.071.543a.75.75 0 0 0-.584.859 6.753 6.753 0 0 0 6.138 5.6 6.73 6.73 0 0 0 2.743 1.346A6.707 6.707 0 0 1 9.279 15H8.54c-1.036 0-1.875.84-1.875 1.875V19.5h-.75a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-.75v-2.625c0-1.036-.84-1.875-1.875-1.875h-.739a6.706 6.706 0 0 1-1.112-3.173 6.73 6.73 0 0 0 2.743-1.347 6.753 6.753 0 0 0 6.139-5.6.75.75 0 0 0-.585-.858 47.077 47.077 0 0 0-3.07-.543V2.62a.75.75 0 0 0-.658-.744 49.798 49.798 0 0 0-6.093-.377.75.75 0 0 0-.657.744Zm0 2.629c0 1.196.312 2.32.857 3.294A5.266 5.266 0 0 1 3.16 5.337a45.6 45.6 0 0 1 2.006-.343v.256Zm13.5 0v-.256c.674.1 1.343.214 2.006.343a5.265 5.265 0 0 1-2.863 3.207 6.72 6.72 0 0 0 .857-3.294Z" clip-rule="evenodd"/></svg>
                                                    @elseif($rank === 2)
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" clip-rule="evenodd"/></svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z"/></svg>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="winner-card-body">
                                            <div
                                                style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:8px;padding:3px 10px;border-radius:6px;display:inline-flex;{{ $palette['tag'] }}">
                                                {{ $categoryName }}</div>
                                            <div class="winner-name">{{ $winner->student->name ?? '—' }}</div>
                                            <div class="winner-school">{{ $winner->student->school->name ?? 'YES India School' }}</div>
                                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
                                                @if($winner->marks_obtained && $winner->max_marks)
                                                    <span class="winner-score" style="{{ $palette['score'] }}border-radius:100px;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" style="width:12px;height:12px;">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                                        </svg>
                                                        {{ $winner->marks_obtained }}/{{ $winner->max_marks }}
                                                    </span>
                                                @endif
                                                @if($winner->percentage)
                                                    <span
                                                        style="font-size:12px;color:#64748b;font-weight:600;">{{ number_format($winner->percentage, 1) }}%</span>
                                                @endif
                                            </div>
                                            @if($winner->examination)
                                                <div style="margin-top:8px;font-size:10.5px;color:#475569;">
                                                    {{ $winner->examination->academic_year ?? '' }} · {{ $winner->examination->name ?? '' }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="border-radius:24px;overflow:hidden;border:1px solid var(--border);">
                        <img src="{{ asset('gallery/im3.jpeg') }}" alt="Previous Winners"
                            style="width:100%;height:320px;object-fit:cover;display:block;">
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
                <p class="section-sub">A glimpse into the YES Genius journey — from exam halls to Genius Jams
                    celebrations.</p>

                <div class="gallery-grid">
                    @php
                        $galleryItems = [
                            ['file' => 'im1.jpeg', 'cls' => 'gi-feat'],
                            ['file' => 'im2.jpeg', 'cls' => 'gi-tr'],
                            ['file' => 'im3.jpeg', 'cls' => 'gi-tl'],
                            ['file' => 'im1.jpeg', 'cls' => 'gi-bl'],
                            ['file' => 'im2.jpeg', 'cls' => 'gi-bm'],
                        ];
                    @endphp
                    @foreach($galleryItems as $item)
                        <div class="gallery-item {{ $item['cls'] }}">
                            <img src="{{ asset('gallery/' . $item['file']) }}"
                                class="gallery-img" loading="lazy">
                            <div class="gallery-overlay">
                                <span class="gallery-caption"></span>
                            </div>
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
                <p class="section-sub">No login required — look up your hall ticket or check results instantly.</p>

                <div class="utilities-grid">
                    <div class="card" x-data="{ ticket: '' }">
                        <div class="card-top-bar"
                            style="background:linear-gradient(90deg,transparent,rgba(6,182,212,0.7),transparent)"></div>
                        <div class="card-icon card-icon-cyan">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor" style="width:22px;height:22px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                            </svg>
                        </div>
                        <div class="card-title">Hall Ticket Verification</div>
                        <p class="card-desc">Enter a candidate's hall ticket number to instantly verify seating details,
                            examination centre, and reporting time.</p>
                        <form class="verify-input-wrap"
                            @submit.prevent="ticket.trim() ? (window.location.href='/verify/hall-ticket/'+encodeURIComponent(ticket.trim())) : null">
                            <input type="text" class="verify-input" x-model="ticket" placeholder="e.g. F89C23D4829E"
                                autocomplete="off">
                            <button type="submit" class="btn-cyan">Verify Candidate →</button>
                        </form>
                    </div>

                    <div class="card" style="display:flex;flex-direction:column;">
                        <div class="card-top-bar"
                            style="background:linear-gradient(90deg,transparent,rgba(168,85,247,0.7),transparent)">
                        </div>
                        <div class="card-icon card-icon-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor" style="width:22px;height:22px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 2.24c-.068.2-.106.416-.106.64 0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.106-.64m-3.537 0A3 3 0 0 1 10.5 4.5h1.5a3 3 0 0 1 2.766 1.838M3 21h10.5a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135.845-2.098 1.976-2.192a48.424 48.424 0 0 1 1.123-.08M3 21V6.108c0-1.135.845-2.098 1.976-2.192a48.424 48.424 0 0 1 1.123-.08M3 21h10.5" />
                            </svg>
                        </div>
                        <div class="card-title">Official Results Portal</div>
                        <p class="card-desc" style="margin-bottom:auto;">Students and parents can access
                            board-certified, digitally authenticated marksheets once results are published. Fully
                            printable and QR-verified.</p>
                        <div style="margin-top:24px;">
                            <a href="{{ route('results.check-form') }}" class="btn-purple">Check Exam Results →</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── PORTAL GATEWAYS ─────────────────── -->
            <div class="section section-sep" id="portals">
                <div class="section-tag">
                    <div class="section-tag-line"></div>
                    <span>Login Portals</span>
                    <div class="section-tag-line"></div>
                </div>
                <h2 class="section-title">Access Gateways</h2>
                <p class="section-sub">Choose your role to access the right portal for your work.</p>

                <div class="gateway-grid">
                    <div class="card gateway-card">
                        <div class="card-top-bar"></div>
                        <div>
                            <div class="card-icon card-icon-indigo">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.6" stroke="currentColor" style="width:22px;height:22px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h18v18H3V3z" />
                                </svg>
                            </div>
                            <span class="gateway-badge badge-indigo" style="margin-bottom:12px;">School Admin</span>
                            <div class="card-title">School Partner Portal</div>
                            <p class="card-desc">Manage student registrations, import candidate data via Excel, monitor
                                application statuses, and bulk-download hall tickets for your institution.</p>
                        </div>
                        <a href="{{ route('login') }}" class="gateway-link gateway-link-indigo"
                            style="font-family:'Outfit',sans-serif;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                            Enter School Desk
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="card gateway-card featured-card">
                        <div class="card-top-bar"
                            style="background:linear-gradient(90deg,transparent,rgba(99,102,241,0.8),transparent);opacity:0.9;">
                        </div>
                        <div>
                            <div class="card-icon card-icon-indigo"
                                style="background:rgba(99,102,241,0.2);border-color:rgba(99,102,241,0.35);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.6" stroke="currentColor" style="width:22px;height:22px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                            </div>
                            <span class="gateway-badge badge-indigo" style="margin-bottom:12px;">Super Admin ·
                                Board</span>
                            <div class="card-title">Board Control Panel</div>
                            <p class="card-desc">Full system oversight — audit schools, manage exam schedules, authorize
                                registrations, configure invigilators, track real-time activity logs, and process final
                                results.</p>
                        </div>
                        <a href="{{ route('login') }}" class="gateway-link"
                            style="color:#fff;border-color:rgba(99,102,241,0.6);background:rgba(99,102,241,0.2);font-family:'Outfit',sans-serif;text-decoration:none;display:inline-flex;align-items:center;gap:6px;justify-content:center;">
                            Board Sign In
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="card gateway-card">
                        <div class="card-top-bar"
                            style="background:linear-gradient(90deg,transparent,rgba(6,182,212,0.6),transparent)"></div>
                        <div>
                            <div class="card-icon card-icon-cyan">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.6" stroke="currentColor" style="width:22px;height:22px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </div>
                            <span class="gateway-badge badge-cyan" style="margin-bottom:12px;">Invigilator</span>
                            <div class="card-title">Invigilator Desk</div>
                            <p class="card-desc">Access live attendance scanning tools, verify candidate hall tickets
                                via barcode, mark on-site presence, and log examination session records in real time.
                            </p>
                        </div>
                        <a href="{{ route('login') }}" class="gateway-link gateway-link-cyan"
                            style="font-family:'Outfit',sans-serif;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                            Invigilator Access
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
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
                            style="color:#fbbf24; font-size:clamp(1rem,2vw,1.5rem);letter-spacing:-0.01em;">Genius Jams
                        </div>
                        <div class="stat-lbl">Award Ceremony</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="footer">
            <div class="footer-inner">
                <span class="footer-copy">&copy; {{ date('Y') }} YES INDIA FOUNDATION. All rights reserved.</span>
                <div class="footer-links">
                    <a href="#utilities">Hall Ticket Lookup</a>
                    <a href="{{ route('results.check-form') }}">Results Portal</a>
                    <a href="{{ route('gallery') }}">Gallery</a>
                    <a href="{{ route('login') }}">Portal Login</a>
                </div>
            </div>
        </footer>

    </div>
</body>

</html>
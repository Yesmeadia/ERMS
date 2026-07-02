<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="ERMS - A unified examination registration, verification and results management system for schools and boards.">
    <title>ERMS — Examination Registration Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300&display=swap"
        rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --color-bg: #07090f;
            --color-surface: rgba(13, 18, 30, 0.7);
            --color-border: rgba(255, 255, 255, 0.06);
            --color-border-hover: rgba(99, 102, 241, 0.35);
            --color-text: #e2e8f0;
            --color-muted: #64748b;
            --indigo: #6366f1;
            --purple: #a855f7;
            --cyan: #06b6d4;
        }

        html {
            background: var(--color-bg);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ─── BACKGROUND ──────────────────────────────── */
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
                linear-gradient(rgba(0, 212, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 212, 255, 0.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            animation: orbDrift 18s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 520px;
            height: 520px;
            background: rgba(99, 102, 241, 0.12);
            top: -140px;
            left: -100px;
            animation-duration: 16s;
        }

        .orb-2 {
            width: 480px;
            height: 480px;
            background: rgba(168, 85, 247, 0.09);
            top: 30%;
            right: -120px;
            animation-duration: 22s;
            animation-delay: -8s;
        }

        .orb-3 {
            width: 400px;
            height: 400px;
            background: rgba(6, 182, 212, 0.07);
            bottom: -80px;
            left: 30%;
            animation-duration: 20s;
            animation-delay: -4s;
        }

        @keyframes orbDrift {
            0% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(40px, -50px) scale(1.1);
            }

            100% {
                transform: translate(-20px, 30px) scale(0.95);
            }
        }

        /* Academic rain */
        .rain-wrap {
            position: absolute;
            inset: 0;
            overflow: hidden;
            opacity: 0.13;
            font-family: 'Courier New', monospace;
            font-size: 10.5px;
            font-weight: 700;
            color: #00d4ff;
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
                transform: translateY(-105vh);
                opacity: 0;
            }
        }

        .ri-1 {
            left: 2%;
            animation-duration: 22s;
            animation-delay: 0s;
        }

        .ri-2 {
            left: 12%;
            animation-duration: 26s;
            animation-delay: -5s;
        }

        .ri-3 {
            left: 22%;
            animation-duration: 20s;
            animation-delay: -9s;
        }

        .ri-4 {
            left: 32%;
            animation-duration: 28s;
            animation-delay: -2s;
        }

        .ri-5 {
            left: 43%;
            animation-duration: 19s;
            animation-delay: -12s;
        }

        .ri-6 {
            left: 54%;
            animation-duration: 24s;
            animation-delay: -6s;
        }

        .ri-7 {
            left: 65%;
            animation-duration: 21s;
            animation-delay: -1s;
        }

        .ri-8 {
            left: 76%;
            animation-duration: 25s;
            animation-delay: -8s;
        }

        .ri-9 {
            left: 88%;
            animation-duration: 18s;
            animation-delay: -14s;
        }

        /* ─── LAYOUT ──────────────────────────────────── */
        .page {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ─── FLOATING NAV BAR ───────────────────────── */
        .header-container {
            position: sticky;
            top: 16px;
            z-index: 200;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            pointer-events: none;
            /* Let clicks pass through container gaps */
            box-sizing: border-box;
        }

        .nav {
            pointer-events: auto;
            /* Restore clicks on nav bar itself */
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 60px;
            background: rgba(13, 18, 30, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.02);
            transition: border-color 0.25s, box-shadow 0.25s;
        }

        .nav:hover {
            border-color: rgba(99, 102, 241, 0.25);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45), 0 0 15px rgba(99, 102, 241, 0.1);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
            min-width: 0;
        }

        .nav-logo {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            object-fit: contain;
            flex-shrink: 0;
            filter: drop-shadow(0 2px 8px rgba(99, 102, 241, 0.3));
        }

        .nav-logo-fallback {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #818cf8;
            font-weight: 800;
            font-size: 16px;
            flex-shrink: 0;
        }

        .nav-name {
            font-size: 16px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.3px;
            white-space: nowrap;
        }

        .nav-sub {
            font-size: 8.5px;
            color: var(--color-muted);
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 1px;
            white-space: nowrap;
        }

        /* Nav links block (desktop only) */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .nav-link-item {
            color: #94a3b8;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            position: relative;
            padding: 8px 0;
            transition: color 0.2s;
        }

        .nav-link-item:hover {
            color: #fff;
        }

        .nav-link-indicator {
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

        .nav-link-item:hover .nav-link-indicator {
            transform: scaleX(1);
        }

        .nav-cta {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--indigo) 0%, #4f52d6 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.25);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .nav-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.45);
        }

        /* Hide the text label in nav-cta on mobile, keep only icon */
        .nav-cta-label {
            display: inline;
        }

        /* Hamburger & drawer: desktop-hidden, mobile-shown */
        .nav-hamburger {
            display: none;
        }

        .mobile-drawer {
            display: none;
        }

        /* ─── MOBILE BOTTOM NAV ───────────────────────── */
        .mobile-bottom-nav {
            display: none;
            /* hidden on desktop */
        }

        /* ─── NAV MOBILE OVERRIDES ─ ≤640px ──────────── */
        @media (max-width: 640px) {
            .nav {
                height: 56px;
                padding: 0 16px;
            }

            .nav-brand {
                gap: 9px;
            }

            .nav-links {
                display: none;
            }

            .nav-logo,
            .nav-logo-fallback {
                width: 32px;
                height: 32px;
                font-size: 15px;
                border-radius: 9px;
            }

            .nav-name {
                font-size: 15px;
            }

            .nav-sub {
                font-size: 8px;
                letter-spacing: 0.08em;
            }

            /* Hide CTA button from mobile header by default */
            .nav-mobile-btn-container {
                display: none;
            }

            /* Show CTA button inline on mobile when hamburger toggled */
            .nav-mobile-btn-container.mobile-show-inline {
                display: inline-flex !important;
            }

            .nav-mobile-btn-container .nav-cta {
                display: inline-flex !important;
                padding: 7px 14px;
                font-size: 13px;
                border-radius: 8px;
            }

            /* ── Hamburger button ── */
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
                transition: background 0.18s, border-color 0.18s, color 0.18s;
                flex-shrink: 0;
            }

            .nav-hamburger:active,
            .nav-hamburger.is-open {
                background: rgba(99, 102, 241, 0.15);
                border-color: rgba(99, 102, 241, 0.35);
                color: #a5b4fc;
            }

            /* Body padding-bottom clear */
            body {
                padding-bottom: 0px;
            }
        }


        /* ─── HERO ────────────────────────────────────── */
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 100px 24px 80px;
            animation: fadeUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(28px);
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
            padding: 6px 16px;
            border-radius: 100px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.25);
            font-size: 12px;
            font-weight: 700;
            color: #a5b4fc;
            letter-spacing: 0.03em;
            margin-bottom: 28px;
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

        .hero-title {
            font-size: clamp(2.4rem, 5.5vw, 4.2rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.08;
            letter-spacing: -0.03em;
            margin-bottom: 22px;
            max-width: 820px;
        }

        .hero-title-gradient {
            background: linear-gradient(130deg, #818cf8 0%, #c084fc 45%, #67e8f9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-desc {
            font-size: 17px;
            color: #94a3b8;
            line-height: 1.7;
            font-weight: 400;
            max-width: 580px;
            margin-bottom: 40px;
        }

        /* Centered logo container on page body */
        .hero-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .hero-logo-item {
            height: 48px;
            width: auto;
            object-fit: contain;
            opacity: 0.85;
            transition: opacity 0.25s, transform 0.25s;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
        }

        .hero-logo-item:hover {
            opacity: 1;
            transform: scale(1.04);
        }

        .hero-logos-divider {
            width: 1px;
            height: 32px;
            background: rgba(255, 255, 255, 0.12);
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-primary {
            padding: 13px 28px;
            border-radius: 12px;
            background: var(--indigo);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 0 24px rgba(99, 102, 241, 0.35);
        }

        .btn-primary:hover {
            background: #4f52d6;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(99, 102, 241, 0.45);
        }

        .btn-ghost {
            padding: 13px 28px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(255, 255, 255, 0.18);
            color: #fff;
            transform: translateY(-2px);
        }

        /* ─── SECTIONS ────────────────────────────────── */
        .section {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .section-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .section-label-line {
            flex: 1;
            max-width: 60px;
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
        }

        .section-label span {
            font-size: 11px;
            font-weight: 700;
            color: var(--color-muted);
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .section-title {
            font-size: clamp(1.5rem, 3vw, 2.1rem);
            font-weight: 800;
            color: #fff;
            text-align: center;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .section-sub {
            text-align: center;
            color: #64748b;
            font-size: 15px;
            margin-bottom: 48px;
        }

        /* ─── UTILITIES (2-up) ────────────────────────── */
        .utilities-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 80px;
            animation: fadeUp 0.9s 0.15s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @media (max-width: 680px) {
            .utilities-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ─── CARDS ───────────────────────────────────── */
        .card {
            background: var(--color-surface);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--color-border);
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
            border-color: var(--color-border-hover);
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

        .card-icon-emerald {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.22);
            color: #34d399;
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

        /* ─── VERIFY CARD ─────────────────────────────── */
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

        /* ─── GATEWAY GRID (3-up) ─────────────────────── */
        .gateway-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 80px;
            animation: fadeUp 0.9s 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @media (max-width: 860px) {
            .gateway-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 861px) and (max-width: 1100px) {
            .gateway-grid {
                grid-template-columns: repeat(3, 1fr);
            }
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

        .badge-purple {
            background: rgba(168, 85, 247, 0.15);
            color: #d8b4fe;
            border: 1px solid rgba(168, 85, 247, 0.25);
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

        .gateway-link-purple {
            color: #c084fc;
            border-color: rgba(168, 85, 247, 0.3);
        }

        .gateway-link-purple:hover {
            background: #9333ea;
            color: #fff;
            border-color: #9333ea;
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

        /* ─── FEATURED GATEWAY ────────────────────────── */
        .featured-card {
            background: linear-gradient(145deg, rgba(79, 82, 214, 0.15), rgba(13, 18, 30, 0.8));
            border-color: rgba(99, 102, 241, 0.3) !important;
        }

        .featured-card::before {
            background: radial-gradient(ellipse at top, rgba(99, 102, 241, 0.12), transparent 70%) !important;
        }

        /* ─── STATS ───────────────────────────────────── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border-radius: 20px;
            overflow: hidden;
            background: var(--color-surface);
            backdrop-filter: blur(18px);
            border: 1px solid var(--color-border);
            margin-bottom: 80px;
            animation: fadeUp 0.9s 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @media (max-width: 680px) {
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-item {
            padding: 32px 20px;
            text-align: center;
            border-right: 1px solid var(--color-border);
            position: relative;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-number {
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.04em;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 11px;
            color: var(--color-muted);
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        @media (max-width: 680px) {
            .stat-item:nth-child(2) {
                border-right: none;
            }

            .stat-item:nth-child(3),
            .stat-item:nth-child(4) {
                border-top: 1px solid var(--color-border);
            }
        }

        /* ─── FOOTER ──────────────────────────────────── */
        .footer {
            margin-top: auto;
            padding: 28px 24px;
            border-top: 1px solid var(--color-border);
            background: rgba(7, 9, 15, 0.6);
            backdrop-filter: blur(10px);
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-copy {
            font-size: 12.5px;
            color: var(--color-muted);
        }

        .footer-links {
            display: flex;
            gap: 24px;
        }

        .footer-links a {
            font-size: 12.5px;
            color: var(--color-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #94a3b8;
        }

        /* ─── SPACING HELPERS ─────────────────────────── */
        .mb-section {
            margin-bottom: 64px;
        }

        /* ─── SCROLLBAR ───────────────────────────────── */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: var(--color-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 99px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }

        /* ─── STATE MODAL ─────────────────────────────── */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9000;
            background: rgba(4, 6, 12, 0.75);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .modal-box {
            width: 100%;
            max-width: 520px;
            background: rgba(13, 18, 30, 0.92);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.03);
            position: relative;
        }

        .modal-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.8), rgba(168, 85, 247, 0.6), transparent);
        }

        .modal-head {
            padding: 28px 28px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .modal-icon {
            width: 46px;
            height: 46px;
            border-radius: 13px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #818cf8;
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 19px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.01em;
            margin-bottom: 4px;
        }

        .modal-sub {
            font-size: 13px;
            color: #64748b;
        }

        .modal-body {
            padding: 20px 28px 28px;
        }

        .modal-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .state-select {
            width: 100%;
            padding: 13px 16px;
            border-radius: 13px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            font-weight: 500;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
            margin-bottom: 18px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19 9-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 42px;
        }

        .state-select:focus {
            border-color: rgba(99, 102, 241, 0.5);
        }

        .state-select option {
            background: #0d1220;
            color: #e2e8f0;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
        }

        .modal-btn-cancel {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #94a3b8;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            transition: all 0.2s;
        }

        .modal-btn-cancel:hover {
            background: rgba(255, 255, 255, 0.09);
            color: #e2e8f0;
        }

        .modal-btn-proceed {
            flex: 2;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            background: var(--indigo);
            border: none;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            transition: all 0.2s;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }

        .modal-btn-proceed:hover {
            background: #4f52d6;
            box-shadow: 0 4px 24px rgba(99, 102, 241, 0.45);
        }

        .modal-btn-proceed:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            box-shadow: none;
        }

        .modal-jk-hint {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 11px;
            background: rgba(6, 182, 212, 0.08);
            border: 1px solid rgba(6, 182, 212, 0.2);
            margin-bottom: 16px;
        }

        .modal-jk-hint-icon {
            color: #22d3ee;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .modal-jk-hint-text {
            font-size: 12.5px;
            color: #94a3b8;
            line-height: 1.5;
        }

        .modal-jk-hint-text strong {
            color: #67e8f9;
        }

        /* Modal animations */
        [x-cloak] {
            display: none !important;
        }

        .modal-enter {
            animation: modalIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
</head>

<body x-data="stateModal()" @keydown.escape.window="close()">
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
            <div class="rain-item ri-5">Q: Magna Carta? A: 1215 AD</div>
            <div class="rain-item ri-6">∇ × B = μ₀J + μ₀ε₀(∂E/∂t)</div>
            <div class="rain-item ri-7">H₂O + CO₂ → H₂CO₃</div>
            <div class="rain-item ri-8">lim (x→0) sin(x)/x = 1</div>
            <div class="rain-item ri-9">e^(iπ) + 1 = 0</div>
        </div>
    </div>
    <div class="page">

        <x-header />
        <div class="hero">
            <div class="hero-logos">
                @if(file_exists(public_path('logo-w.png')))
                    <img src="{{ asset('logo-w.png') }}" alt="Logo W" class="hero-logo-item">
                @endif
                @if(file_exists(public_path('logo-w.png')) && file_exists(public_path('logo1.png')))
                    <div class="hero-logos-divider"></div>
                @endif
                @if(file_exists(public_path('logo1.png')))
                    <img src="{{ asset('logo1.png') }}" alt="Logo 1" class="hero-logo-item">
                @endif
            </div>

            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                Board Examinations 2026 – 27 · Live
            </div>

            <h1 class="hero-title">
                One Portal for<br>
                <span class="hero-title-gradient">Every Examination</span><br>
                Need
            </h1>

            <p class="hero-desc">
                A unified, secure system for schools, invigilators, and board administrators — managing registrations,
                hall tickets, live attendance, and result verifications in one place.
            </p>

            <div class="hero-actions">
                <a href="#portals" class="btn-primary">Access Portals</a>
                <a href="#utilities" class="btn-ghost">Verify Hall Ticket</a>
            </div>
        </div>
        <main>
            <div class="section mb-section" id="utilities">
                <div class="section-label">
                    <div class="section-label-line"></div>
                    <span>Quick Access</span>
                    <div class="section-label-line"></div>
                </div>
                <h2 class="section-title">Public Services</h2>
                <p class="section-sub">No login required — look up your hall ticket or check results instantly.</p>

                <div class="utilities-grid">

                    <!-- Hall Ticket Verify -->
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
                            <input type="text" class="verify-input" x-model="ticket" placeholder="e.g. HT-2026-00458"
                                autocomplete="off">
                            <button type="submit" class="btn-cyan">
                                Verify Candidate →
                            </button>
                        </form>
                    </div>

                    <!-- Results Portal -->
                    <div class="card" style="display:flex;flex-direction:column;">
                        <div class="card-top-bar"
                            style="background:linear-gradient(90deg,transparent,rgba(168,85,247,0.7),transparent)">
                        </div>
                        <div class="card-icon card-icon-purple">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor" style="width:22px;height:22px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 2.24c-.068.2-.106.416-.106.64 0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.106-.64m-3.537 0A3 3 0 0 1 10.5 4.5h1.5a3 3 0 0 1 2.766 1.838M3 21h10.5a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M3 21V6.108c0-1.135.845-2.098 1.976-2.192a48.424 48.424 0 0 1 1.123-.08M3 21h10.5" />
                            </svg>
                        </div>
                        <div class="card-title">Official Results Portal</div>
                        <p class="card-desc" style="margin-bottom:auto;">Students and parents can access
                            board-certified, digitally authenticated marksheets once results are published. Fully
                            printable and QR-verified.</p>
                        <div style="margin-top:24px;">
                            <a href="{{ route('results.check-form') }}" class="btn-purple">
                                Check Exam Results →
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ── PORTAL GATEWAYS ─────────────────── -->
            <div class="section mb-section" id="portals">
                <div class="section-label">
                    <div class="section-label-line"></div>
                    <span>Login Portals</span>
                    <div class="section-label-line"></div>
                </div>
                <h2 class="section-title">Access Gateways</h2>
                <p class="section-sub">Choose your role to access the right portal for your work.</p>

                <div class="gateway-grid">

                    <!-- School Admin -->
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
                        <button @click="open('School Partner Portal')" class="gateway-link gateway-link-indigo"
                            style="cursor:pointer;background:none;font-family:'Outfit',sans-serif;">
                            Enter School Desk
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Board Admin (featured) -->
                    <div class="card gateway-card featured-card">
                        <div class="card-top-bar"
                            style="background:linear-gradient(90deg,transparent,rgba(99,102,241,0.8),transparent);opacity:0.8;">
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
                        <button @click="open('Board Control Panel')" class="gateway-link"
                            style="color:#fff;border-color:rgba(99,102,241,0.6);background:rgba(99,102,241,0.2);cursor:pointer;font-family:'Outfit',sans-serif;">
                            Board Sign In
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Invigilator -->
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
                        <button @click="open('Invigilator Desk')" class="gateway-link gateway-link-cyan"
                            style="cursor:pointer;background:none;font-family:'Outfit',sans-serif;">
                            Invigilator Access
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>

                </div>
            </div>

            <!-- ── STATS BAR ────────────────────────── -->
            <div class="section mb-section">
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="stat-number">140<span style="font-size:60%;color:#818cf8;">+</span></div>
                        <div class="stat-label">Registered Schools</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">15K<span style="font-size:60%;color:#c084fc;">+</span></div>
                        <div class="stat-label">Active Candidates</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">99.9<span style="font-size:60%;color:#22d3ee;">%</span></div>
                        <div class="stat-label">Audit Integrity</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" style="font-size:clamp(1.2rem,2.5vw,1.8rem);letter-spacing:-0.01em;">
                            Real-time</div>
                        <div class="stat-label">Attendance Scan</div>
                    </div>
                </div>
            </div>

        </main>

        <!-- ── FOOTER ─────────────────────────────── -->
        <footer class="footer">
            <div class="footer-inner">
                <span class="footer-copy">&copy; {{ date('Y') }} YES INDIA FOUNDATION. All rights reserved.</span>
                <div class="footer-links">
                    <a href="#utilities">Hall Ticket Lookup</a>
                    <a href="{{ route('results.check-form') }}">Results Portal</a>
                    <a href="{{ route('login') }}">Portal Login</a>
                </div>
            </div>
        </footer>

    </div><!-- .page -->

    <!-- ╔═══════════════════════════════════╗ -->
    <!-- ║  STATE SELECTION MODAL            ║ -->
    <!-- ╚═══════════════════════════════════╝ -->
    <div x-cloak x-show="visible" class="modal-backdrop" @click.self="close()"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="modal-box modal-enter" x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Header -->
            <div class="modal-head">
                <div class="modal-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor" style="width:22px;height:22px;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                    </svg>
                </div>
                <div class="modal-title" x-text="'Accessing: ' + portalName"></div>
                <div class="modal-sub">Select your state to be directed to the correct regional portal.</div>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- J&K hint (shown when J&K selected) -->
                <div class="modal-jk-hint" x-show="selectedState === 'Jammu and Kashmir'" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <svg class="modal-jk-hint-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor" style="width:16px;height:16px;margin-top:1px;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    <div class="modal-jk-hint-text">
                        Jammu &amp; Kashmir uses a dedicated regional portal at <strong>JK Genius Exam Portal</strong>.
                        You will
                        be redirected there.
                    </div>
                </div>

                <div class="modal-label">Select Your State / UT</div>
                <select class="state-select" x-model="selectedState">
                    <option value="">— Choose your state —</option>
                    <optgroup label="States">
                        <option>Andhra Pradesh</option>
                        <option>Bihar</option>
                        <option>Karnataka</option>
                        <option>Kerala</option>
                        <option>Maharashtra</option>
                        <option>Rajasthan</option>
                        <option>West Bengal</option>
                    </optgroup>
                    <optgroup label="Union Territories">
                        <option>Delhi</option>
                        <option>Jammu and Kashmir</option>
                    </optgroup>
                </select>

                <div class="modal-actions">
                    <button class="modal-btn-cancel" @click="close()">Cancel</button>
                    <button class="modal-btn-proceed" :disabled="!selectedState" @click="proceed()">
                        <span
                            x-text="selectedState === 'Jammu and Kashmir' ? 'Go to JK Exam Portal →' : 'Continue to Login →'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Component Logic -->
    <script>
        function stateModal() {
            return {
                visible: false,
                selectedState: '',
                portalName: '',
                loginUrl: '{{ route("login") }}',
                open(name) {
                    this.portalName = name;
                    this.selectedState = '';
                    this.visible = true;
                    // Prevent body scroll
                    document.body.style.overflow = 'hidden';
                },
                close() {
                    this.visible = false;
                    document.body.style.overflow = '';
                },
                proceed() {
                    if (!this.selectedState) return;
                    this.close();
                    if (this.selectedState === 'Jammu and Kashmir') {
                        window.open('https://jkgeniusexam.cyberduce.in', '_blank', 'noopener,noreferrer');
                    } else {
                        window.location.href = this.loginUrl;
                    }
                }
            }
        }
    </script>

</body>

</html>
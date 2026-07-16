<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="YES Genius Examination Gallery — View photos from past award ceremonies, Genius Jams celebrations, and examination events across India.">
    <title>Gallery — YES Genius Examination</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
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
            --surface: rgba(12, 16, 28, 0.80);
            --border: rgba(255, 255, 255, 0.06);
            --text: #e2e8f0;
            --muted: #64748b;
            --indigo: #6366f1;
            --purple: #a855f7;
        }

        html {
            background: var(--bg);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        [x-cloak] {
            display: none !important;
        }

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
            background-image: linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 52px 52px;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            animation: orbDrift 20s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: rgba(99, 102, 241, 0.10);
            top: -100px;
            left: -100px;
            animation-duration: 18s;
        }

        .orb-2 {
            width: 420px;
            height: 420px;
            background: rgba(168, 85, 247, 0.08);
            bottom: 10%;
            right: -80px;
            animation-duration: 24s;
            animation-delay: -9s;
        }

        @keyframes orbDrift {
            0% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(30px, -40px) scale(1.08);
            }

            100% {
                transform: translate(-20px, 28px) scale(0.94);
            }
        }

        /* ── LAYOUT ── */
        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 24px;
            width: 100%;
        }

        /* ── NAV styles are in public-nav component ── */
        .page-header {
            text-align: center;
            padding: 80px 24px 52px;
            animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 100px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.25);
            font-size: 11.5px;
            font-weight: 700;
            color: #a5b4fc;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 22px;
        }

        .page-header-title {
            font-size: clamp(2rem, 4.5vw, 3.6rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.08;
            letter-spacing: -0.03em;
            margin-bottom: 16px;
        }

        .page-title-gradient {
            background: #fbbf24;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-header-sub {
            font-size: 15.5px;
            color: #64748b;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ── FILTER TABS ── */
        .filter-wrap {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 44px;
        }

        .filter-btn {
            padding: 8px 20px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid var(--border);
            background: var(--surface);
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Outfit', sans-serif;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: rgba(99, 102, 241, 0.45);
            color: #a5b4fc;
            background: rgba(99, 102, 241, 0.1);
            box-shadow: 0 0 14px rgba(99, 102, 241, 0.12);
        }

        /* ── GALLERY GRID (Lunchbox / Bento Layout) ── */
        .gallery-section {
            flex: 1;
            padding-bottom: 80px;
        }

        /* Each "panel" of 5 photos in a bento block */
        .gallery-bento {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            grid-template-rows: 280px 220px;
            gap: 14px;
            margin-bottom: 20px;
            animation: fadeUp 0.8s 0.1s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* Bento cell positions */
        .gi-feat {
            grid-column: 1;
            grid-row: 1 / 3;
        }

        /* tall left cell */
        .gi-tr {
            grid-column: 2;
            grid-row: 1;
        }

        /* top centre */
        .gi-tl {
            grid-column: 3;
            grid-row: 1;
        }

        /* top right */
        .gi-bl {
            grid-column: 2;
            grid-row: 2;
        }

        /* bottom centre */
        .gi-bm {
            grid-column: 3;
            grid-row: 2;
        }

        /* bottom right */

        /* overflow cells — simple rows for any remainder beyond multiples of 5 */
        .gallery-overflow {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .gallery-overflow .gallery-item {
            aspect-ratio: 4/3;
        }

        .gallery-item {
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            border: 1px solid var(--border);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s, border-color 0.28s;
            background: rgba(12, 16, 28, 0.6);
        }

        .gallery-item:hover {
            transform: scale(1.02);
            box-shadow: 0 24px 56px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.3);
            z-index: 2;
        }

        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), filter 0.3s;
            filter: brightness(0.92) saturate(1.05);
        }

        .gallery-item:hover .gallery-img {
            transform: scale(1.07);
            filter: brightness(1) saturate(1.15);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(6, 8, 16, 0.82) 0%, rgba(6, 8, 16, 0.15) 55%, transparent 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 18px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-caption {
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }

        .gallery-tag {
            display: inline-flex;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(99, 102, 241, 0.25);
            color: #a5b4fc;
            width: fit-content;
        }

        .gallery-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 52px;
            gap: 12px;
        }

        .gallery-placeholder-lbl {
            font-size: 12px;
            color: #475569;
            font-weight: 600;
        }

        .zoom-icon {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a5b4fc;
            opacity: 0;
            transition: opacity 0.25s;
        }

        .gallery-item:hover .zoom-icon {
            opacity: 1;
        }

        /* ── LIGHTBOX ── */
        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 9000;
            background: rgba(4, 6, 14, 0.92);
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .lightbox-img {
            max-width: 90vw;
            max-height: 85vh;
            border-radius: 16px;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.7);
            object-fit: contain;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .lightbox-placeholder {
            width: min(480px, 90vw);
            height: min(360px, 70vh);
            border-radius: 16px;
            background: rgba(12, 16, 28, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            gap: 12px;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.7);
        }

        .lightbox-placeholder-lbl {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 20px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
        }

        .lightbox-info {
            position: absolute;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .lightbox-caption {
            font-size: 14px;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 4px;
        }

        .lightbox-sub {
            font-size: 12px;
            color: #64748b;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(12, 16, 28, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .lightbox-nav:hover {
            background: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.35);
            color: #a5b4fc;
        }

        .lightbox-prev {
            left: 16px;
        }

        .lightbox-next {
            right: 16px;
        }

        /* ── FOOTER styles are in public-footer component ── */

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .gallery-bento {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto;
            }

            .gi-feat {
                grid-column: 1 / -1;
                grid-row: auto;
                aspect-ratio: 16/9;
            }

            .gi-tr,
            .gi-tl,
            .gi-bl,
            .gi-bm {
                grid-column: auto;
                grid-row: auto;
            }

            .gallery-overflow {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            /* Nav responsive styles are in public-nav component */

            .gallery-bento {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }

            .gi-feat,
            .gi-tr,
            .gi-tl,
            .gi-bl,
            .gi-bm {
                grid-column: 1;
                grid-row: auto;
                aspect-ratio: 4/3;
            }

            .gallery-overflow {
                grid-template-columns: 1fr;
            }

            .lightbox-nav {
                display: none;
            }

            .container {
                padding: 0 16px;
            }
        }

        /* Nav 440px override style is in public-nav component */
    </style>
</head>

<body>
    <div class="bg-wrap">
        <div class="bg-grid"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="page" x-data="{
    menuOpen: false,
    lightbox: false,
    activeImg: null,
    activeCaption: '',
    activeTag: '',
    activeIdx: 0,
    filter: 'all',
    photos: [
        { file: 'im1.jpeg' },
        { file: 'im2.jpeg' },
        { file: 'im3.jpeg' },
        { file: 'im1.jpeg' },
        { file: 'im2.jpeg' },
        { file: 'im3.jpeg' },
        { file: 'im1.jpeg' },
        { file: 'im2.jpeg' },
        { file: 'im3.jpeg' },
    ],
    get filtered() {
        if (this.filter === 'all') return this.photos;
        return this.photos.filter(p => p.tag === this.filter || p.year === this.filter);
    },
    openLightbox(idx) {
        this.activeIdx = idx;
        const p = this.filtered[idx];
        this.activeCaption = p.caption;
        this.activeTag = p.tag;
        this.lightbox = true;
        document.body.style.overflow = 'hidden';
    },
    closeLightbox() { this.lightbox = false; document.body.style.overflow = ''; },
    prevPhoto() { this.activeIdx = (this.activeIdx - 1 + this.filtered.length) % this.filtered.length; this.activeCaption = this.filtered[this.activeIdx].caption; this.activeTag = this.filtered[this.activeIdx].tag; },
    nextPhoto() { this.activeIdx = (this.activeIdx + 1) % this.filtered.length; this.activeCaption = this.filtered[this.activeIdx].caption; this.activeTag = this.filtered[this.activeIdx].tag; }
}" @keydown.escape.window="closeLightbox()" @keydown.arrowleft.window="lightbox && prevPhoto()"
        @keydown.arrowright.window="lightbox && nextPhoto()">

        <!-- NAV COMPONENT -->
        <x-public-nav page="gallery" />

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h1 class="page-header-title">
                Moments of <span class="page-title-gradient">Excellence</span>
            </h1>
            <p class="page-header-sub">
                From examination halls to Genius Jams celebrations — a visual journey through the YES Genius programme.
            </p>
        </div>

        <!-- GALLERY SECTION -->
        <section class="gallery-section">
            <div class="container">

                <!-- Bento panels: one per group of 5 photos -->
                <template x-for="(panel, pi) in Math.ceil(filtered.length / 5)" :key="pi">
                    <div class="gallery-bento">

                        <!-- Cell 0 of panel: gi-feat (tall left) -->
                        <template x-if="filtered[pi * 5 + 0]">
                            <div class="gallery-item gi-feat" @click="openLightbox(pi * 5 + 0)">
                                <img :src="'/gallery/' + filtered[pi * 5 + 0].file" class="gallery-img" loading="lazy">
                                <div class="zoom-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                    </svg>
                                </div>
                            </div>
                        </template>

                        <!-- Cell 1: gi-tr (top centre) -->
                        <template x-if="filtered[pi * 5 + 1]">
                            <div class="gallery-item gi-tr" @click="openLightbox(pi * 5 + 1)">
                                <img :src="'/gallery/' + filtered[pi * 5 + 1].file" :alt="filtered[pi * 5 + 1].caption"
                                    class="gallery-img" loading="lazy">
                                <div class="gallery-overlay">
                                    <div class="gallery-caption" x-text="filtered[pi * 5 + 1].caption"></div>
                                    <div class="gallery-tag" x-text="filtered[pi * 5 + 1].tag"></div>
                                </div>
                                <div class="zoom-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                    </svg>
                                </div>
                            </div>
                        </template>

                        <!-- Cell 2: gi-tl (top right) -->
                        <template x-if="filtered[pi * 5 + 2]">
                            <div class="gallery-item gi-tl" @click="openLightbox(pi * 5 + 2)">
                                <img :src="'/gallery/' + filtered[pi * 5 + 2].file" :alt="filtered[pi * 5 + 2].caption"
                                    class="gallery-img" loading="lazy">
                                <div class="gallery-overlay">
                                    <div class="gallery-caption" x-text="filtered[pi * 5 + 2].caption"></div>
                                    <div class="gallery-tag" x-text="filtered[pi * 5 + 2].tag"></div>
                                </div>
                                <div class="zoom-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                    </svg>
                                </div>
                            </div>
                        </template>

                        <!-- Cell 3: gi-bl (bottom centre) -->
                        <template x-if="filtered[pi * 5 + 3]">
                            <div class="gallery-item gi-bl" @click="openLightbox(pi * 5 + 3)">
                                <img :src="'/gallery/' + filtered[pi * 5 + 3].file" :alt="filtered[pi * 5 + 3].caption"
                                    class="gallery-img" loading="lazy">
                                <div class="gallery-overlay">
                                    <div class="gallery-caption" x-text="filtered[pi * 5 + 3].caption"></div>
                                    <div class="gallery-tag" x-text="filtered[pi * 5 + 3].tag"></div>
                                </div>
                                <div class="zoom-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                    </svg>
                                </div>
                            </div>
                        </template>

                        <!-- Cell 4: gi-bm (bottom right) -->
                        <template x-if="filtered[pi * 5 + 4]">
                            <div class="gallery-item gi-bm" @click="openLightbox(pi * 5 + 4)">
                                <img :src="'/gallery/' + filtered[pi * 5 + 4].file" :alt="filtered[pi * 5 + 4].caption"
                                    class="gallery-img" loading="lazy">
                                <div class="gallery-overlay">
                                    <div class="gallery-caption" x-text="filtered[pi * 5 + 4].caption"></div>
                                    <div class="gallery-tag" x-text="filtered[pi * 5 + 4].tag"></div>
                                </div>
                                <div class="zoom-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                    </svg>
                                </div>
                            </div>
                        </template>

                    </div>
                </template>

                <!-- Empty state -->
                <template x-if="filtered.length === 0">
                    <div
                        style="text-align:center;padding:80px 20px;background:var(--surface);border:1px dashed var(--border);border-radius:20px;color:var(--muted);">
                        <div style="display:flex;justify-content:center;margin-bottom:16px;">
                            <div
                                style="width:64px;height:64px;border-radius:16px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);display:flex;align-items:center;justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="#818cf8" style="width:28px;height:28px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </div>
                        </div>
                        <div style="font-size:15px;font-weight:600;color:#e2e8f0;margin-bottom:6px;">No photos in this
                            category</div>
                        <div style="font-size:13px;">Try selecting a different filter above.</div>
                    </div>
                </template>

            </div>
        </section>

        <!-- LIGHTBOX -->
        <div class="lightbox" x-show="lightbox" x-cloak @click.self="closeLightbox()"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <template x-if="filtered[activeIdx]">
                <img :src="'/gallery/' + filtered[activeIdx].file" :alt="activeCaption" class="lightbox-img">
            </template>

            <button class="lightbox-close" @click="closeLightbox()" aria-label="Close">✕</button>

            <button class="lightbox-nav lightbox-prev" @click="prevPhoto()" aria-label="Previous">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button class="lightbox-nav lightbox-next" @click="nextPhoto()" aria-label="Next">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <div class="lightbox-info">
                <div class="lightbox-caption" x-text="activeCaption"></div>
                <div class="lightbox-sub" x-text="(activeIdx + 1) + ' of ' + filtered.length + ' · YES Genius'"></div>
            </div>
        </div>

        <!-- FOOTER COMPONENT -->
        <x-public-footer page="gallery" />

    </div>
</body>

</html>
@props(['page' => 'home'])

@php
    $isHome    = $page === 'home';
    $isGallery = $page === 'gallery';
@endphp

<style>
    /* ── NAV COMPONENT ── */
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
        color: #64748b;
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

    .nav-link:hover,
    .nav-link.active {
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

    .nav-link:hover::after,
    .nav-link.active::after {
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
        font-family: 'Outfit', sans-serif;
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

    /* Responsive nav */
    @media (max-width: 680px) {
        .nav-links { display: none; }
        .nav .nav-cta { display: none; }

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
        }

        @keyframes drawerIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
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
    }

    @media (max-width: 440px) {
        .nav { height: 56px; padding: 0 14px; }
    }
</style>

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
            <a href="{{ $isHome ? '#about' : '/#about' }}" class="nav-link">About<span></span></a>
            <a href="{{ $isHome ? '#winners' : '/#winners' }}" class="nav-link">Winners<span></span></a>
            <a href="{{ route('gallery') }}" class="nav-link {{ $isGallery ? 'active' : '' }}">Gallery<span></span></a>
            <a href="{{ $isHome ? '#portals' : '/#portals' }}" class="nav-link">Portals<span></span></a>
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
        @if(!$isHome)
            <a href="/" @click="menuOpen=false">← Home</a>
        @endif
        <a href="{{ $isHome ? '#about' : '/#about' }}" @click="menuOpen=false">About</a>
        <a href="{{ $isHome ? '#winners' : '/#winners' }}" @click="menuOpen=false">Winners</a>
        <a href="{{ route('gallery') }}" @click="menuOpen=false">Gallery</a>
        <a href="{{ $isHome ? '#utilities' : '/#utilities' }}" @click="menuOpen=false">Hall Ticket Verify</a>
        <a href="{{ route('results.check-form') }}" @click="menuOpen=false">Results Portal</a>
        <a href="{{ $isHome ? '#portals' : '/#portals' }}" @click="menuOpen=false">Portals</a>
        <a href="{{ route('login') }}" class="nav-cta" style="margin-top:8px;">Sign In →</a>
    </div>
</div>

@props(['page' => 'home'])

@php
    $isHome = $page === 'home';
@endphp

<style>
    /* ── FOOTER COMPONENT ── */
    .footer {
        margin-top: auto;
        padding: 32px 24px;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
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
        color: #64748b;
    }

    .footer-links {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
    }

    .footer-links a {
        font-size: 12.5px;
        color: #64748b;
        text-decoration: none;
        transition: color 0.2s;
    }

    .footer-links a:hover {
        color: #94a3b8;
    }
</style>

<footer class="footer">
    <div class="footer-inner">
        <span class="footer-copy">&copy; {{ date('Y') }} YES INDIA FOUNDATION. All rights reserved.</span>
        <div class="footer-links">
            @if(!$isHome)
                <a href="/">Home</a>
            @endif
            <a href="{{ $isHome ? '#utilities' : '/#utilities' }}">Hall Ticket Lookup</a>
            <a href="{{ route('results.check-form') }}">Results Portal</a>
            @if($isHome)
                <a href="{{ route('gallery') }}">Gallery</a>
            @endif
            <a href="{{ route('login') }}">Portal Login</a>
        </div>
    </div>
</footer>

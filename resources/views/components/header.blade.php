<header x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" class="header-container">
    <nav class="nav">
        <!-- Brand / Logo -->
        <a class="nav-brand" href="#" @click="menuOpen = false">
            @if(file_exists(public_path('logo-w.png')))
                <img src="{{ asset('logo-w.png') }}" alt="ERMS" class="nav-logo">
            @elseif(file_exists(public_path('icon.png')))
                <img src="{{ asset('icon.png') }}" alt="ERMS" class="nav-logo">
            @else
                <div class="nav-logo-fallback">E</div>
            @endif
            <div>
                <div class="nav-name">ERMS</div>
                <div class="nav-sub">Exam Portal</div>
            </div>
        </a>

        <!-- Desktop Navigation Links (Hidden on mobile) -->
        <div class="nav-links">
            <a href="#portals" class="nav-link-item">
                <span>Access Portals</span>
                <span class="nav-link-indicator"></span>
            </a>
            <a href="#utilities" class="nav-link-item">
                <span>Quick Services</span>
                <span class="nav-link-indicator"></span>
            </a>
            <a href="{{ route('results.check-form') }}" class="nav-link-item">
                <span>Exam Results</span>
                <span class="nav-link-indicator"></span>
            </a>
        </div>

        <!-- Right: Actions -->
        <div style="display:flex;align-items:center;gap:8px;">
            <!-- Inline Sign In / Dashboard button -->
            <div :class="{ 'mobile-show-inline': menuOpen }" class="nav-mobile-btn-container">
                @auth
                    <a href="{{ url('/dashboard') }}" class="nav-cta">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2"
                            stroke="currentColor" style="width:15px;height:15px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        <span class="nav-cta-label">Dashboard</span>
                    </a>
                @else
                    <button @click="open('Sign In')" class="nav-cta" style="border:none;cursor:pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2"
                            stroke="currentColor" style="width:15px;height:15px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                        </svg>
                        <span class="nav-cta-label">Sign In</span>
                    </button>
                @endauth
            </div>

            {{-- Hamburger (mobile only) --}}
            <button class="nav-hamburger" :class="{ 'is-open': menuOpen }" @click="menuOpen = !menuOpen" aria-label="Toggle menu">
                {{-- Bars icon (closed) --}}
                <svg x-show="!menuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                {{-- X icon (open) --}}
                <svg x-show="menuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </nav>
</header>

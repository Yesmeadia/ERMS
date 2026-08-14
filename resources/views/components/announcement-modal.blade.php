@props(['unreadAnnouncement' => null])

<!-- Custom HTML/CSS Announcement Modal (CSP Nonce Compliant & Fail-Safe Read Dismissal) -->
<div id="announcement-modal"
    x-data="{ modalOpen: true }"
    x-show="modalOpen"
    class="fixed inset-0 z-[99999] items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md transition-all duration-300 {{ $unreadAnnouncement ? 'flex' : 'hidden' }}"
    style="{{ $unreadAnnouncement ? 'display: flex;' : 'display: none;' }}"
    data-announcement-id="{{ is_object($unreadAnnouncement) ? $unreadAnnouncement->id : ($unreadAnnouncement['id'] ?? '') }}">

    <div class="relative w-full max-w-xl bg-slate-900 border border-blue-500/40 rounded-2xl shadow-2xl shadow-blue-950/80 overflow-hidden transform transition-all duration-300 scale-100">
        <!-- Glowing Blue Accent Top Line -->
        <div class="h-2 w-full" style="background: linear-gradient(90deg, #2563eb 0%, #38bdf8 50%, #06b6d4 100%);"></div>

        <div class="p-6 sm:p-8">
            <!-- Modal Header -->
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-1 min-w-0">
                    <span class="inline-block px-3 py-1 mb-1.5 text-[11px] uppercase font-bold tracking-wider text-blue-300 bg-blue-500/20 border border-blue-400/30 rounded-full">
                        Official Announcement
                    </span>
                    <h3 id="announcement-modal-title" class="text-xl font-bold text-white tracking-tight leading-snug">
                        {{ is_object($unreadAnnouncement) ? $unreadAnnouncement->title : ($unreadAnnouncement['title'] ?? 'New Announcement') }}
                    </h3>
                </div>
            </div>

            <!-- Modal Message Body -->
            <div class="mt-4 mb-6 bg-slate-950/70 p-5 rounded-xl border border-slate-800/80 max-h-80 overflow-y-auto">
                <p id="announcement-modal-message" class="text-sm text-slate-200 whitespace-pre-line leading-relaxed">
                    {{ is_object($unreadAnnouncement) ? $unreadAnnouncement->message : ($unreadAnnouncement['message'] ?? '') }}
                </p>
            </div>

            <!-- Modal Footer Action -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800/80">
                <button type="button" id="announcement-modal-close-btn" data-dismiss="announcement-modal"
                    @click="modalOpen = false; window.dismissAnnouncementModal($event);"
                    onclick="window.dismissAnnouncementModal(event)"
                    style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #0284c7 100%) !important; color: #ffffff !important; font-weight: 600; cursor: pointer; border: 1px solid #60a5fa;"
                    class="w-full sm:w-auto px-7 py-3 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-600/40 hover:opacity-90 active:scale-95 transition-all duration-200 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Close / I Understand</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Silent Fail-Safe Background Submission Form & Iframe -->
<form id="announcement-read-form" action="{{ route('announcements.read-direct') }}" method="POST" target="announcement-read-iframe" style="display: none !important;">
    @csrf
    <input type="hidden" name="announcement_id" id="announcement-read-form-id" value="{{ is_object($unreadAnnouncement) ? $unreadAnnouncement->id : ($unreadAnnouncement['id'] ?? '') }}">
</form>
<iframe name="announcement-read-iframe" id="announcement-read-iframe" style="display: none !important; width: 0; height: 0; border: 0;"></iframe>

<script @nonce>
    (function () {
        const localDismissed = new Set();

        function isLocallyDismissed(id) {
            if (!id) return false;
            const strId = String(id);
            if (localDismissed.has(strId)) return true;
            try {
                if (localStorage.getItem('announcement_read_' + strId) === '1') return true;
                if (sessionStorage.getItem('announcement_read_' + strId) === '1') return true;
            } catch (e) {}
            return false;
        }

        window.dismissAnnouncementModal = function (e) {
            if (e) {
                if (e.preventDefault) e.preventDefault();
                if (e.stopPropagation) e.stopPropagation();
            }

            const modal = document.getElementById('announcement-modal');
            let announcementId = modal ? modal.getAttribute('data-announcement-id') : null;

            // 1. INSTANTLY HIDE THE MODAL VISUALLY
            if (modal) {
                modal.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;';
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            if (announcementId) {
                const strId = String(announcementId);
                localDismissed.add(strId);
                try {
                    localStorage.setItem('announcement_read_' + strId, '1');
                    sessionStorage.setItem('announcement_read_' + strId, '1');
                } catch (err) {}

                // 2. FAIL-SAFE METHOD 1: NATIVE HIDDEN FORM SUBMISSION TO IFRAME (Sends full session cookies + CSRF token)
                const readForm = document.getElementById('announcement-read-form');
                const readInput = document.getElementById('announcement-read-form-id');
                if (readForm && readInput) {
                    readInput.value = announcementId;
                    try {
                        readForm.submit();
                    } catch (e) {}
                }

                // 3. FAIL-SAFE METHOD 2: FETCH WITH SAME-ORIGIN CREDENTIALS
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const token = csrfMeta ? csrfMeta.getAttribute('content') : '';

                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                };
                const bodyData = JSON.stringify({ _token: token, announcement_id: announcementId });

                fetch('/announcements/read-direct', { method: 'POST', credentials: 'same-origin', headers: headers, body: bodyData, keepalive: true }).catch(function() {});
                fetch('/announcements/' + announcementId + '/read', { method: 'POST', credentials: 'same-origin', headers: headers, body: bodyData, keepalive: true }).catch(function() {});
                fetch('/school/announcements/' + announcementId + '/read', { method: 'POST', credentials: 'same-origin', headers: headers, body: bodyData, keepalive: true }).catch(function() {});
            }
        };

        window.showAnnouncementModal = function (announcement) {
            const modal = document.getElementById('announcement-modal');
            const titleEl = document.getElementById('announcement-modal-title');
            const messageEl = document.getElementById('announcement-modal-message');

            if (!modal || !titleEl || !messageEl || !announcement) return;

            if (isLocallyDismissed(announcement.id)) return;

            modal.setAttribute('data-announcement-id', announcement.id);
            titleEl.textContent = announcement.title;
            messageEl.textContent = announcement.message;

            const readInput = document.getElementById('announcement-read-form-id');
            if (readInput) readInput.value = announcement.id;

            modal.style.cssText = 'display: flex !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important;';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        // Capture-phase event delegation for click
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('#announcement-modal-close-btn, [data-dismiss="announcement-modal"]');
            if (btn) {
                window.dismissAnnouncementModal(e);
            }
        }, true);

        // Check on initial load if announcement was already read in session/local storage
        document.addEventListener('DOMContentLoaded', function() {
            const initialModal = document.getElementById('announcement-modal');
            if (initialModal) {
                const initialId = initialModal.getAttribute('data-announcement-id');
                if (initialId && isLocallyDismissed(initialId)) {
                    initialModal.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;';
                    initialModal.classList.add('hidden');
                    initialModal.classList.remove('flex');
                }
            }
        });

        // Shared Hosting Polling Check (every 30 seconds)
        function checkUnreadAnnouncement() {
            const modal = document.getElementById('announcement-modal');
            if (modal && modal.style.display !== 'none' && !modal.classList.contains('hidden')) {
                return;
            }

            fetch('/announcements/check-unread', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.announcement && !isLocallyDismissed(data.announcement.id)) {
                    window.showAnnouncementModal(data.announcement);
                }
            })
            .catch(function () { });
        }

        setInterval(checkUnreadAnnouncement, 30000);
    })();
</script>
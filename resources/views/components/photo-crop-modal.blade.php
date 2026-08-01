{{--
    Passport Photo Crop Modal Component
    ------------------------------------
    Usage: <x-photo-crop-modal />

    Events (window-level):
      - open-photo-cropper  { imageDataUrl, targetPreviewId, targetHiddenId }
        → Opens the modal with the given image and wires up crop output to:
            #targetPreviewId  (img / alpine preview)
            #targetHiddenId   (hidden input carrying Base64 for server)

    Notes:
      - Aspect ratio locked to 35mm × 45mm passport ratio
      - Minimum crop output: 350 × 450 px
      - Cropper.js loaded from CDN (only once per page)
--}}

{{-- Cropper.js CSS --}}
@once
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css"
        referrerpolicy="no-referrer"
    />
@endonce

{{-- Modal Root --}}
<div
    id="photoCropModal"
    style="display:none; z-index:9999;"
    class="fixed inset-0 flex items-center justify-center p-4"
    aria-modal="true"
    role="dialog"
    aria-label="Crop Candidate Photograph"
>
    {{-- Backdrop --}}
    <div
        id="photoCropBackdrop"
        class="absolute inset-0 bg-black/80 backdrop-blur-sm cursor-pointer"
    ></div>

    {{-- Modal Panel --}}
    <div class="relative z-10 w-full max-w-xl rounded-2xl border border-slate-700/60 bg-slate-900 shadow-2xl flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/60">
            <div class="flex items-center gap-2.5 text-indigo-400">
                {{-- Crop icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v4.5M3 6.75h4.5m10.5-3.75V6.75m-3.75-3.75H18m-3.75 13.5H18m3.75-3.75H18M6.75 18v3.75M3 17.25H6.75M6.75 6.75v10.5" />
                </svg>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200">Crop Candidate Photo</h3>
            </div>
            <button
                type="button"
                id="photoCropCloseBtn"
                class="text-slate-400 hover:text-slate-100 transition-colors p-1 rounded-lg hover:bg-slate-800 cursor-pointer"
                aria-label="Close"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Crop Workspace --}}
        <div class="px-6 pt-5 pb-3">

            {{-- Info banner --}}
            <div class="mb-3 flex items-start gap-2 bg-indigo-600/10 border border-indigo-500/20 rounded-xl px-3.5 py-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p class="text-[11px] text-indigo-300 leading-relaxed">
                    Frame candidate's face in standard <strong>passport size (35mm × 45mm / 7:9 ratio)</strong>.
                </p>
            </div>

            {{-- Cropper image container --}}
            <div class="bg-slate-950 rounded-xl overflow-hidden" style="max-height: 400px;">
                <img
                    id="photoCropperImage"
                    src=""
                    alt="Photo to crop"
                    style="display:block; max-width:100%; max-height:400px;"
                />
            </div>

            {{-- Size warning --}}
            <p
                id="photoCropSizeWarning"
                style="display:none;"
                class="mt-2 text-xs text-rose-400 flex items-center gap-1.5"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <span id="photoCropSizeWarningText">Selection too small. Please zoom in or enlarge the crop area.</span>
            </p>

            {{-- Live crop size indicator --}}
            <p class="mt-1.5 text-[10px] text-slate-500 text-right">
                Crop size: <span id="photoCropSizeDisplay" class="text-slate-400 font-mono">— × —</span>
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-800/60 bg-slate-900/80">
            <button
                type="button"
                id="photoCropCancelBtn"
                class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl transition-all cursor-pointer"
            >
                Cancel
            </button>
            <button
                type="button"
                id="photoCropConfirmBtn"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-600/20 flex items-center gap-2 cursor-pointer"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Use This Photo
            </button>
        </div>
    </div>
</div>

{{-- Cropper.js Script --}}
@once
<script
    @nonce
    src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"
    referrerpolicy="no-referrer"
></script>

<script @nonce>
(function () {
    'use strict';

    const MIN_W = 350;
    const MIN_H = 450;
    const ASPECT = 35 / 45; // 35mm × 45mm Passport ratio (0.7777...)

    let cropperInstance   = null;
    let targetPreviewId   = null;
    let targetHiddenId    = null;
    let targetFileInputId  = null;

    const modal      = document.getElementById('photoCropModal');
    const imgEl      = document.getElementById('photoCropperImage');
    const warnEl     = document.getElementById('photoCropSizeWarning');
    const warnText   = document.getElementById('photoCropSizeWarningText');
    const sizeDisp   = document.getElementById('photoCropSizeDisplay');
    const confirmBtn = document.getElementById('photoCropConfirmBtn');

    /* ── Attach CSP-compliant Event Listeners ── */
    function attachListeners() {
        document.getElementById('photoCropBackdrop')?.addEventListener('click', function () { PhotoCropper.cancel(); });
        document.getElementById('photoCropCloseBtn')?.addEventListener('click', function () { PhotoCropper.cancel(); });
        document.getElementById('photoCropCancelBtn')?.addEventListener('click', function () { PhotoCropper.cancel(); });
        document.getElementById('photoCropConfirmBtn')?.addEventListener('click', function () { PhotoCropper.confirm(); });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachListeners);
    } else {
        attachListeners();
    }

    /* ── Open Event Listener ── */
    window.addEventListener('open-photo-cropper', function (e) {
        const detail = e.detail || {};
        targetPreviewId   = detail.targetPreviewId   || null;
        targetHiddenId    = detail.targetHiddenId    || null;
        targetFileInputId = detail.targetFileInputId || null;

        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }

        function startCropper() {
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
            if (typeof Cropper === 'undefined') {
                console.error('Cropper library is not loaded');
                return;
            }
            cropperInstance = new Cropper(imgEl, {
                aspectRatio : ASPECT,
                viewMode    : 1,
                dragMode    : 'move',
                autoCropArea: 0.9,
                responsive  : true,
                guides      : true,
                center      : true,
                highlight   : true,
                cropBoxMovable   : true,
                cropBoxResizable : true,
                toggleDragModeOnDblclick: false,
                ready: function () { PhotoCropper._updateSize(); },
                crop : function () { PhotoCropper._updateSize(); },
            });
        }

        imgEl.onload = function () {
            imgEl.onload = null;
            startCropper();
        };

        imgEl.src = detail.imageDataUrl;
        if (imgEl.complete && imgEl.naturalWidth) {
            imgEl.onload = null;
            startCropper();
        }
    });

    /* ── Public API ── */
    window.PhotoCropper = {
        cancel: function () {
            _close();
            if (targetFileInputId) {
                const fi = document.getElementById(targetFileInputId);
                if (fi && !fi.files.length) fi.value = '';
            }
        },

        confirm: function () {
            if (!cropperInstance) return;

            const data = cropperInstance.getData(true);
            const cropW = Math.round(data.width) || MIN_W;
            const cropH = Math.round(data.height) || MIN_H;

            // Auto-scale to minimum 350x450 px passport resolution if selection is smaller
            const outW = Math.max(MIN_W, cropW);
            const outH = Math.max(MIN_H, cropH);

            // Export canvas at exact output size
            const canvas = cropperInstance.getCroppedCanvas({
                width           : outW,
                height          : outH,
                imageSmoothingEnabled : true,
                imageSmoothingQuality : 'high',
            });

            const base64 = canvas.toDataURL('image/jpeg', 0.92);

            // Push to hidden field
            if (targetHiddenId) {
                const hidden = document.getElementById(targetHiddenId);
                if (hidden) hidden.value = base64;
            }

            // Sync with file input if provided
            if (targetFileInputId && window.DataTransfer) {
                canvas.toBlob(function (blob) {
                    if (blob) {
                        try {
                            const file = new File([blob], 'candidate-photo.jpg', { type: 'image/jpeg' });
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            const fileInput = document.getElementById(targetFileInputId);
                            if (fileInput) fileInput.files = dt.files;
                        } catch (err) {
                            console.warn('Could not set DataTransfer files', err);
                        }
                    }
                }, 'image/jpeg', 0.92);
            }

            // Push to preview
            if (targetPreviewId) {
                const preview = document.getElementById(targetPreviewId);
                if (preview) {
                    preview.src = base64;

                    // If Alpine.js is managing the preview, update x-data state directly
                    const alpineWrapper = preview.closest('[x-data]');
                    if (alpineWrapper && alpineWrapper.__x) {
                        alpineWrapper.__x.$data.photoPreview = base64;
                    }
                    preview.dispatchEvent(new CustomEvent('photo-cropped', { bubbles: true, detail: { base64 } }));
                }
            }

            // For Alpine-managed preview using x-data (update via custom event on document)
            if (targetHiddenId) {
                document.dispatchEvent(new CustomEvent('photo-crop-done', {
                    detail: { hiddenId: targetHiddenId, base64 }
                }));
            }

            _close();
        },

        _updateSize: function () {
            if (!cropperInstance) return;
            const d = cropperInstance.getData(true);
            const w = Math.round(d.width);
            const h = Math.round(d.height);

            if (sizeDisp) {
                sizeDisp.textContent = `${w} × ${h} px (Passport 35mm × 45mm)`;
                sizeDisp.classList.remove('text-rose-400');
                sizeDisp.classList.add('text-slate-400');
            }
            _hideWarning();
            if (confirmBtn) {
                confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                confirmBtn.disabled = false;
            }
        },
    };

    function _close() {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        imgEl.src = '';
        modal.style.display = 'none';
        document.body.style.overflow = '';
        _hideWarning();
    }

    function _showWarning(msg) {
        if (warnText) warnText.textContent = msg;
        if (warnEl) warnEl.style.display = 'flex';
    }

    function _hideWarning() {
        if (warnEl) warnEl.style.display = 'none';
    }
})();
</script>
@endonce

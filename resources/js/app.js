// Global Custom Dialog Elements Setup
function createDialogContainer() {
    if (document.getElementById('custom-alert-modal')) return;

    const modalHTML = `
        <div id="custom-alert-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm transition-opacity duration-300">
            <div class="dialog-card w-full max-w-md bg-slate-900/90 border border-slate-800/80 rounded-3xl p-6 shadow-2xl relative overflow-hidden flex flex-col items-center text-center">
                <!-- Decorative glowing orb -->
                <div id="custom-alert-glow" class="absolute -top-24 -left-24 w-48 h-48 rounded-full blur-3xl pointer-events-none"></div>
                
                <!-- Icon container -->
                <div id="custom-alert-icon-container" class="w-16 h-16 rounded-2xl bg-slate-950 border border-slate-800/60 flex items-center justify-center mb-4 relative z-10">
                </div>

                <!-- Content -->
                <h3 id="custom-alert-title" class="text-lg font-bold text-white mb-2 relative z-10 tracking-tight"></h3>
                <p id="custom-alert-message" class="text-sm text-slate-400 mb-6 leading-relaxed relative z-10 font-medium"></p>

                <!-- Actions -->
                <div class="flex w-full gap-3 relative z-10">
                    <button id="custom-alert-cancel" type="button" class="flex-1 px-5 py-2.5 bg-slate-800 hover:bg-slate-700/80 text-slate-300 text-sm font-semibold rounded-xl cursor-pointer transition-all duration-200 text-center border border-slate-700/40">
                        Cancel
                    </button>
                    <button id="custom-alert-ok" type="button" class="flex-1 px-5 py-2.5 text-white text-sm font-semibold rounded-xl cursor-pointer transition-all duration-200 text-center shadow-lg">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    `;

    const div = document.createElement('div');
    div.innerHTML = modalHTML.trim();
    document.body.appendChild(div.firstChild);
}

// Global Custom Alert Function
function showCustomAlert(message, callback, options = {}) {
    createDialogContainer();
    const modal = document.getElementById('custom-alert-modal');
    const titleEl = document.getElementById('custom-alert-title');
    const messageEl = document.getElementById('custom-alert-message');
    const okBtn = document.getElementById('custom-alert-ok');
    const cancelBtn = document.getElementById('custom-alert-cancel');
    const iconContainer = document.getElementById('custom-alert-icon-container');
    const glowOrb = document.getElementById('custom-alert-glow');

    titleEl.textContent = options.title || 'Notification';
    messageEl.textContent = message;

    // Detect type based on message content or options
    let type = options.type || 'info';
    const lowerMsg = message.toLowerCase();
    if (!options.type) {
        if (lowerMsg.includes('error') || lowerMsg.includes('failed') || lowerMsg.includes('wrong') || lowerMsg.includes('invalid') || lowerMsg.includes('exception')) {
            type = 'error';
        } else if (lowerMsg.includes('success') || lowerMsg.includes('saved') || lowerMsg.includes('updated') || lowerMsg.includes('created') || lowerMsg.includes('sent')) {
            type = 'success';
        }
    }

    let iconHTML = '';
    let glowColor = '';
    let okBtnClass = '';

    if (type === 'success') {
        iconHTML = `<svg class="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>`;
        glowColor = 'bg-emerald-500/10';
        okBtnClass = 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/20';
    } else if (type === 'error' || type === 'danger') {
        iconHTML = `<svg class="w-8 h-8 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9 3.75h.008v.008H12v-.008z" /></svg>`;
        glowColor = 'bg-rose-500/10';
        okBtnClass = 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/20';
    } else if (type === 'warning') {
        iconHTML = `<svg class="w-8 h-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376C1.83 19.13 2.506 21 4.16 21h15.68c1.653 0 2.33-1.87 1.503-3.376L13.504 5.376c-.83-1.507-2.607-1.507-3.437 0L2.858 17.624Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 16h.008v.008H12V16Zm0-3h.008v.008H12V13Z" /></svg>`;
        glowColor = 'bg-amber-500/10';
        okBtnClass = 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/20';
    } else {
        iconHTML = `<svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 1 1 1.063.852l-.708 2.836a.75.75 0 001.063.852l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9-3.75h.008v.008H12V8.25z" /></svg>`;
        glowColor = 'bg-indigo-500/10';
        okBtnClass = 'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-600/20';
    }

    iconContainer.innerHTML = iconHTML;
    glowOrb.className = `absolute -top-24 -left-24 w-48 h-48 rounded-full blur-3xl pointer-events-none ${glowColor}`;

    // OK Button styling
    okBtn.textContent = options.confirmButtonText || 'OK';
    okBtn.className = `flex-1 px-5 py-2.5 text-white text-sm font-semibold rounded-xl cursor-pointer transition-all duration-200 text-center shadow-lg hover:shadow-none ${okBtnClass}`;
    
    // Hide cancel button
    cancelBtn.style.display = 'none';

    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const card = modal.querySelector('.dialog-card');
    card.classList.remove('dialog-scale-out');
    card.classList.add('dialog-scale-in');

    // Focus OK button for convenience
    setTimeout(() => okBtn.focus(), 50);

    const cleanUp = () => {
        card.classList.remove('dialog-scale-in');
        card.classList.add('dialog-scale-out');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.removeEventListener('keydown', keyHandler);
        }, 150);
    };

    const keyHandler = (e) => {
        if (e.key === 'Escape' || e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            cleanUp();
            if (callback) callback();
        }
    };

    document.addEventListener('keydown', keyHandler);

    okBtn.onclick = function() {
        cleanUp();
        if (callback) callback();
    };
}

// Global Custom Confirm Function
function showCustomConfirm(message, callback, options = {}) {
    createDialogContainer();
    const modal = document.getElementById('custom-alert-modal');
    const titleEl = document.getElementById('custom-alert-title');
    const messageEl = document.getElementById('custom-alert-message');
    const okBtn = document.getElementById('custom-alert-ok');
    const cancelBtn = document.getElementById('custom-alert-cancel');
    const iconContainer = document.getElementById('custom-alert-icon-container');
    const glowOrb = document.getElementById('custom-alert-glow');

    titleEl.textContent = options.title || 'Confirm Action';
    messageEl.textContent = message;

    // Detect type/danger based on keyword
    const lowerMsg = message.toLowerCase();
    const isDanger = lowerMsg.includes('delete') || lowerMsg.includes('remove') || lowerMsg.includes('destroy') || lowerMsg.includes('terminate');
    const type = options.type || (isDanger ? 'danger' : 'warning');

    let iconHTML = '';
    let glowColor = '';
    let okBtnClass = '';
    let okText = options.confirmButtonText || (isDanger ? 'Delete' : 'Confirm');
    let cancelText = options.cancelButtonText || 'Cancel';

    if (type === 'danger' || type === 'error') {
        iconHTML = `<svg class="w-8 h-8 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9 3.75h.008v.008H12v-.008z" /></svg>`;
        glowColor = 'bg-rose-500/10';
        okBtnClass = 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/20';
    } else {
        iconHTML = `<svg class="w-8 h-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376C1.83 19.13 2.506 21 4.16 21h15.68c1.653 0 2.33-1.87 1.503-3.376L13.504 5.376c-.83-1.507-2.607-1.507-3.437 0L2.858 17.624Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 16h.008v.008H12V16Zm0-3h.008v.008H12V13Z" /></svg>`;
        glowColor = 'bg-amber-500/10';
        okBtnClass = 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/20';
    }

    iconContainer.innerHTML = iconHTML;
    glowOrb.className = `absolute -top-24 -left-24 w-48 h-48 rounded-full blur-3xl pointer-events-none ${glowColor}`;

    // Apply button texts
    okBtn.textContent = okText;
    cancelBtn.textContent = cancelText;

    // Apply button styles
    okBtn.className = `flex-1 px-5 py-2.5 text-white text-sm font-semibold rounded-xl cursor-pointer transition-all duration-200 text-center shadow-lg hover:shadow-none ${okBtnClass}`;
    cancelBtn.style.display = 'block';

    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const card = modal.querySelector('.dialog-card');
    card.classList.remove('dialog-scale-out');
    card.classList.add('dialog-scale-in');

    // Focus Cancel button for safety if it's destructive, otherwise focus OK button
    setTimeout(() => {
        if (isDanger) {
            cancelBtn.focus();
        } else {
            okBtn.focus();
        }
    }, 50);

    let resolved = false;

    const resolveVal = (val) => {
        if (resolved) return;
        resolved = true;
        
        card.classList.remove('dialog-scale-in');
        card.classList.add('dialog-scale-out');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.removeEventListener('keydown', keyHandler);
            if (callback) callback(val);
        }, 150);
    };

    const keyHandler = (e) => {
        if (e.key === 'Escape') {
            e.preventDefault();
            resolveVal(false);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            resolveVal(true);
        }
    };

    document.addEventListener('keydown', keyHandler);

    okBtn.onclick = function() {
        resolveVal(true);
    };

    cancelBtn.onclick = function() {
        resolveVal(false);
    };
}

// Override standard alert/confirm globals
window.alert = function(message) {
    showCustomAlert(message);
};

window.confirm = function(message) {
    // Note: Since standard confirm is synchronous and custom modal is asynchronous, 
    // overriding window.confirm direct calls without Promise logic returns false.
    // However, our global submit interceptor handles inline confirmations asynchronously!
    console.warn("Synchronous confirm intercepted. Use window.confirmDialog instead.");
    return false;
};

// Expose modern promise-based versions globally
window.alertDialog = function(message, options = {}) {
    return new Promise((resolve) => {
        showCustomAlert(message, resolve, options);
    });
};

window.confirmDialog = function(message, options = {}) {
    return new Promise((resolve) => {
        showCustomConfirm(message, resolve, options);
    });
};

// Global Form Submit confirmation interceptor
document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Check if the form has been approved by our modal already
    if (form.dataset.confirmed === 'true') {
        // Allow submission and reset state
        delete form.dataset.confirmed;
        return;
    }

    // Check if the form has an onsubmit attribute containing confirm('...')
    const onsubmitAttr = form.getAttribute('onsubmit');
    if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
        // Prevent immediate submission
        e.preventDefault();
        e.stopPropagation();

        // Extract confirm message
        let message = 'Are you sure you want to perform this action?';
        const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
        if (match && match[1]) {
            message = match[1];
        }

        // Show the beautiful modal
        showCustomConfirm(message, function(confirmed) {
            if (confirmed) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
    }
}, true); // Capture phase is critical to run before the inline onsubmit handler!

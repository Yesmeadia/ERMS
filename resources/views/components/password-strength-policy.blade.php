{{--
    Password Strength Policy Component
    Automatically attaches to all "new password" inputs across the app.
    Excluded: current_password, password_confirmation, card CVV, and MFA fields.
--}}
<style>
    .psp-wrapper {
        margin-top: 8px;
    }
    .psp-bar-track {
        display: flex;
        gap: 4px;
        margin-bottom: 6px;
    }
    .psp-bar-seg {
        flex: 1;
        height: 4px;
        border-radius: 9999px;
        background: #334155;
        transition: background 0.3s ease;
    }
    .psp-bar-seg.active-weak    { background: #ef4444; }
    .psp-bar-seg.active-fair    { background: #f59e0b; }
    .psp-bar-seg.active-good    { background: #3b82f6; }
    .psp-bar-seg.active-strong  { background: #22c55e; }
    .psp-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .psp-label.lbl-weak   { color: #ef4444; }
    .psp-label.lbl-fair   { color: #f59e0b; }
    .psp-label.lbl-good   { color: #3b82f6; }
    .psp-label.lbl-strong { color: #22c55e; }
    .psp-criteria {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 12px;
        margin-bottom: 6px;
    }
    .psp-crit {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 10.5px;
        color: #64748b;
        transition: color 0.2s;
    }
    .psp-crit.met { color: #22c55e; }
    .psp-crit-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }
    .psp-suggest-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 4px;
        font-size: 11px;
        font-weight: 600;
        color: #818cf8;
        background: rgba(99,102,241,0.08);
        border: 1px solid rgba(99,102,241,0.2);
        border-radius: 8px;
        padding: 4px 10px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        user-select: none;
    }
    .psp-suggest-btn:hover {
        background: rgba(99,102,241,0.18);
        color: #a5b4fc;
    }
    .psp-suggestion-box {
        margin-top: 6px;
        padding: 7px 10px;
        background: rgba(15,23,42,0.7);
        border: 1px solid rgba(99,102,241,0.25);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .psp-suggestion-val {
        font-family: 'Courier New', monospace;
        font-size: 12.5px;
        color: #e2e8f0;
        letter-spacing: 0.04em;
        word-break: break-all;
    }
    .psp-copy-btn {
        flex-shrink: 0;
        font-size: 10px;
        font-weight: 700;
        color: #6366f1;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: color 0.2s;
    }
    .psp-copy-btn:hover { color: #a5b4fc; }
</style>

<script @nonce>
(function () {
    'use strict';

    // Names of password fields that should trigger the strength meter
    // (excludes current_password, password_confirmation, CVV, MFA fields)
    var NEW_PASS_NAMES = ['password', 'new_password', 'admin_password'];

    // Criteria definitions
    var CRITERIA = [
        { key: 'length',   label: '8+ chars',       test: function(v){ return v.length >= 8; } },
        { key: 'upper',    label: 'Uppercase',       test: function(v){ return /[A-Z]/.test(v); } },
        { key: 'lower',    label: 'Lowercase',       test: function(v){ return /[a-z]/.test(v); } },
        { key: 'number',   label: 'Number',          test: function(v){ return /[0-9]/.test(v); } },
        { key: 'special',  label: 'Special (!@#…)',  test: function(v){ return /[^A-Za-z0-9]/.test(v); } },
        { key: 'length12', label: '12+ chars',       test: function(v){ return v.length >= 12; } },
    ];

    function getScore(val) {
        return CRITERIA.filter(function(c){ return c.test(val); }).length;
    }

    function getLevel(score) {
        if (score <= 1) return 'weak';
        if (score <= 3) return 'fair';
        if (score <= 4) return 'good';
        return 'strong';
    }

    function getLevelIndex(level) {
        return { weak: 1, fair: 2, good: 3, strong: 4 }[level];
    }

    function getLevelClass(level) {
        return { weak: 'lbl-weak', fair: 'lbl-fair', good: 'lbl-good', strong: 'lbl-strong' }[level];
    }

    function getActiveSegClass(level) {
        return { weak: 'active-weak', fair: 'active-fair', good: 'active-good', strong: 'active-strong' }[level];
    }

    function getLevelLabel(level) {
        return {
            weak:   '\u26a0 Weak \u2013 please choose a stronger password',
            fair:   '\u26a1 Fair \u2013 add more variety',
            good:   '\u2713 Good',
            strong: '\u2713 Strong password'
        }[level];
    }

    function generateStrongPassword() {
        var upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        var lower   = 'abcdefghjkmnpqrstuvwxyz';
        var digits  = '23456789';
        var special = '!@#$%^&*_+=?';
        var all     = upper + lower + digits + special;

        var pass = [
            upper[Math.floor(Math.random() * upper.length)],
            upper[Math.floor(Math.random() * upper.length)],
            lower[Math.floor(Math.random() * lower.length)],
            lower[Math.floor(Math.random() * lower.length)],
            digits[Math.floor(Math.random() * digits.length)],
            digits[Math.floor(Math.random() * digits.length)],
            special[Math.floor(Math.random() * special.length)],
            special[Math.floor(Math.random() * special.length)],
        ];
        for (var i = 0; i < 6; i++) {
            pass.push(all[Math.floor(Math.random() * all.length)]);
        }
        // Fisher-Yates shuffle
        for (var j = pass.length - 1; j > 0; j--) {
            var k = Math.floor(Math.random() * (j + 1));
            var tmp = pass[j]; pass[j] = pass[k]; pass[k] = tmp;
        }
        return pass.join('');
    }

    function buildStrengthUI(input) {
        var wrapper = document.createElement('div');
        wrapper.className = 'psp-wrapper';

        // 4-segment bar
        var barTrack = document.createElement('div');
        barTrack.className = 'psp-bar-track';
        var segs = [];
        for (var s = 0; s < 4; s++) {
            var seg = document.createElement('div');
            seg.className = 'psp-bar-seg';
            barTrack.appendChild(seg);
            segs.push(seg);
        }

        // Label
        var label = document.createElement('div');
        label.className = 'psp-label';

        // Criteria list
        var criteriaDiv = document.createElement('div');
        criteriaDiv.className = 'psp-criteria';
        var critEls = {};
        CRITERIA.forEach(function(c) {
            var el = document.createElement('span');
            el.className = 'psp-crit';
            el.innerHTML = '<span class="psp-crit-dot"></span>' + c.label;
            criteriaDiv.appendChild(el);
            critEls[c.key] = el;
        });

        // Suggest button
        var suggestBtn = document.createElement('button');
        suggestBtn.type = 'button';
        suggestBtn.className = 'psp-suggest-btn';
        suggestBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>Suggest strong password';

        // Suggestion box
        var suggBox = document.createElement('div');
        suggBox.className = 'psp-suggestion-box';
        suggBox.style.display = 'none';

        var suggVal = document.createElement('span');
        suggVal.className = 'psp-suggestion-val';

        var copyBtn = document.createElement('button');
        copyBtn.type = 'button';
        copyBtn.className = 'psp-copy-btn';
        copyBtn.textContent = 'Use this';

        suggBox.appendChild(suggVal);
        suggBox.appendChild(copyBtn);

        wrapper.appendChild(barTrack);
        wrapper.appendChild(label);
        wrapper.appendChild(criteriaDiv);
        wrapper.appendChild(suggestBtn);
        wrapper.appendChild(suggBox);

        function update(val) {
            var score    = getScore(val);
            var level    = getLevel(score);
            var levelIdx = getLevelIndex(level);
            var segClass = getActiveSegClass(level);

            segs.forEach(function(seg, i) {
                seg.className = 'psp-bar-seg';
                if (i < levelIdx) seg.classList.add(segClass);
            });

            if (val.length === 0) {
                label.textContent = '';
                label.className = 'psp-label';
                criteriaDiv.style.display = 'none';
                suggestBtn.style.display = 'none';
                suggBox.style.display = 'none';
            } else {
                criteriaDiv.style.display = 'flex';
                var isWeak = (level === 'weak' || level === 'fair');
                suggestBtn.style.display = isWeak ? 'inline-flex' : 'none';
                if (!isWeak) suggBox.style.display = 'none';

                label.textContent = getLevelLabel(level);
                label.className = 'psp-label ' + getLevelClass(level);

                CRITERIA.forEach(function(c) {
                    critEls[c.key].className = 'psp-crit' + (c.test(val) ? ' met' : '');
                });
            }
        }

        criteriaDiv.style.display = 'none';
        suggestBtn.style.display = 'none';

        input.addEventListener('input', function() { update(input.value); });

        suggestBtn.addEventListener('click', function() {
            var pass = generateStrongPassword();
            suggVal.textContent = pass;
            suggBox.style.display = 'flex';
        });

        copyBtn.addEventListener('click', function() {
            var pass = suggVal.textContent;
            input.value = pass;

            // Temporarily show the password
            var origType = input.getAttribute('type');
            input.setAttribute('type', 'text');
            setTimeout(function() { input.setAttribute('type', 'password'); }, 1500);

            // Fill confirmation field if present in the same form
            var form = input.closest('form');
            if (form) {
                var confirmField = form.querySelector(
                    'input[name="password_confirmation"], input[name="new_password_confirmation"], input[name="admin_password_confirmation"]'
                );
                if (confirmField) confirmField.value = pass;
            }

            copyBtn.textContent = 'Copied!';
            setTimeout(function() { copyBtn.textContent = 'Use this'; }, 2000);
            update(pass);
            suggBox.style.display = 'none';
            suggestBtn.style.display = 'none';
        });

        return wrapper;
    }

    function attachToInputs() {
        NEW_PASS_NAMES.forEach(function(name) {
            var selector = 'input[type="password"][name="' + name + '"]';
            document.querySelectorAll(selector).forEach(function(input) {
                if (input.dataset.pspAttached) return;
                // Skip CVV and MFA-style fields that use Alpine x-model
                if (input.hasAttribute('x-model')) return;

                input.dataset.pspAttached = 'true';

                var ui = buildStrengthUI(input);
                var parent = input.parentElement;
                if (parent) {
                    parent.insertAdjacentElement('afterend', ui);
                } else {
                    input.insertAdjacentElement('afterend', ui);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachToInputs);
    } else {
        attachToInputs();
    }

    // Handle dynamically-injected inputs (modals, etc.)
    var observer = new MutationObserver(function() { attachToInputs(); });
    observer.observe(document.body, { childList: true, subtree: true });
})();
</script>

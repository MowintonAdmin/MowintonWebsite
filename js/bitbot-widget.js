/**
 * BitBot Chat Widget
 *
 * Frontend chat widget for visitor interactions.
 *
 * @package BitBot
 * @since   1.0.0
 * @author  youami.ai
 * @license GPL-2.0-or-later
 */
(function() {
    'use strict';

    // Configuration from WordPress
    const config = window.bitbotConfig || {};

    // DOM Elements
    // `widget` is the light-DOM host (#bitbot-widget) — holds the position
    // class, CSS variables, and data-* attributes read by auto-flip.
    // `widgetRoot` is the shadow root attached to the host on init — every
    // bitbot-internal element lives inside it, so all queries for them
    // scope through widgetRoot.getElementById(...) instead of document.
    let widget, widgetRoot, toggle, chat, messages, form, input;
    let chatHistory = [];
    let isOpen = false;
    let isLoading = false;
    let sessionId = null;
    let leadCapturedEmail = null; // Track if a lead was already captured this session
    // Live-handover mode (1.7.0): when the agent escalates to a human, the
    // widget stops talking to the agent and polls for admin replies instead.
    let liveMode = false;
    let liveLastMessageId = null;
    let livePollTimer = null;

    // Visitor presence (1.7.0): while the page is visible we send a
    // lightweight heartbeat every 30s so the admin's Live Chats dashboard
    // can render "present / idle / left" for this session. On tab close
    // we fire a sendBeacon so the admin sees the visitor leave immediately.
    let heartbeatTimer = null;
    const HEARTBEAT_INTERVAL_MS = 30000;

    // Per-tab chat persistence (1.5.3): sessionStorage so the conversation
    // survives navigation within the same tab, wipes on tab close.
    const CHAT_STORAGE_KEY = 'bitbot_chat_state';

    // Local persistence cap — stops sessionStorage growth on marathon chats.
    // Unrelated to the per-request send cap below.
    const CHAT_LOCAL_MAX = 80;
    // How many prior messages we send to the backend on each chat request.
    // The Pro+ agent also reads ground-truth tool results from its own
    // server-side trace, so we don't need the full transcript for facts —
    // just enough recent turns for conversational flow. Bumped from 10 to
    // 20 so a 4–5-turn OTP-add-to-cart doesn't lose its earlier context.
    const CHAT_SEND_MAX = 20;

    function saveChatState() {
        try {
            // Cap the local transcript so marathon sessions don't blow
            // sessionStorage. The agent gets its long-horizon memory from
            // the server-side tool_trace, not this client cache.
            if (chatHistory.length > CHAT_LOCAL_MAX) {
                chatHistory = chatHistory.slice(-CHAT_LOCAL_MAX);
            }
            sessionStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify({
                sessionId: sessionId,
                history: chatHistory,
                leadCapturedEmail: leadCapturedEmail,
                isOpen: isOpen,
                liveMode: liveMode,
                liveLastMessageId: liveLastMessageId,
            }));
        } catch (e) {
            // Storage quota or privacy mode — not fatal.
        }
    }

    function loadChatState() {
        try {
            const raw = sessionStorage.getItem(CHAT_STORAGE_KEY);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    /**
     * Visitor's IANA timezone (e.g. "Europe/London") from the browser. The
     * Pro+ agent uses this to format appointment labels in local time.
     * Returns empty string if unavailable; backend falls back to site tz.
     *
     * @since 1.7.0
     */
    function getVisitorTimezone() {
        try {
            return Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {
            return '';
        }
    }

    /**
     * Persistent per-browser UUID so the Pro+ agent can recognise returning
     * visitors across sessions. Unlike sessionId (per-tab-life), this lives
     * in localStorage and survives tab close / navigation. Fully opaque —
     * no PII, no server access beyond our own API.
     *
     * @since 1.7.0
     */
    function getVisitorId() {
        var key = 'bitbot_visitor_id';
        try {
            var existing = localStorage.getItem(key);
            if (existing && existing.length > 10) return existing;
        } catch (e) { /* storage disabled */ }

        var uuid;
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            uuid = crypto.randomUUID();
        } else {
            // RFC4122-ish fallback for older browsers.
            uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0;
                var v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
        try { localStorage.setItem(key, uuid); } catch (e) { /* swallow */ }
        return uuid;
    }

    /**
     * Generate or retrieve session ID
     */
    function getSessionId() {
        if (sessionId) return sessionId;

        // Try to get from localStorage
        const storageKey = 'bitbot_session_id';
        try {
            sessionId = localStorage.getItem(storageKey);

            if (!sessionId) {
                // Generate new UUID
                sessionId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    const r = Math.random() * 16 | 0;
                    const v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
                localStorage.setItem(storageKey, sessionId);
            }
        } catch (e) {
            // localStorage not available, generate temporary ID
            sessionId = 'temp-' + Date.now();
        }

        return sessionId;
    }

    /**
     * Detect a fixed-position widget (accessibility tools, other chat
     * widgets, "back to top" buttons) overlapping our planned corner.
     *
     * Strategy:
     *   1. Try known signatures first — UserWay, AccessiBe, EqualWeb,
     *      Tawk.to, Crisp, Intercom, Drift, HubSpot, Drift, Olark, Kommo,
     *      ChatBot, etc. Cheap and reliable.
     *   2. Fall back to a geometric scan: any fixed-position element with
     *      z-index >= 100 whose bounding box intersects our 80×80 corner
     *      box, ignoring elements smaller than 30px (purely decorative).
     *
     * Runs twice — once on init, once after a 1500ms delay — because a
     * lot of these widgets inject themselves async after page load.
     *
     * @param {string} corner 'right' or 'left' — the corner we're checking.
     * @returns {boolean} true if the corner is occupied.
     */
    function isCornerOccupied(corner) {
        var KNOWN_SELECTORS = [
            // UserWay accessibility widget
            '#userwayAccessibilityIcon', '.userway_buttons_wrapper', '#userwayDebug',
            // AccessiBe / accessiBe Web Accessibility
            '#acsbBtn', '.acsb-trigger', '#acsb-trigger', '[data-acsb]',
            // EqualWeb
            '#INDmenu-btn', '.INDmenu-btn',
            // Tawk.to
            '.tawk-min-container', '#tawkchat-container', '#tawkchat-minified-wrapper',
            // Crisp
            '.crisp-client',
            // Intercom
            '.intercom-launcher', '.intercom-lightweight-app-launcher',
            // Drift
            '#drift-frame-controller', '.drift-frame-controller',
            // HubSpot chat
            '#hubspot-messages-iframe-container',
            // Olark
            '#olark-box-front',
            // Kommo / amoCRM chat
            '#amo-chat-button',
            // ChatBot.com / LiveChat
            '#chat-widget-container', '#chat-widget',
            // Generic "scroll-to-top" buttons / back-to-top plugins
            '#scrollUp', '.scroll-to-top', '.back-to-top'
        ];
        for (var i = 0; i < KNOWN_SELECTORS.length; i++) {
            var el = document.querySelector(KNOWN_SELECTORS[i]);
            if (!el) continue;
            var r = el.getBoundingClientRect();
            if (r.width === 0 || r.height === 0) continue;
            var cs = getComputedStyle(el);
            if (cs.display === 'none' || cs.visibility === 'hidden') continue;
            // Only count it if it's actually pinned to the bottom of the viewport.
            if (r.bottom < window.innerHeight - 200) continue;
            // Match the side
            if (corner === 'right' && r.right > window.innerWidth - 120) return true;
            if (corner === 'left' && r.left < 120) return true;
        }
        // Geometric fallback — scan ALL fixed elements for ones in the corner.
        var all = document.querySelectorAll('body *');
        for (var j = 0; j < all.length; j++) {
            var node = all[j];
            if (node.id && node.id.indexOf('bitbot') === 0) continue; // ourselves
            var c = getComputedStyle(node);
            if (c.position !== 'fixed') continue;
            if (c.display === 'none' || c.visibility === 'hidden') continue;
            // Invisible-but-mounted overlays (e.g. WooCommerce mini-cart's
            // screen-overlay sits at opacity:0, pointer-events:none until
            // the cart is opened) shouldn't count as a real occupant —
            // we'd otherwise flag both corners as busy and stick with
            // the user's preference, defeating the whole point of the
            // collision check. Same logic for pointer-events:none alone:
            // if you can't click it, it's decorative.
            if (parseFloat(c.opacity) === 0) continue;
            if (c.pointerEvents === 'none') continue;
            var z = parseInt(c.zIndex, 10);
            if (!isNaN(z) && z < 100) continue;
            var b = node.getBoundingClientRect();
            if (b.width < 30 || b.height < 30) continue;
            // Anything wider than 60% of the viewport is a sheet / modal /
            // toast container, not a corner widget. It "occupies" both
            // corners by accident; counting it as an occupant breaks the
            // flip logic. Same height check catches full-screen overlays.
            if (b.width > window.innerWidth * 0.6) continue;
            if (b.height > window.innerHeight * 0.6) continue;
            if (b.bottom < window.innerHeight - 200) continue;
            if (corner === 'right' && b.right > window.innerWidth - 120) return true;
            if (corner === 'left' && b.left < 120) return true;
        }
        return false;
    }

    /**
     * Set widget position class. Strips any prior position class first.
     */
    function setWidgetPosition(corner) {
        if (!widget) return;
        widget.classList.remove('bitbot-bottom-right', 'bitbot-bottom-left');
        widget.classList.add(corner === 'left' ? 'bitbot-bottom-left' : 'bitbot-bottom-right');
    }

    /**
     * Pick the best corner. Respects the site owner's configured preference
     * unless that corner is occupied — then flips to the other corner if
     * THAT one is free. If both are occupied, sticks with the configured
     * preference (we'd rather overlap a third-party widget than be hidden).
     */
    /**
     * Find the topmost (smallest .top) fixed widget already in our
     * target corner so we can stack on top of it. Skips the same
     * candidates that isCornerOccupied filters out (invisible
     * overlays, full-viewport sheets) because those wouldn't be a
     * "real" collision to stack against either.
     *
     * Returns the bounding rect of the matching element, or null if
     * nothing real is in the corner.
     */
    function findCornerOccupant(corner) {
        var candidates = document.querySelectorAll('body *');
        var topmost = null;
        for (var i = 0; i < candidates.length; i++) {
            var node = candidates[i];
            if (node.id && node.id.indexOf('bitbot') === 0) continue;
            var c = getComputedStyle(node);
            if (c.position !== 'fixed') continue;
            if (c.display === 'none' || c.visibility === 'hidden') continue;
            if (parseFloat(c.opacity) === 0) continue;
            if (c.pointerEvents === 'none') continue;
            var z = parseInt(c.zIndex, 10);
            if (!isNaN(z) && z < 100) continue;
            var b = node.getBoundingClientRect();
            if (b.width < 30 || b.height < 30) continue;
            if (b.width > window.innerWidth * 0.6) continue;
            if (b.height > window.innerHeight * 0.6) continue;
            if (b.bottom < window.innerHeight - 200) continue;
            if (corner === 'right' && b.right <= window.innerWidth - 120) continue;
            if (corner === 'left' && b.left >= 120) continue;
            // Prefer the one closest to the bottom of the viewport
            // (largest .bottom) so we stack above THAT one's height.
            if (!topmost || b.bottom > topmost.bottom) {
                topmost = b;
            }
        }
        return topmost;
    }

    /**
     * Override the bottom offset on the widget so it stacks on top of
     * the corner occupant: occupant.height + 16px gap. Stored in the
     * runtime --bitbot-offset-y custom property; doesn't touch the
     * saved option.
     */
    function applyStackedOffset(occupantRect) {
        if (!widget || !occupantRect) return;
        // distance from viewport bottom to top of occupant + 16px gap
        var clearance = (window.innerHeight - occupantRect.top) + 16;
        widget.style.setProperty('--bitbot-offset-y', clearance + 'px');
    }

    /**
     * Restore the owner's configured vertical offset (set on
     * data-base-offset-y by PHP). Called when we successfully move to
     * a free corner so we don't carry a stacked offset across flips.
     */
    function restoreBaseOffset() {
        if (!widget) return;
        var base = parseInt(widget.getAttribute('data-base-offset-y') || '20', 10);
        widget.style.setProperty('--bitbot-offset-y', base + 'px');
    }

    function autoPositionWidget() {
        if (!widget) return;
        // Read the OWNER'S configured preference from the data attribute,
        // not the current class. The class can flip across passes (init
        // + 1.5s) and inherits from this same function -- if we read the
        // class as "preferred", a first-pass flip locks in the new
        // corner as the new "preferred" and we can never restore the
        // owner's actual choice when the original corner becomes free
        // again. data-preferred is set once by PHP from settings.
        var preferred = widget.getAttribute('data-preferred') === 'left' ? 'left' : 'right';
        var preferredOccupied = isCornerOccupied(preferred);
        if (!preferredOccupied) {
            // Configured corner is clear → restore it + clear any stacked offset.
            setWidgetPosition(preferred);
            restoreBaseOffset();
            return;
        }
        var alt = preferred === 'right' ? 'left' : 'right';
        if (!isCornerOccupied(alt)) {
            setWidgetPosition(alt);
            restoreBaseOffset();
            return;
        }
        // Both occupied → instead of overlapping, stack on top of
        // whatever's in the owner's preferred corner. Bumps the
        // vertical offset by occupant.height + 16px gap so the two
        // widgets sit one above the other.
        setWidgetPosition(preferred);
        var occupant = findCornerOccupant(preferred);
        if (occupant) {
            applyStackedOffset(occupant);
        } else {
            restoreBaseOffset();
        }
    }

    /**
     * Initialize the widget
     */
    function init() {
        widget = document.getElementById('bitbot-widget');
        if (!widget) {
            return;
        }

        // If a prior chat in this tab session already discovered the
        // bot can't reply right now (trial expired OR Free chat cap
        // hit), don't render the bubble on subsequent pageloads.
        // Keeps visitors from seeing flicker-then-disappear behaviour.
        // 2.0.119: also covers Free monthly chat cap (was previously
        // deflecting to "leave us your email" which we couldn't honour
        // on Free since admin can't see lead rows).
        // Server-authoritative pause. The plugin computes whether the bot can
        // reply right now (expired sub or Free monthly chat cap) from a cached,
        // throttle-refreshed plan snapshot and sends it as config.accessPaused
        // (fetched fresh by the bootstrap before init runs). Honour it so we
        // never render a FAB that clicks into a dead chat — and the widget
        // reappears on its own once the cap resets / the site upgrades, with
        // no client-side flag or timer.
        if (config.accessPaused) {
            widget.style.display = 'none';
            return;
        }
        // Clear any pre-2.1.9 sticky flag so sessions stuck under the old
        // client-flag behaviour recover (the server value is authoritative now).
        try {
            sessionStorage.removeItem('bitbot-access-paused');
            sessionStorage.removeItem('bitbot-trial-expired');
        } catch (e) { /* sessionStorage unavailable */ }

        // 2.0.12 widget-seen heartbeat. Single fire-and-forget POST per
        // browser session so the WP backend can stamp the wp_option the
        // admin Overview banner reads. Lets us detect themes that skip
        // wp_footer() — the enqueue ran, but the visitor's browser
        // never executed widget.js, so we never ping back. Throttled
        // via sessionStorage so even chatty single-page-app navigation
        // only pings once.
        try {
            if (sessionStorage.getItem('bitbot-seen-pinged') !== '1') {
                var origin = (window.bitbotConfig && window.bitbotConfig.restUrl)
                    ? new URL(window.bitbotConfig.restUrl).origin
                    : window.location.origin;
                var ajaxUrl = origin + '/wp-admin/admin-ajax.php';
                var body = new URLSearchParams();
                body.append('action', 'bitbot_widget_seen');
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: body,
                    credentials: 'same-origin',
                    keepalive: true
                }).catch(function () { /* swallow — best-effort */ });
                sessionStorage.setItem('bitbot-seen-pinged', '1');
            }
        } catch (e) { /* sessionStorage unavailable, skip ping */ }

        // Attach a shadow root on the host and mount the template into it.
        // Theme CSS rules on bare `button`, `svg`, `input`, etc. cannot
        // cross the shadow boundary, so the widget renders exactly as
        // designed regardless of which theme the site uses.
        if (widget.shadowRoot) {
            widgetRoot = widget.shadowRoot;
        } else if (typeof widget.attachShadow === 'function') {
            widgetRoot = widget.attachShadow({ mode: 'open' });

            // First child: the stylesheet. Browsers block style application
            // on its descendants until this link resolves, which prevents a
            // flash of unstyled widget on first paint.
            if (config.cssUrl) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = config.cssUrl;
                widgetRoot.appendChild(link);
            }

            // Clone the template content into the shadow root.
            const tpl = document.getElementById('bitbot-widget-template');
            if (tpl && 'content' in tpl) {
                widgetRoot.appendChild(tpl.content.cloneNode(true));
            }
        } else {
            // Shadow DOM unsupported (very old browser): fall back to
            // mounting the template directly in the host. The widget will
            // be vulnerable to theme CSS bleed but at least functional.
            const tpl = document.getElementById('bitbot-widget-template');
            if (tpl && 'content' in tpl) {
                widget.appendChild(tpl.content.cloneNode(true));
            }
            widgetRoot = widget;
        }

        toggle = widgetRoot.getElementById('bitbot-toggle');
        chat = widgetRoot.getElementById('bitbot-chat');
        messages = widgetRoot.getElementById('bitbot-messages');
        form = widgetRoot.getElementById('bitbot-form');
        input = widgetRoot.getElementById('bitbot-input');

        if (!toggle || !chat) {
            return;
        }

        // Citation-click beacon. Delegation so we catch any future
        // .bitbot-citation-link nodes inserted into `messages` without
        // re-binding. The beacon is best-effort — we never block the
        // visitor's actual navigation; the click still opens the link
        // because the listener doesn't preventDefault.
        messages.addEventListener('click', function (ev) {
            var target = ev.target;
            // Walk up the tree in case the click landed on a child node
            // (browsers can hit a text node, especially with text-only
            // links).
            while (target && target !== messages && !(target.classList && target.classList.contains('bitbot-citation-link'))) {
                target = target.parentNode;
            }
            if (!target || target === messages) return;
            var url = target.getAttribute('href') || '';
            var title = (target.textContent || '').trim();
            if (!url) return;
            try {
                var body = JSON.stringify({
                    sessionId: getSessionId(),
                    url: url,
                    title: title,
                });
                if (navigator.sendBeacon) {
                    navigator.sendBeacon(
                        config.restUrl + '/citation-click',
                        new Blob([body], { type: 'application/json' })
                    );
                } else {
                    // Fallback: keepalive fetch survives tab close.
                    fetch(config.restUrl + '/citation-click', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: body,
                        keepalive: true,
                    }).catch(function () { /* swallow */ });
                }
            } catch (e) { /* swallow — never block the click */ }
        });

        // 1.9.0: mobile-behaviour override (8k). Stamp a data attribute on
        // the host so the widget CSS @media block can flip position or
        // hide entirely below the mobile breakpoint. CSS does the heavy
        // lifting; this just communicates intent.
        const mobileBehaviour = (config.mobileBehaviour || 'inherit').toLowerCase();
        const allowedMobileBehaviour = new Set(['inherit', 'bottom-right', 'bottom-left', 'hide']);
        const host = document.getElementById('bitbot-widget');
        if (host && allowedMobileBehaviour.has(mobileBehaviour) && mobileBehaviour !== 'inherit') {
            host.setAttribute('data-mobile-behaviour', mobileBehaviour);
        }

        // 1.9.0: custom avatar (8d). Replace the default FAB chat icon
        // with the admin's image when configured. The chat-header slot
        // also receives a small circular crop of the same image.
        //
        // 2.0.2 (refactor): when a saved crop exists for the CURRENT FAB
        // shape, render via background-image with computed bg-size +
        // bg-position so the cropped region fills the FAB exactly —
        // no brand-colour bleed regardless of pan/zoom. Fall back to
        // <img object-fit:cover> when no transform / shape mismatch.
        const avatarUrl = (config.avatarUrl || '').trim();
        const avatarTx  = config.avatarTransform || null;
        const widgetHost = document.getElementById('bitbot-widget');
        const currentShape = widgetHost && widgetHost.getAttribute('data-shape') === 'pill'
            ? 'pill'
            : 'rounded';

        // Build a CSS background spec from a saved transform for the
        // given target container size. Returns null when the transform
        // is missing, shape-mismatched, or malformed.
        function bgFromTransform(tx, targetShape, containerW, containerH) {
            if (!tx || !tx.cropW || !tx.cropH || !tx.sourceW || !tx.sourceH) return null;
            if (tx.shape && tx.shape !== targetShape) return null;
            var scaleX = containerW / tx.cropW;
            var scaleY = containerH / tx.cropH;
            return {
                size: (tx.sourceW * scaleX) + 'px ' + (tx.sourceH * scaleY) + 'px',
                position: (-tx.cropX * scaleX) + 'px ' + (-tx.cropY * scaleY) + 'px'
            };
        }

        if (avatarUrl) {
            // ── FAB launcher ──
            // Real widget FAB: rounded = 56×56, pill = 112×28.
            var fabW = currentShape === 'pill' ? 112 : 56;
            var fabH = currentShape === 'pill' ?  28 : 56;
            var fabBg = bgFromTransform(avatarTx, currentShape, fabW, fabH);
            var chatIcon = widgetRoot.querySelector('.bitbot-icon-chat');
            if (chatIcon && chatIcon.parentNode) {
                var fabAvatar;
                if (fabBg) {
                    // bg-image path
                    fabAvatar = document.createElement('span');
                    fabAvatar.className = 'bitbot-icon-chat bitbot-avatar-img';
                    fabAvatar.style.display = 'block';
                    fabAvatar.style.width = '100%';
                    fabAvatar.style.height = '100%';
                    fabAvatar.style.borderRadius = 'inherit';
                    fabAvatar.style.backgroundImage = 'url(' + avatarUrl + ')';
                    fabAvatar.style.backgroundSize = fabBg.size;
                    fabAvatar.style.backgroundPosition = fabBg.position;
                    fabAvatar.style.backgroundRepeat = 'no-repeat';
                } else {
                    // Fallback to plain <img> with object-fit cover.
                    fabAvatar = document.createElement('img');
                    fabAvatar.src = avatarUrl;
                    fabAvatar.alt = '';
                    fabAvatar.className = 'bitbot-icon-chat bitbot-avatar-img';
                    fabAvatar.style.width = '100%';
                    fabAvatar.style.height = '100%';
                    fabAvatar.style.objectFit = 'cover';
                    fabAvatar.style.borderRadius = 'inherit';
                }
                chatIcon.replaceWith(fabAvatar);
            }

            // ── Chat-header avatar slot ──
            // Header avatar is a 36×36 circle in widget.css. Only
            // 'rounded' crops apply here; pill crops fall back to cover.
            var headerBg = bgFromTransform(avatarTx, 'rounded', 36, 36);
            var headerSlot = widgetRoot.getElementById('bitbot-header-avatar');
            if (headerSlot) {
                var hAvatar;
                if (headerBg) {
                    hAvatar = document.createElement('span');
                    hAvatar.className = 'bitbot-header-avatar-img';
                    hAvatar.style.display = 'block';
                    hAvatar.style.width = '100%';
                    hAvatar.style.height = '100%';
                    hAvatar.style.backgroundImage = 'url(' + avatarUrl + ')';
                    hAvatar.style.backgroundSize = headerBg.size;
                    hAvatar.style.backgroundPosition = headerBg.position;
                    hAvatar.style.backgroundRepeat = 'no-repeat';
                } else {
                    hAvatar = document.createElement('img');
                    hAvatar.src = avatarUrl;
                    hAvatar.alt = '';
                    hAvatar.className = 'bitbot-header-avatar-img';
                }
                headerSlot.appendChild(hAvatar);
            }
        }

        // 1.9.0: proactive nudge (8f). Pro feature. Three modes:
        //   - simple: one static line
        //   - page-aware: URL-pattern → line lookup
        //   - rag: ask backend for a contextual line (Pro+; widget falls
        //     back to first page-aware rule or simpleText if backend errors)
        // One-shot per session; sessionStorage 'bitbot-nudge-shown'='1'.
        //
        // 2.0.3: actually implement "inactivity" — the admin label promises
        // "after N seconds of inactivity" but the original code was a flat
        // setTimeout that fired regardless of what the visitor did, including
        // opening the chat first. Now: reset the timer on user activity
        // (mousemove, scroll, keydown, touch) and cancel it entirely once
        // the chat opens. If the chat is already open at fire-time we skip.
        (function attachNudge() {
            const nudge = config.nudge;
            if (!nudge || !nudge.enabled) return;
            try {
                if (sessionStorage.getItem('bitbot-nudge-shown') === '1') return;
            } catch (e) {}

            const delayMs = (nudge.delaySeconds || 8) * 1000;
            let timer = null;
            let cancelled = false;
            let currentBubble = null;
            let chatObserver = null;

            function pickText() {
                if (nudge.mode === 'page-aware' && nudge.pageRules) {
                    const path = window.location.pathname || '/';
                    const rules = nudge.pageRules.split(/\r?\n/);
                    for (let i = 0; i < rules.length; i++) {
                        const parts = rules[i].split('|');
                        if (parts.length < 2) continue;
                        const pat = parts[0].trim();
                        const txt = parts.slice(1).join('|').trim();
                        if (!pat || !txt) continue;
                        const regex = new RegExp('^' + pat.replace(/[.+?^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*') + '$', 'i');
                        if (regex.test(path) || path.indexOf(pat) !== -1) return txt;
                    }
                }
                // simple OR rag-fallback OR page-aware-no-match
                return nudge.simpleText || '';
            }

            function showBubble(text) {
                if (!text || !toggle) return;
                try { sessionStorage.setItem('bitbot-nudge-shown', '1'); } catch (e) {}
                const bubble = document.createElement('div');
                bubble.className = 'bitbot-nudge-bubble';
                bubble.setAttribute('role', 'status');
                bubble.textContent = text;
                const close = document.createElement('button');
                close.type = 'button';
                close.className = 'bitbot-nudge-close';
                close.setAttribute('aria-label', 'Dismiss');
                close.textContent = '×';
                close.addEventListener('click', function(e) {
                    e.stopPropagation();
                    bubble.remove();
                });
                bubble.addEventListener('click', function() {
                    bubble.remove();
                    if (chat && chat.style.display === 'none') toggleChat();
                });
                bubble.appendChild(close);
                // 2.0.2: insert INSIDE the shadow root, not the light DOM.
                // The previous "light DOM so the theme doesn't see inside
                // the shadow root" comment had the encapsulation direction
                // backwards — shadow DOM hides its *contents* from the
                // page, not the other way around. widget.css lives only
                // inside the shadow root (loaded via cssUrl by widget.js)
                // so a bubble in the light DOM gets none of the
                // .bitbot-nudge-bubble styles and renders as a 0×0 element.
                // Matches how the pinned banner already does it.
                if (widgetRoot) widgetRoot.appendChild(bubble);
                currentBubble = bubble;
            }

            function fire() {
                if (cancelled) return;
                // Chat already open? Treat as engaged — don't interrupt.
                if (chat && chat.style.display !== 'none') {
                    cancel(true);
                    return;
                }
                const text = pickText();
                if (text) showBubble(text);
                // Don't tear down listeners yet — keep watching the chat
                // so the bubble disappears if the visitor opens the chat
                // (via toggle, auto-open, or any programmatic path).
            }

            function schedule() {
                if (cancelled) return;
                if (timer) clearTimeout(timer);
                timer = setTimeout(fire, delayMs);
            }

            function cancel(removeBubble) {
                cancelled = true;
                if (timer) { clearTimeout(timer); timer = null; }
                document.removeEventListener('mousemove', schedule, true);
                document.removeEventListener('scroll', schedule, true);
                document.removeEventListener('keydown', schedule, true);
                document.removeEventListener('touchstart', schedule, true);
                if (chatObserver) { chatObserver.disconnect(); chatObserver = null; }
                if (removeBubble && currentBubble) {
                    currentBubble.remove();
                    currentBubble = null;
                }
            }

            // Reset the countdown on any user activity — that's what
            // "inactivity" actually means.
            document.addEventListener('mousemove', schedule, { capture: true, passive: true });
            document.addEventListener('scroll',    schedule, { capture: true, passive: true });
            document.addEventListener('keydown',   schedule, { capture: true });
            document.addEventListener('touchstart', schedule, { capture: true, passive: true });

            // Watch chat-panel display so we cancel/tear-down on ANY way
            // the chat opens — toggle click, auto-open delay/scroll/exit,
            // restored from session storage, programmatic toggleChat from
            // pre-chat form, etc. Pure click-listener wouldn't catch the
            // programmatic openings.
            if (chat && typeof MutationObserver === 'function') {
                chatObserver = new MutationObserver(function() {
                    if (chat.style.display !== 'none') cancel(true);
                });
                chatObserver.observe(chat, { attributes: true, attributeFilter: ['style'] });
            }

            schedule();
        })();

        // 1.9.0: auto-open trigger (8g). Pro feature; opens the chat
        // panel automatically once per session based on admin-configured
        // triggers. Storage: sessionStorage 'bitbot-auto-opened' = '1'
        // so reload-spam doesn't re-trigger.
        (function attachAutoOpen() {
            const ao = config.autoOpen;
            if (!ao) return;
            let alreadyTriggered = false;
            try {
                if (sessionStorage.getItem('bitbot-auto-opened') === '1') return;
            } catch (e) { /* sessionStorage unavailable */ }

            function trigger(reason) {
                if (alreadyTriggered) return;
                alreadyTriggered = true;
                try { sessionStorage.setItem('bitbot-auto-opened', '1'); } catch (e) {}
                // Open the chat panel via the existing toggle handler.
                if (chat && chat.style.display === 'none') {
                    toggleChat();
                }
            }

            // URL pattern match.
            if (ao.urlPatterns) {
                const path = window.location.pathname || '/';
                const url  = window.location.href;
                const patterns = ao.urlPatterns.split(/\r?\n/).map(function(p){ return p.trim(); }).filter(Boolean);
                for (let i = 0; i < patterns.length; i++) {
                    const pat = patterns[i];
                    const regex = new RegExp('^' + pat.replace(/[.+?^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*') + '$', 'i');
                    if (regex.test(path) || regex.test(url) || path.indexOf(pat) !== -1) {
                        trigger('url');
                        return;
                    }
                }
            }

            // Time-on-page delay.
            if (ao.delaySeconds > 0) {
                setTimeout(function(){ trigger('delay'); }, ao.delaySeconds * 1000);
            }

            // Scroll-percent trigger.
            if (ao.scrollPercent > 0) {
                const threshold = ao.scrollPercent;
                const onScroll = function() {
                    const doc = document.documentElement;
                    const scrolled = (doc.scrollTop || document.body.scrollTop);
                    const max = (doc.scrollHeight - doc.clientHeight) || 1;
                    if ((scrolled / max) * 100 >= threshold) {
                        trigger('scroll');
                        window.removeEventListener('scroll', onScroll);
                    }
                };
                window.addEventListener('scroll', onScroll, { passive: true });
            }

            // Exit-intent (cursor moving toward browser chrome — top y < 0).
            if (ao.exitIntent) {
                const onMouseOut = function(e) {
                    if (!e.toElement && !e.relatedTarget && e.clientY < 10) {
                        trigger('exit');
                        document.removeEventListener('mouseout', onMouseOut);
                    }
                };
                document.addEventListener('mouseout', onMouseOut);
            }
        })();

        // 2.0.3: pinned "Get started" banner removed — the admin UI no
        // longer exposes the field. Stored values are ignored client-side.
        // PHP no longer sends pinnedMessage in the config object either.

        // 2.1.x: chat-gate. When the admin has flipped on the chat-gate
        // toggle for the default pre-chat form, the FIRST visitor
        // message gets held client-side: not echoed into the thread,
        // not POSTed to the bot. We render the form inline below the
        // welcome bubble and only release the held message once the
        // visitor submits. Cancel collapses the widget to the FAB.
        //
        // Per-session: once submitted, the gate stays open for the rest
        // of the session. If the visitor cancels, we DON'T remember
        // that — they'll see the form again on their next first
        // message (intentional: re-prompt on re-open).
        (function attachChatGate() {
            const gateFormId = (config.chatGateFormId || '').trim();
            const formSchema = config.chatGateForm || null;
            if (!gateFormId || !formSchema) return;
            try {
                if (sessionStorage.getItem('bitbot-chat-gate-done') === '1') return;
            } catch (e) {}
            if (!form || !input) return;

            function renderGate(heldMessage) {
                const wrap = document.createElement('div');
                wrap.className = 'bitbot-chat-gate';
                const heading = (formSchema && formSchema.name) || 'A few details first';
                let fieldsHtml = '';
                const fields = (formSchema && formSchema.fields) || [
                    { name: 'name',  label: 'Full name', type: 'text',  required: true,  placeholder: 'Your name' },
                    { name: 'email', label: 'Email',     type: 'email', required: true,  placeholder: 'you@example.com' },
                ];
                fields.forEach(function(f) {
                    const ph = f.placeholder ? ' placeholder="' + String(f.placeholder).replace(/"/g, '&quot;') + '"' : '';
                    const req = f.required ? ' required' : '';
                    const type = f.type === 'email' ? 'email' : f.type === 'phone' ? 'tel' : 'text';
                    fieldsHtml += ''
                        + '<label class="bitbot-chat-gate-row">'
                        +   '<span>' + escapeHtml(f.label) + (f.required ? ' *' : '') + '</span>'
                        +   '<input data-field="' + escapeHtml(f.name) + '" type="' + type + '"' + ph + req + '>'
                        + '</label>';
                });
                wrap.innerHTML = ''
                    + '<div class="bitbot-chat-gate-head">' + escapeHtml(heading) + '</div>'
                    + '<form class="bitbot-chat-gate-form">'
                    +   fieldsHtml
                    +   '<div class="bitbot-chat-gate-actions">'
                    +     '<button type="submit" class="bitbot-chat-gate-submit">Submit</button>'
                    +     '<button type="button" class="bitbot-chat-gate-cancel">Cancel</button>'
                    +   '</div>'
                    + '</form>';
                messages.appendChild(wrap);
                scrollToBottom();

                const gateForm  = wrap.querySelector('.bitbot-chat-gate-form');
                const cancelBtn = wrap.querySelector('.bitbot-chat-gate-cancel');

                // 2.0.20: field-level error display. Earlier the form
                // would silently re-enable Submit on a server reject
                // (or accept "a@a" as an email because HTML5's regex
                // is too permissive). Now each input can carry an
                // error-row class + inline error span; typing in a
                // field clears its own error on input.
                function showFieldError(fieldName, message) {
                    const row = wrap.querySelector('.bitbot-chat-gate-row [data-field="' + fieldName + '"]')?.closest('.bitbot-chat-gate-row');
                    if (!row) return;
                    row.classList.add('bitbot-chat-gate-row-error');
                    let err = row.querySelector('.bitbot-chat-gate-row-msg');
                    if (!err) {
                        err = document.createElement('span');
                        err.className = 'bitbot-chat-gate-row-msg';
                        row.appendChild(err);
                    }
                    err.textContent = message;
                }
                function clearFieldError(fieldName) {
                    const row = wrap.querySelector('.bitbot-chat-gate-row [data-field="' + fieldName + '"]')?.closest('.bitbot-chat-gate-row');
                    if (!row) return;
                    row.classList.remove('bitbot-chat-gate-row-error');
                    const err = row.querySelector('.bitbot-chat-gate-row-msg');
                    if (err) err.remove();
                }
                function clearAllErrors() {
                    wrap.querySelectorAll('.bitbot-chat-gate-row-error').forEach(function(r) {
                        r.classList.remove('bitbot-chat-gate-row-error');
                        const m = r.querySelector('.bitbot-chat-gate-row-msg');
                        if (m) m.remove();
                    });
                }
                // Tight email regex — stricter than HTML5 type=email but
                // loose enough to accept any reasonable mailbox: requires
                // at least one char before @, a dot in the domain, and
                // at least two chars after the final dot.
                const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
                wrap.querySelectorAll('input[data-field]').forEach(function(el) {
                    el.addEventListener('input', function() {
                        clearFieldError(el.getAttribute('data-field'));
                    });
                });

                gateForm.addEventListener('submit', async function(ev) {
                    ev.preventDefault();
                    clearAllErrors();
                    const submitBtn = wrap.querySelector('.bitbot-chat-gate-submit');

                    const inputs = wrap.querySelectorAll('input[data-field]');
                    const values = {};
                    inputs.forEach(function(el) { values[el.getAttribute('data-field')] = (el.value || '').trim(); });

                    // Everything beyond the four standard fields is a custom
                    // answer; send it under `answers` keyed by field name so
                    // the backend stores it and the Responses table can show it.
                    const STANDARD_FIELDS = { name: 1, email: 1, phone: 1, message: 1 };
                    const answers = {};
                    Object.keys(values).forEach(function(k) {
                        if (!STANDARD_FIELDS[k] && values[k]) answers[k] = values[k];
                    });

                    // Client-side gate: catch obvious bad input before
                    // burning a server round-trip. Backend still
                    // double-checks since the API is callable directly.
                    if (!values.name) {
                        showFieldError('name', 'Please enter your name.');
                        return;
                    }
                    if (!values.email || !EMAIL_RE.test(values.email)) {
                        showFieldError('email', 'Please enter a valid email address.');
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Submitting…';

                    try {
                        const res = await fetch(config.restUrl + '/form-submit', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                formId:    gateFormId,
                                name:      values.name  || '',
                                email:     values.email || '',
                                phone:     values.phone || '',
                                message:   heldMessage,
                                // Visitor session — lets the backend
                                // link this lead to the conversation
                                // that the next sendMessage will
                                // populate, so the lead detail panel
                                // can render the chat transcript card.
                                // Use getSessionId() not the bare var
                                // — sessionId is lazily populated and
                                // is null until the first chat-send
                                // or this very call.
                                sessionId: getSessionId(),
                                answers: answers,
                            }),
                        });
                        const body = await res.json();
                        if (!res.ok || (body && body.error)) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Submit';
                            // Map server-side error to the offending
                            // field if the response told us which one.
                            // Falls back to the email row when shape
                            // is missing because that's the only field
                            // the server validates beyond "empty".
                            const msg = (body && body.error) ? body.error : 'Something went wrong. Please try again.';
                            const fieldName = (body && body.field) ? body.field : 'email';
                            showFieldError(fieldName, msg);
                            return;
                        }
                    } catch (e) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit';
                        showFieldError('email', 'Network error. Please try again.');
                        return;
                    }

                    try { sessionStorage.setItem('bitbot-chat-gate-done', '1'); } catch (e) {}
                    wrap.remove();
                    // Now release the held message: render it into the
                    // visible thread and send it through the normal
                    // chat path so the bot replies.
                    addMessage(heldMessage, 'user');
                    chatHistory.push({ role: 'user', content: heldMessage });
                    sendMessage(heldMessage);
                });

                cancelBtn.addEventListener('click', function() {
                    // Cancel = collapse widget back to FAB. Held message
                    // is dropped. Session flag stays unset so the gate
                    // re-prompts on the visitor's next first message.
                    wrap.remove();
                    if (chat && chat.style.display !== 'none' && typeof toggleChat === 'function') {
                        toggleChat();
                    }
                });
            }

            // Intercept the first submit: capture the typed value before
            // the normal handler echoes / sends it. We use capture-phase
            // so we run before the inner submit handler.
            form.addEventListener('submit', function gateHandler(ev) {
                // Once the session has the gate-done flag, this listener
                // is effectively inert because we exited up top. But we
                // also self-detach after first use to be safe.
                let done = false;
                try { done = sessionStorage.getItem('bitbot-chat-gate-done') === '1'; } catch (e) {}
                if (done) {
                    form.removeEventListener('submit', gateHandler, true);
                    return;
                }
                const value = (input.value || '').trim();
                if (!value) return;  // empty submit — let it fall through
                ev.preventDefault();
                ev.stopImmediatePropagation();
                input.value = '';
                form.removeEventListener('submit', gateHandler, true);
                renderGate(value);
            }, true);

            function escapeHtml(s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }
        })();

        // 1.9.0: suggested-question chips (8e). Render below welcome
        // message, hide on first visitor turn (whether they typed or
        // clicked a chip). Each click submits the chip's message as if
        // typed — same path as a normal send.
        const suggestedQuestions = Array.isArray(config.suggestedQuestions) ? config.suggestedQuestions : [];
        if (suggestedQuestions.length > 0 && messages) {
            const chipBar = document.createElement('div');
            chipBar.className = 'bitbot-chips';
            chipBar.setAttribute('role', 'group');
            chipBar.setAttribute('aria-label', 'Suggested questions');
            suggestedQuestions.forEach(function(q) {
                if (!q || (!q.label && !q.message)) return;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'bitbot-chip';
                btn.textContent = q.label || q.message;
                btn.addEventListener('click', function() {
                    // Hide entire chip bar on the first click.
                    chipBar.style.display = 'none';
                    const payload = (q.message || q.label || '').trim();
                    if (payload) {
                        addMessage(payload, 'user');
                        chatHistory.push({ role: 'user', content: payload });
                        sendMessage(payload);
                    }
                });
                chipBar.appendChild(btn);
            });
            messages.appendChild(chipBar);

            // Also hide on first typed-send (use the form submit listener
            // already wired below by capturing here, not the cleanest but
            // avoids cross-cutting state).
            if (form) {
                form.addEventListener('submit', function() {
                    chipBar.style.display = 'none';
                }, { once: true });
            }
        }

        // 1.9.0: apply custom placeholder if admin has set one in Settings.
        // Empty string keeps the template's localized "Type your message…"
        // default — no surprise behaviour change for existing installs.
        if (input && typeof config.placeholderText === 'string' && config.placeholderText.trim()) {
            input.setAttribute('placeholder', config.placeholderText.trim());
        }

        // Auto-reposition if a 3rd-party widget (UserWay, Tawk, etc.) is
        // squatting on the configured corner. Run once now + once after a
        // delay because a lot of accessibility widgets inject themselves
        // post-load.
        autoPositionWidget();
        setTimeout(autoPositionWidget, 1500);

        // Event listeners
        toggle.addEventListener('click', toggleChat);

        const minimize = widgetRoot.getElementById('bitbot-minimize');
        if (minimize) {
            minimize.addEventListener('click', toggleChat);
        }

        if (form) {
            form.addEventListener('submit', handleSubmit);
        }

        // Close on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isOpen) {
                toggleChat();
            }
        });

        // Rehydrate chat state from sessionStorage (persists across page navigation
        // within the same tab).
        const saved = loadChatState();
        if (saved && Array.isArray(saved.history) && saved.history.length > 0) {
            if (saved.sessionId) sessionId = saved.sessionId;
            chatHistory = saved.history;
            leadCapturedEmail = saved.leadCapturedEmail || null;
            liveMode = !!saved.liveMode;
            liveLastMessageId = saved.liveLastMessageId || null;
            if (liveMode) {
                // Restarting poll after page navigation — enterLiveMode is
                // idempotent, so we flip the flag back off first to let it
                // rebuild the banner + timer cleanly.
                liveMode = false;
                enterLiveMode();
            }

            // Remove the static welcome message — replay the real conversation.
            messages.innerHTML = '';
            chatHistory.forEach(function(msg) {
                addMessage(
                    msg.content,
                    msg.role === 'user' ? 'user' : 'bot',
                    msg.citations || null,
                    msg.products || null
                );
            });

            // Re-open the chat if the visitor had it open when they navigated.
            if (saved.isOpen) {
                toggleChat();
            }
        }

        // Presence: heartbeat while visible + leave beacon on unload.
        // This only matters once there's a conversation (so we gate
        // every tick on having a sessionId + backend-acknowledged).
        if (document.visibilityState === 'visible') {
            startHeartbeat();
        }
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                startHeartbeat();
            } else {
                stopHeartbeat();
            }
        });
        // pagehide covers mobile Safari + back/forward cache cases where
        // beforeunload is flaky. Using both is safe — idempotent on the server.
        //
        // The pagehide listener gates on `!event.persisted`: pagehide fires
        // on every internal navigation too (the previous page is being
        // unloaded for the new one), but `persisted=true` means the
        // browser may restore this page from bfcache, so the visitor
        // hasn't truly left — they could come back via Back. Only fire
        // the leave beacon on real unloads (tab close, off-site nav,
        // hard reload). Without this guard, every same-origin click
        // marked the conversation as "Ended" prematurely.
        window.addEventListener('beforeunload', sendLeaveBeacon);
        window.addEventListener('pagehide', function (ev) {
            if (ev && ev.persisted) return;
            sendLeaveBeacon();
        });
    }

    function startHeartbeat() {
        if (heartbeatTimer) return;
        heartbeatTimer = setInterval(function () {
            if (!sessionId || document.visibilityState !== 'visible') return;
            fetch(config.restUrl + '/visitor-heartbeat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce },
                body: JSON.stringify({ sessionId: sessionId }),
                keepalive: true,
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (json) {
                    // Heartbeat response carries the conversation's current
                    // live_mode flag. If an admin took the thread over on the
                    // dashboard, we pick it up here within one interval and
                    // flip the widget into live mode without waiting for the
                    // visitor's next message.
                    if (json && json.success && json.data && json.data.liveMode && !liveMode) {
                        enterLiveMode();
                    }
                })
                .catch(function () { /* silent */ });
        }, HEARTBEAT_INTERVAL_MS);
    }

    function stopHeartbeat() {
        if (heartbeatTimer) {
            clearInterval(heartbeatTimer);
            heartbeatTimer = null;
        }
    }

    function sendLeaveBeacon() {
        if (!sessionId) return;
        try {
            const body = JSON.stringify({ sessionId: sessionId });
            // sendBeacon survives the tab closing without us waiting for the
            // ack. Falls back to fetch+keepalive where sendBeacon isn't
            // available (very old browsers).
            if (navigator.sendBeacon) {
                navigator.sendBeacon(
                    config.restUrl + '/visitor-left',
                    new Blob([body], { type: 'application/json' })
                );
            } else {
                fetch(config.restUrl + '/visitor-left', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce },
                    body: body,
                    keepalive: true,
                }).catch(function () { /* silent */ });
            }
        } catch (e) {
            // Unload path — never throw.
        }
    }

    /**
     * Toggle chat window
     */
    function toggleChat() {
        isOpen = !isOpen;

        const iconChat = toggle.querySelector('.bitbot-icon-chat');
        const iconClose = toggle.querySelector('.bitbot-icon-close');

        if (isOpen) {
            chat.style.display = 'flex';
            if (iconChat) iconChat.style.display = 'none';
            if (iconClose) iconClose.style.display = 'block';
            if (input) input.focus();
        } else {
            chat.style.display = 'none';
            if (iconChat) iconChat.style.display = 'block';
            if (iconClose) iconClose.style.display = 'none';
        }

        saveChatState();
    }

    /**
     * Handle form submission
     */
    function handleSubmit(e) {
        e.preventDefault();

        const message = input.value.trim();
        if (!message || isLoading) return;

        // Add user message
        addMessage(message, 'user');
        chatHistory.push({ role: 'user', content: message });
        saveChatState();

        // Clear input
        input.value = '';

        // Send to API
        sendMessage(message);
    }

    /**
     * Send message to API
     */
    async function sendMessage(message) {
        isLoading = true;
        showTypingIndicator();

        try {
            const response = await fetch(config.restUrl + '/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    history: chatHistory.slice(-CHAT_SEND_MAX),
                    sessionId: getSessionId(),
                    visitorId: getVisitorId(),
                    // 1.7.0: visitor's IANA timezone so appointment slots +
                    // confirmations render in their local time rather than the
                    // site owner's.
                    visitorTimezone: getVisitorTimezone(),
                    siteLanguage: config.languageCode || 'en',  // Pass site language
                    // 1.9.0 Feature 8i custom copy bundle (server-side
                    // consumers). Empty strings tell the backend to use
                    // built-in agent defaults. Backend ignores unknown
                    // fields, so pre-1.9 deploys are forward-compatible.
                    fallbackMessage: (config.fallbackMessage || '').trim(),
                    leadCapturePrompt: (config.leadCapturePrompt || '').trim(),
                })
            });

            const data = await response.json();

            hideTypingIndicator();

            if (data.success) {
                // Only render an agent-side message if the backend actually
                // sent one. In live mode the backend returns { message: '' }
                // because the agent stepped aside.
                if (data.data.message && data.data.message.trim() !== '') {
                    addMessage(data.data.message, 'bot', data.data.citations, data.data.products);
                    // Persist citations + product cards alongside the
                    // text so back-nav rehydration restores the full
                    // bubble, not just the prose. Both fields are
                    // optional + small (<= a few KB each); the
                    // CHAT_LOCAL_MAX cap still bounds total state size.
                    chatHistory.push({
                        role: 'assistant',
                        content: data.data.message,
                        citations: data.data.citations || null,
                        products: data.data.products || null,
                    });
                }
                saveChatState();

                // Show lead captured confirmation
                if (data.data.lead_captured && !leadCapturedEmail) {
                    var emailMatch = message.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/);
                    leadCapturedEmail = emailMatch ? emailMatch[0] : true;
                    saveChatState();
                    var confirmDiv = document.createElement('div');
                    confirmDiv.className = 'bitbot-lead-confirmed';
                    confirmDiv.innerHTML = '<span class="bitbot-lead-check">&#10003;</span> We\'ll follow up' +
                        (typeof leadCapturedEmail === 'string' ? ' at ' + escapeHtml(leadCapturedEmail) : '') + '.';
                    messages.appendChild(confirmDiv);
                    scrollToBottom();
                }

                // Live-handover detection: enter live mode as soon as backend
                // flips to it (either because this turn triggered it via the
                // agent, OR because a prior turn did and we're already there).
                if (data.data.live_mode && !liveMode) {
                    enterLiveMode();
                }
            } else {
                var errMsg = (data && typeof data.error === 'string' && data.error.trim())
                    ? data.error
                    : 'Sorry, I encountered an error. Please try again.';
                // Backend says the bot can't reply right now. Always
                // hide the widget for the rest of this session rather
                // than pretend with degraded copy.
                //
                //   ACCESS_PAUSED:  — generic "bot is paused" (new in
                //                    2.0.119; covers expired sub and
                //                    Free chat-cap)
                //   CHAT_CAP:       — legacy Free monthly cap (pre-2.0.119)
                //   TRIAL_EXPIRED:  — legacy 30-day trial (pre-freemium)
                //
                // We promise the visitor an answer or we hide; we never
                // ask for an email we can't act on. Resets next session.
                if (
                    errMsg.indexOf('ACCESS_PAUSED:') === 0 ||
                    errMsg.indexOf('CHAT_CAP:') === 0 ||
                    errMsg.indexOf('TRIAL_EXPIRED:') === 0
                ) {
                    hideWidgetForSession();
                    return;
                }
                addMessage(errMsg, 'bot');
            }
        } catch (error) {
            hideTypingIndicator();
            addMessage('Sorry, I couldn\'t connect to the server. Please try again later.', 'bot');
        }

        isLoading = false;
    }

    /**
     * Hide the widget for the rest of THIS page view when a chat reply comes
     * back paused (expired sub or Free chat cap) — avoids leaving a dead bot
     * on screen after the visitor already hit the wall. No client flag: on the
     * next page load the server-driven config.accessPaused (refreshed within
     * ~5 min) decides whether the FAB renders, so it reappears on its own once
     * the cap lifts.
     */
    function hideWidgetForSession() {
        var host = document.getElementById('bitbot-widget');
        if (host) host.style.display = 'none';
    }

    /**
     * Enter live-handover mode. Starts polling for admin replies and shows
     * an indicator banner so the visitor knows they're now talking to a
     * person. Idempotent.
     *
     * @since 1.7.0
     */
    function enterLiveMode() {
        if (liveMode) return;
        liveMode = true;
        saveChatState();

        var banner = document.createElement('div');
        banner.className = 'bitbot-live-banner';
        banner.style.cssText = 'padding: 8px 12px; margin: 8px 0; background: #e7f5ea; border-left: 3px solid #46b450; border-radius: 4px; font-size: 12px; color: #1a5d2f;';
        banner.innerHTML = '<strong>Live:</strong> You\'re now chatting with someone from the team.';
        messages.appendChild(banner);
        scrollToBottom();

        if (livePollTimer) clearInterval(livePollTimer);
        livePollTimer = setInterval(pollLiveUpdates, 5000);
    }

    /**
     * Exit live mode — admin closed the thread, agent resumes.
     *
     * @since 1.7.0
     */
    function exitLiveMode(reason) {
        if (!liveMode) return;
        liveMode = false;
        if (livePollTimer) { clearInterval(livePollTimer); livePollTimer = null; }
        saveChatState();

        var banner = document.createElement('div');
        banner.className = 'bitbot-live-banner';
        banner.style.cssText = 'padding: 8px 12px; margin: 8px 0; background: #f4f4f5; border-left: 3px solid #888; border-radius: 4px; font-size: 12px; color: #555;';
        banner.innerHTML = reason || 'The team has handed things back to the assistant.';
        messages.appendChild(banner);
        scrollToBottom();
    }

    /**
     * Poll the plugin's REST bridge for new admin messages + live_mode state.
     *
     * @since 1.7.0
     */
    async function pollLiveUpdates() {
        if (!liveMode) return;
        try {
            const res = await fetch(config.restUrl + '/live-updates', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    sessionId: getSessionId(),
                    sinceMessageId: liveLastMessageId || '',
                }),
            });
            const data = await res.json();
            if (!data || !data.success || !data.data) return;

            var payload = data.data;
            var newOnes = payload.newMessages || [];
            for (var i = 0; i < newOnes.length; i++) {
                addMessage(newOnes[i].content, 'admin');
                chatHistory.push({ role: 'assistant', content: newOnes[i].content });
                liveLastMessageId = newOnes[i].id;
            }
            if (newOnes.length > 0) saveChatState();

            if (!payload.liveMode) {
                exitLiveMode('The team has handed things back to the assistant. Ask anything!');
            }
        } catch (e) {
            // swallow — next tick retries
        }
    }

    /**
     * Simple markdown to HTML conversion
     */
    function parseMarkdown(text) {
        return text
            // Escape HTML first
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            // Markdown links [text](url) — do this BEFORE auto-linking so we
            // don't double-wrap the URL portion of a markdown link.
            .replace(
                /\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g,
                '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>'
            )
            // Bare URLs not already inside an href="..." — auto-link them so
            // `Visit https://example.com` renders as a clickable link.
            .replace(
                /(^|[^"'>=])(https?:\/\/[^\s<)]+)/g,
                '$1<a href="$2" target="_blank" rel="noopener noreferrer">$2</a>'
            )
            // Bold **text**
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            // Italic *text*
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            // Line breaks
            .replace(/\n/g, '<br>')
            // Lists - lines starting with -
            .replace(/^- (.+)$/gm, '<li>$1</li>')
            .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
            // Clean up multiple <ul> tags
            .replace(/<\/ul>\s*<ul>/g, '');
    }

    /**
     * Add message to chat
     */
    function addMessage(content, sender, citations, products) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'bitbot-message bitbot-' + sender;

        // Admin messages (live-handover): render with a distinct label so the
        // visitor clearly knows a person wrote this, not the agent.
        if (sender === 'admin') {
            const label = document.createElement('div');
            label.className = 'bitbot-message-label';
            label.style.cssText = 'font-size: 10px; font-weight: 600; color: #46b450; text-transform: uppercase; margin-bottom: 2px;';
            label.textContent = 'Team';
            messageDiv.appendChild(label);
        }

        // Use markdown parsing for bot + admin messages (both may format), escape for user
        const formattedContent = (sender === 'bot' || sender === 'admin') ? parseMarkdown(content) : escapeHtml(content);
        let html = '<div class="bitbot-message-content">' + formattedContent + '</div>';

        // Add citations if present
        if (citations && citations.length > 0) {
            html += '<div class="bitbot-citations">';
            html += '<div class="bitbot-citations-title">Related pages:</div>';

            citations.forEach(function(citation) {
                let url = citation.url;
                if (citation.anchorId) {
                    url += '#' + citation.anchorId;
                }
                html += '<a href="' + escapeHtml(url) + '" class="bitbot-citation-link" target="_blank" rel="noopener noreferrer">';
                html += escapeHtml(citation.title || citation.url);
                html += '</a>';
            });

            html += '</div>';
        }

        // Product cards from the Pro+ agent's `searchProducts` tool. Rendered
        // as a horizontal card strip so the visitor sees image + price + CTA
        // the same way they would on a catalogue page, instead of reading the
        // agent's inline markdown links.
        if (products && products.length > 0) {
            html += '<div class="bitbot-product-cards">';
            products.forEach(function (p) {
                const name = escapeHtml(p.name || '');
                const url = escapeHtml(p.url || '#');
                const image = p.imageUrl ? escapeHtml(p.imageUrl) : '';
                const blurb = escapeHtml((p.shortDescription || '').slice(0, 140));
                const rawPrice = p.onSale && p.salePrice ? p.salePrice : p.price;
                const hasStrike = p.onSale && p.salePrice && p.price;
                const price = rawPrice ? escapeHtml(rawPrice) : '';
                const oldPrice = hasStrike ? escapeHtml(p.price) : '';
                const outOfStock = p.inStock === false;

                html += '<a class="bitbot-product-card" href="' + url + '" target="_blank" rel="noopener noreferrer">';
                if (image) {
                    html += '<div class="bitbot-product-image" style="background-image: url(\'' + image + '\')"></div>';
                } else {
                    html += '<div class="bitbot-product-image bitbot-product-image-empty"></div>';
                }
                html += '<div class="bitbot-product-body">';
                html += '<div class="bitbot-product-name">' + name + '</div>';
                if (blurb) {
                    html += '<div class="bitbot-product-blurb">' + blurb + '</div>';
                }
                html += '<div class="bitbot-product-foot">';
                if (price) {
                    html += '<span class="bitbot-product-price">';
                    if (oldPrice) {
                        html += '<span class="bitbot-product-old-price">$' + oldPrice + '</span> ';
                    }
                    html += '$' + price + '</span>';
                }
                if (outOfStock) {
                    html += '<span class="bitbot-product-oos">Out of stock</span>';
                } else {
                    html += '<span class="bitbot-product-cta">View →</span>';
                }
                html += '</div></div></a>';
            });
            html += '</div>';
        }

        messageDiv.innerHTML = html;
        messages.appendChild(messageDiv);
        scrollToBottom();
    }

    /**
     * Show typing indicator. If the admin has set a custom typing string
     * in Settings (1.9.0 custom copy bundle), render that text in place
     * of the animated dots. Empty string → fall back to dots.
     */
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'bitbot-typing-indicator';
        typingDiv.className = 'bitbot-message bitbot-bot';
        const customTypingText = (config.typingIndicatorText || '').trim();
        if (customTypingText) {
            const escaped = customTypingText
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            typingDiv.innerHTML = '<div class="bitbot-typing bitbot-typing-text">' + escaped + '</div>';
        } else {
            typingDiv.innerHTML = '<div class="bitbot-typing"><span></span><span></span><span></span></div>';
        }
        messages.appendChild(typingDiv);
        scrollToBottom();
    }

    /**
     * Hide typing indicator
     */
    function hideTypingIndicator() {
        const typing = widgetRoot.getElementById('bitbot-typing-indicator');
        if (typing) {
            typing.remove();
        }
    }

    /**
     * Scroll messages to bottom
     */
    function scrollToBottom() {
        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Refresh config past page caches, then initialize.
    // bitbotConfig is inlined into the page HTML via wp_localize_script for
    // first paint, but full-page caches (LiteSpeed/WP Rocket/CDN) freeze that
    // HTML — so a settings change (avatar, colour, copy) can be stale. We
    // fetch the live config from the uncacheable /widget-config endpoint with
    // a cache-buster and override the inlined values BEFORE rendering, so the
    // widget always reflects current settings regardless of any cache layer.
    // A 2.5s timeout guarantees the widget still renders (from the inlined
    // config) if the fetch is slow/blocked.
    function bootstrap() {
        var base = (config.restUrl || '').replace(/\/+$/, '');
        if (!base) { init(); return; }
        var started = false;
        var go = function () { if (!started) { started = true; init(); } };
        var timer = setTimeout(go, 2500);
        fetch(base + '/widget-config?_=' + Date.now(), { cache: 'no-store', credentials: 'omit' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (fresh) {
                if (fresh && typeof fresh === 'object') {
                    for (var k in fresh) { if (Object.prototype.hasOwnProperty.call(fresh, k)) config[k] = fresh[k]; }
                }
            })
            .catch(function () { /* keep inlined config on any failure */ })
            .then(function () { clearTimeout(timer); go(); });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
})();

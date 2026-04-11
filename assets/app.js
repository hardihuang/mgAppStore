/* ============================================================
   MG APP Store — app.js  v3.0
   Features:
     - Starfield background
     - Tag filter / sort / search
     - timeAgo display
     - Cookie-based edit button
     - Download button
     - iframe preview modal (AJAX view count)
     - Hot badge modal
     - AI tips modal
     - Celebrate modal + confetti
     - Minecraft Web Audio sound effects
     - Click ripple visual feedback
   ============================================================ */

'use strict';

// ── Tiny helpers ──────────────────────────────────────────────────────────
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

// ── Modal helpers ─────────────────────────────────────────────────────────
function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('active'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('active'); document.body.style.overflow = ''; }
}
// Close by clicking overlay backdrop
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// ── Minecraft Sound Engine ────────────────────────────────────────────────
const mcSound = (() => {
    let ctx = null;

    function getCtx() {
        if (!ctx) {
            try { ctx = new (window.AudioContext || window.webkitAudioContext)(); } catch (e) { return null; }
        }
        if (ctx.state === 'suspended') ctx.resume();
        return ctx;
    }

    // Core tone player
    function tone(freq, duration, type = 'square', volume = 0.18, startTime = null) {
        const c = getCtx();
        if (!c) return;
        const t = startTime ?? c.currentTime;
        const osc  = c.createOscillator();
        const gain = c.createGain();
        osc.connect(gain);
        gain.connect(c.destination);
        osc.type = type;
        osc.frequency.setValueAtTime(freq, t);
        gain.gain.setValueAtTime(volume, t);
        gain.gain.exponentialRampToValueAtTime(0.001, t + duration);
        osc.start(t);
        osc.stop(t + duration + 0.01);
    }

    // Noise burst (for click/gravel sounds)
    function noise(duration, volume = 0.08) {
        const c = getCtx();
        if (!c) return;
        const bufLen = Math.floor(c.sampleRate * duration);
        const buf = c.createBuffer(1, bufLen, c.sampleRate);
        const data = buf.getChannelData(0);
        for (let i = 0; i < bufLen; i++) data[i] = Math.random() * 2 - 1;
        const src  = c.createBufferSource();
        const gain = c.createGain();
        src.buffer = buf;
        src.connect(gain);
        gain.connect(c.destination);
        gain.gain.setValueAtTime(volume, c.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, c.currentTime + duration);
        src.start();
    }

    return {
        // ▶ Play / Open game — rising "level up" ding
        play() {
            const c = getCtx(); if (!c) return;
            const now = c.currentTime;
            tone(330, 0.08, 'square', 0.15, now);
            tone(440, 0.08, 'square', 0.15, now + 0.07);
            tone(550, 0.12, 'square', 0.18, now + 0.14);
        },

        // + Submit button — triumphant "item pickup" chord
        submit() {
            const c = getCtx(); if (!c) return;
            const now = c.currentTime;
            tone(523, 0.10, 'square', 0.14, now);
            tone(659, 0.10, 'square', 0.14, now + 0.08);
            tone(784, 0.16, 'square', 0.18, now + 0.16);
            tone(1047, 0.20, 'sine', 0.12, now + 0.24);
        },

        // 🏷 Filter / sort / tag btn — stone button "click"
        click() {
            const c = getCtx(); if (!c) return;
            const now = c.currentTime;
            noise(0.04, 0.12);
            tone(180, 0.06, 'square', 0.10, now);
        },

        // ⬇ Download — "chest open" descending
        download() {
            const c = getCtx(); if (!c) return;
            const now = c.currentTime;
            tone(440, 0.09, 'square', 0.14, now);
            tone(370, 0.09, 'square', 0.14, now + 0.08);
            tone(294, 0.13, 'square', 0.16, now + 0.16);
        },

        // ✕ Close modal — soft "thunk" dismiss
        close() {
            const c = getCtx(); if (!c) return;
            const now = c.currentTime;
            tone(220, 0.07, 'square', 0.12, now);
            tone(165, 0.10, 'square', 0.10, now + 0.05);
        },

        // 💡 AI button — short "pop" note
        ai() {
            const c = getCtx(); if (!c) return;
            const now = c.currentTime;
            tone(660, 0.06, 'sine', 0.12, now);
            tone(880, 0.09, 'sine', 0.10, now + 0.07);
        },

        // 🔍 Search typing — very faint tick
        tick() {
            const c = getCtx(); if (!c) return;
            noise(0.025, 0.05);
        },

        // 🚀 Celebrate — epic fanfare
        celebrate() {
            const c = getCtx(); if (!c) return;
            const now = c.currentTime;
            [[262,0],[330,0.1],[392,0.2],[523,0.32],[659,0.44],[784,0.56],[1047,0.7]].forEach(([f, dt]) => {
                tone(f, 0.18, 'square', 0.14, now + dt);
            });
        },
    };
})();

// ── Click ripple visual feedback ─────────────────────────────────────────
function spawnRipple(e, emoji = '⛏') {
    const el = document.createElement('span');
    el.className = 'mc-click-ripple';
    el.textContent = emoji;
    el.style.left = (e.clientX - 12) + 'px';
    el.style.top  = (e.clientY - 12) + 'px';
    document.body.appendChild(el);
    el.addEventListener('animationend', () => el.remove());
}

const RIPPLE_EMOJIS = {
    play:     '▶',
    submit:   '🚀',
    download: '⬇',
    filter:   '🏷',
    sort:     '🔀',
    close:    '✕',
    ai:       '💡',
    click:    '⛏',
};

// ── Starfield ─────────────────────────────────────────────────────────────
(function initStars() {
    const canvas = document.getElementById('stars-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    function resize() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    const STAR_COUNT = 200;
    const stars = Array.from({ length: STAR_COUNT }, () => ({
        x:   Math.random() * canvas.width,
        y:   Math.random() * canvas.height,
        r:   Math.random() * 1.4 + 0.3,
        spd: Math.random() * 0.3 + 0.05,
        op:  Math.random() * 0.7 + 0.2,
    }));

    function drawStars() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (const s of stars) {
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255,255,255,${s.op})`;
            ctx.fill();
            s.y -= s.spd;
            if (s.y < -2) { s.y = canvas.height + 2; s.x = Math.random() * canvas.width; }
        }
        requestAnimationFrame(drawStars);
    }
    drawStars();
})();

// ── Time ago ──────────────────────────────────────────────────────────────
function timeAgo(ts, lang = 'zh') {
    const diff = Math.floor(Date.now() / 1000) - ts;
    if (lang === 'en') {
        if (diff < 60)        return 'just now';
        if (diff < 3600)      return Math.floor(diff / 60)        + 'm ago';
        if (diff < 86400)     return Math.floor(diff / 3600)      + 'h ago';
        if (diff < 86400 * 7) return Math.floor(diff / 86400)     + 'd ago';
        if (diff < 86400 * 30)return Math.floor(diff / 86400 / 7) + 'w ago';
        return Math.floor(diff / 86400 / 30) + 'mo ago';
    }
    if (diff < 60)        return '刚刚';
    if (diff < 3600)      return Math.floor(diff / 60)        + '分钟前';
    if (diff < 86400)     return Math.floor(diff / 3600)      + '小时前';
    if (diff < 86400 * 7) return Math.floor(diff / 86400)     + '天前';
    if (diff < 86400 * 30)return Math.floor(diff / 86400 / 7) + '周前';
    return Math.floor(diff / 86400 / 30) + '个月前';
}

// ── Cookie helpers ────────────────────────────────────────────────────────
function getEditTokens() {
    try {
        const match = document.cookie.match(/(?:^|;\s*)mg_edit_tokens=([^;]*)/);
        return match ? JSON.parse(decodeURIComponent(match[1])) : {};
    } catch { return {}; }
}

// ── Card enhancements (time, edit button, AI button, heat badges) ─────────
function enhanceCards() {
    const lang       = window.pageLang || 'zh';
    const now        = Math.floor(Date.now() / 1000);
    const editTokens = getEditTokens();

    $$('.app-card').forEach(card => {
        const ts      = parseInt(card.dataset.ts    || '0', 10);
        const views   = parseInt(card.dataset.views || '0', 10);
        const appId   = card.dataset.id || '';
        const appFile = card.dataset.file || '';
        const age     = now - ts;  // seconds since upload

        // ── Time display ──
        const timeEl = card.querySelector('.card-time');
        if (timeEl && ts) timeEl.textContent = timeAgo(ts, lang);

        // ── Edit button (cookie-based) ──
        if (appId && editTokens[appId]) {
            const editBtn = card.querySelector('.btn-edit-card');
            if (editBtn) editBtn.style.display = 'inline-flex';
        }

        // ── Heat class & badges ──
        if (views >= 50) {
            card.classList.add('card-hot-glow');
            // 🔥 HOT badge (already in PHP for views>=50... but add via JS as well for robustness)
        } else if (views >= 20) {
            card.classList.add('card-warm');
        }

        // ── AI tip button (low heat + uploaded > 3 days) ──
        if (views < 5 && age > 259200) {
            const aiBtn = card.querySelector('.btn-ai-card');
            if (aiBtn) aiBtn.style.display = 'block';
        }
    });
}

// ── Filter / Sort / Search ────────────────────────────────────────────────
let activeTag  = 'all';
let activeSort = 'new';
let searchTerm = '';

function applyFilters() {
    const cards = $$('.app-card');
    const visible = [];

    cards.forEach(card => {
        const tag    = card.dataset.tag    || '';
        const title  = (card.dataset.title  || '');
        const author = (card.dataset.author || '');
        const views  = parseInt(card.dataset.views || '0', 10);
        const ts     = parseInt(card.dataset.ts    || '0', 10);

        const tagOk    = activeTag === 'all' || tag === activeTag;
        const searchOk = !searchTerm ||
                         title.includes(searchTerm) || author.includes(searchTerm);

        card.style.display = (tagOk && searchOk) ? '' : 'none';
        if (tagOk && searchOk) visible.push({ el: card, views, ts });
    });

    if (activeSort === 'hot')    visible.sort((a, b) => b.views - a.views);
    else if (activeSort === 'new') visible.sort((a, b) => b.ts - a.ts);
    else if (activeSort === 'random') visible.sort(() => Math.random() - 0.5);

    const grid = document.getElementById('appGrid');
    if (grid) visible.forEach(({ el }) => grid.appendChild(el));
}

$$('.tag-filter-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        $$('.tag-filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeTag = this.dataset.tag;
        mcSound.click();
        spawnRipple(e, RIPPLE_EMOJIS.filter);
        applyFilters();
    });
});

$$('.sort-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        $$('.sort-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeSort = this.dataset.sort;
        mcSound.click();
        spawnRipple(e, RIPPLE_EMOJIS.sort);
        applyFilters();
    });
});

const searchBox = document.getElementById('searchBox');
if (searchBox) {
    let searchTick = 0;
    searchBox.addEventListener('input', function () {
        searchTerm = this.value.toLowerCase().trim();
        // Play tick every 3 keystrokes to avoid spam
        if (++searchTick % 3 === 0) mcSound.tick();
        applyFilters();
    });
}

// ── Submit modal ──────────────────────────────────────────────────────────
const submitBtn = document.getElementById('submitBtn');
if (submitBtn) submitBtn.addEventListener('click', e => {
    mcSound.submit();
    spawnRipple(e, RIPPLE_EMOJIS.submit);
    openModal('submitModal');
});

// Char counter
const descTextarea = document.getElementById('desc');
const charCount    = document.getElementById('charCount');
if (descTextarea && charCount) {
    descTextarea.addEventListener('input', function () {
        charCount.textContent = this.value.length + ' / 200';
    });
}

// Duplicate check
const uploadForm = document.getElementById('uploadForm');
if (uploadForm) {
    uploadForm.addEventListener('submit', function (e) {
        const author   = (document.getElementById('author')?.value   || '').trim().toLowerCase();
        const title    = (document.getElementById('app_name')?.value || '').trim().toLowerCase();
        const existing = window.existingApps || [];
        const dup      = existing.find(a =>
            a.author.toLowerCase() === author && a.title.toLowerCase() === title
        );
        if (dup && !window.__dupConfirmed) {
            e.preventDefault();
            if (confirm(window.dupWarnMsg || 'Duplicate. Submit anyway?')) {
                window.__dupConfirmed = true;
                this.submit();
            }
        }
    });
}

// ── iframe Preview Modal ──────────────────────────────────────────────────
const previewModal  = document.getElementById('previewModal');
const previewIframe = document.getElementById('previewIframe');
const previewTitleEl = document.getElementById('previewTitle');
const previewFullscreen = document.getElementById('previewFullscreen');
const previewDownload   = document.getElementById('previewDownload');
const previewEdit       = document.getElementById('previewEditBtn');

function openPreview(appId, title, file) {
    if (!previewModal || !previewIframe) return;

    mcSound.play();

    if (previewTitleEl)  previewTitleEl.textContent  = title;
    if (previewDownload) {
        previewDownload.href      = file;
        previewDownload.download  = title + '.html';
    }

    // Edit button — show only if cookie token exists
    if (previewEdit) {
        const tokens = getEditTokens();
        if (tokens[appId]) {
            previewEdit.href         = `edit.php?id=${encodeURIComponent(appId)}`;
            previewEdit.style.display = 'inline-block';
        } else {
            previewEdit.style.display = 'none';
        }
    }

    // Load iframe
    previewIframe.src = file;
    openModal('previewModal');

    // Update views count on card
    fetch(`play.php?id=${encodeURIComponent(appId)}&ajax=1`)
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                const card = document.querySelector(`.app-card[data-id="${appId}"]`);
                if (card) {
                    card.dataset.views = data.views;
                    const viewEl = card.querySelector('.card-views');
                    const lang   = window.pageLang || 'zh';
                    if (viewEl) viewEl.textContent = `👁 ${data.views} ${window.viewsLabel || '次访问'}`;
                }
            }
        }).catch(() => {});
}

// Fullscreen
if (previewFullscreen && previewIframe) {
    previewFullscreen.addEventListener('click', e => {
        mcSound.click();
        spawnRipple(e, '🔲');
        (previewIframe.requestFullscreen || previewIframe.webkitRequestFullscreen ||
         previewIframe.mozRequestFullScreen).call(previewIframe);
    });
}

// Clear iframe src on close (stop game music/code)
if (previewModal) {
    previewModal.addEventListener('click', function (e) {
        if (e.target === this) {
            mcSound.close();
            if (previewIframe) previewIframe.src = '';
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}
const previewCloseBtn = document.getElementById('previewCloseBtn');
if (previewCloseBtn) {
    previewCloseBtn.addEventListener('click', e => {
        mcSound.close();
        spawnRipple(e, RIPPLE_EMOJIS.close);
        if (previewIframe) previewIframe.src = '';
        closeModal('previewModal');
    });
}

// Sound on all modal-close buttons (✕)
document.addEventListener('click', e => {
    const btn = e.target.closest('.modal-close');
    if (btn) { mcSound.close(); spawnRipple(e, RIPPLE_EMOJIS.close); }
    // Download card button
    const dlBtn = e.target.closest('.btn-download-card');
    if (dlBtn) { mcSound.download(); spawnRipple(e, RIPPLE_EMOJIS.download); }
    // Preview download button
    const pdBtn = e.target.closest('#previewDownload');
    if (pdBtn) { mcSound.download(); spawnRipple(e, RIPPLE_EMOJIS.download); }
    // Hot badge download
    const hbDl = e.target.closest('.btn-download');
    if (hbDl) { mcSound.download(); spawnRipple(e, RIPPLE_EMOJIS.download); }
    // Upload my version button
    const upBtn = e.target.closest('.btn-upload-my');
    if (upBtn) { mcSound.submit(); spawnRipple(e, RIPPLE_EMOJIS.submit); }
});

// Attach click to cards
$$('.app-card').forEach(card => {
    card.addEventListener('click', function (e) {
        // Ignore clicks on buttons/links within card
        if (e.target.closest('a,button')) return;
        const appId = this.dataset.id;
        const title = this.dataset.titleOrig || this.dataset.title || '';
        const file  = this.dataset.file || '';
        const views = parseInt(this.dataset.views || '0', 10);

        if (views >= 30) {
            mcSound.click();
            spawnRipple(e, '🔥');
            // Show hot badge modal instead
            openHotBadge(appId, views, file);
        } else {
            spawnRipple(e, RIPPLE_EMOJIS.play);
            openPreview(appId, title, file);
        }
    });
});

// ── Hot Badge Modal ───────────────────────────────────────────────────────
function openHotBadge(appId, viewCount, file) {
    const modal = document.getElementById('hotBadgeModal');
    if (!modal) return;
    const playsEl = modal.querySelector('.plays-count');
    const dlBtn   = modal.querySelector('.btn-download');
    const upBtn   = modal.querySelector('.btn-upload-my');
    if (playsEl) playsEl.textContent = viewCount;
    if (dlBtn)   { dlBtn.href = file; dlBtn.download = ''; }
    if (upBtn)   upBtn.href = 'index.php';
    openModal('hotBadgeModal');
}

// play button still opens preview (not hot modal)
$$('.btn-play').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        mcSound.play();
        spawnRipple(e, RIPPLE_EMOJIS.play);
        const card  = this.closest('.app-card');
        if (!card) return;
        const appId = card.dataset.id;
        const title = card.dataset.titleOrig || card.dataset.title || '';
        const file  = card.dataset.file || '';
        openPreview(appId, title, file);
    });
});

// ── AI Tips Modal ─────────────────────────────────────────────────────────
const aiCopyBtn = document.getElementById('aiCopyBtn');
if (aiCopyBtn) {
    aiCopyBtn.addEventListener('click', function (e) {
        mcSound.ai();
        spawnRipple(e, RIPPLE_EMOJIS.ai);
        const text = document.getElementById('aiBodyText')?.textContent || '';
        navigator.clipboard.writeText(text).then(() => {
            const orig = this.textContent;
            this.textContent = '✅ 已复制!';
            setTimeout(() => { this.textContent = orig; }, 2000);
        }).catch(() => {});
    });
}

// AI button on cold cards
$$('.btn-ai-card').forEach(btn => {
    btn.addEventListener('click', e => {
        e.stopPropagation();
        mcSound.ai();
        spawnRipple(e, RIPPLE_EMOJIS.ai);
        openModal('aiModal');
    });
});

// ── Confetti ──────────────────────────────────────────────────────────────
function launchConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;

    const COLORS = ['#4facfe','#00f2fe','#fbbf24','#34d399','#f472b6','#a78bfa','#ff6b6b'];
    const pieces = Array.from({ length: 130 }, () => ({
        x: Math.random() * canvas.width,
        y: Math.random() * -canvas.height,
        r: Math.random() * 6 + 3,
        d: Math.random() * 80 + 40,
        color: COLORS[Math.floor(Math.random() * COLORS.length)],
        tilt: 0, tiltAngle: 0,
        tiltSpeed: Math.random() * 0.07 + 0.04,
    }));
    let angle = 0, frame;
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        angle += 0.01;
        pieces.forEach(p => {
            p.tiltAngle += p.tiltSpeed;
            p.y += Math.cos(angle + p.d) + 2;
            p.x += Math.sin(angle) * 0.8;
            p.tilt = Math.sin(p.tiltAngle) * 12;
            ctx.beginPath();
            ctx.lineWidth = p.r;
            ctx.strokeStyle = p.color;
            ctx.moveTo(p.x + p.tilt + p.r / 4, p.y);
            ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r / 4);
            ctx.stroke();
            if (p.y > canvas.height) { p.y = -10; p.x = Math.random() * canvas.width; }
        });
        frame = requestAnimationFrame(draw);
    }
    draw();
    setTimeout(() => { cancelAnimationFrame(frame); ctx.clearRect(0, 0, canvas.width, canvas.height); }, 4500);
}

// ── Orbs physics animation ────────────────────────────────────────────────
function initOrbs() {
    const canvas = document.getElementById('orbs-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    // All light/white tones only
    const COLORS = [
        'rgba(255,255,255,0.22)',
        'rgba(255,255,255,0.18)',
        'rgba(255,255,255,0.28)',
        'rgba(200,230,255,0.20)',
        'rgba(180,220,255,0.16)',
    ];

    let orbs = [], particles = [], W, H;

    function randR() { return 30 + Math.random() * 90; } // 30–120px, random radius

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }

    function makeOrb(x, y) {
        const r = randR();
        return {
            x: x !== undefined ? x : r + Math.random() * (W - r * 2),
            y: y !== undefined ? y : r + Math.random() * (H - r * 2),
            vx: (Math.random() - 0.5) * 0.6,
            vy: (Math.random() - 0.5) * 0.6,
            r,
            color: COLORS[Math.floor(Math.random() * COLORS.length)],
        };
    }

    function init() {
        resize();
        orbs = Array.from({ length: 9 }, () => makeOrb());
    }

    function resolveCollision(a, b) {
        const dx = b.x - a.x, dy = b.y - a.y;
        const dist = Math.sqrt(dx*dx + dy*dy);
        const minDist = a.r + b.r;
        if (dist === 0 || dist >= minDist) return;
        const nx = dx/dist, ny = dy/dist;
        const overlap = (minDist - dist) / 2;
        a.x -= nx*overlap; a.y -= ny*overlap;
        b.x += nx*overlap; b.y += ny*overlap;
        const dot = (a.vx-b.vx)*nx + (a.vy-b.vy)*ny;
        if (dot > 0) return;
        a.vx -= dot*nx; a.vy -= dot*ny;
        b.vx += dot*nx; b.vy += dot*ny;
    }

    // Burst particles on click
    function burst(x, y, r, color) {
        const count = Math.floor(r / 8) + 4;
        for (let i = 0; i < count; i++) {
            const angle = (Math.PI * 2 / count) * i + Math.random() * 0.4;
            const speed = 1.5 + Math.random() * 3;
            particles.push({
                x, y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                r: 3 + Math.random() * (r / 6),
                alpha: 0.7,
                color,
            });
        }
    }

    // Click: pop nearest orb
    canvas.addEventListener('click', e => {
        const mx = e.clientX, my = e.clientY;
        let closest = -1, minD = Infinity;
        orbs.forEach((o, i) => {
            const d = Math.sqrt((o.x-mx)**2 + (o.y-my)**2);
            if (d < o.r && d < minD) { minD = d; closest = i; }
        });
        if (closest === -1) return;
        const o = orbs[closest];
        burst(o.x, o.y, o.r, o.color);
        orbs.splice(closest, 1);
        // Respawn a new orb from a random edge after 1s
        setTimeout(() => orbs.push(makeOrb()), 1000);
    });

    function tick() {
        ctx.clearRect(0, 0, W, H);

        // Move & bounce orbs
        for (const o of orbs) {
            o.x += o.vx; o.y += o.vy;
            if (o.x - o.r < 0)  { o.x = o.r;     o.vx *= -1; }
            if (o.x + o.r > W)  { o.x = W - o.r; o.vx *= -1; }
            if (o.y - o.r < 0)  { o.y = o.r;     o.vy *= -1; }
            if (o.y + o.r > H)  { o.y = H - o.r; o.vy *= -1; }
        }
        for (let i = 0; i < orbs.length; i++)
            for (let j = i+1; j < orbs.length; j++)
                resolveCollision(orbs[i], orbs[j]);

        // Draw orbs
        for (const o of orbs) {
            ctx.beginPath();
            ctx.arc(o.x, o.y, o.r, 0, Math.PI*2);
            ctx.fillStyle = o.color;
            ctx.fill();
        }

        // Update & draw burst particles
        particles = particles.filter(p => p.alpha > 0.02);
        for (const p of particles) {
            p.x += p.vx; p.y += p.vy;
            p.vy += 0.05; // slight gravity
            p.alpha *= 0.93;
            p.r *= 0.97;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
            ctx.fillStyle = p.color.replace(/[\d.]+\)$/, `${p.alpha})`);
            ctx.fill();
        }

        requestAnimationFrame(tick);
    }

    window.addEventListener('resize', resize);
    init(); tick();
}

// ── DOMContentLoaded init ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initOrbs();
    enhanceCards();
    applyFilters(); // initial sort (newest first)

    // Display actual numbers immediately (no animation)
    const numbers = document.querySelectorAll('.stat-number[data-target]');
    numbers.forEach(el => {
        const target = parseInt(el.dataset.target, 10);
        el.textContent = target;
    });

    // Auto-open celebrate modal on upload success
    const cel = document.getElementById('celebrateModal');
    if (cel && cel.dataset.autoopen === '1') {
        openModal('celebrateModal');
        launchConfetti();
        mcSound.celebrate();
        // Scroll to new card after 3s
        const newId = cel.dataset.newid;
        if (newId) {
            setTimeout(() => {
                const card = document.querySelector(`.app-card[data-id="${newId}"]`);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.style.outline = '2px solid #4facfe';
                    setTimeout(() => { card.style.outline = ''; }, 2500);
                }
            }, 3000);
        }
    }
});

// Resize confetti & stars canvas
window.addEventListener('resize', () => {
    const cc = document.getElementById('confetti-canvas');
    if (cc) { cc.width = window.innerWidth; cc.height = window.innerHeight; }
});

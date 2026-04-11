<?php
/**
 * includes/tutorial-header.php
 * <head>, CSS, background deco, nav with XP bar
 */
?>
<!DOCTYPE html>
<html lang="zh-CN" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="pageTitle">🎮 AI 游戏创作教程 — MG APP Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* ══════════════════════════════════════════════════════
           Tutorial Page — Gamified UI  v3
           ══════════════════════════════════════════════════════ */

        .top-nav { flex-direction: column; height: auto; padding: 0; }
        .nav-top-row {
            width: 100%; display: flex; align-items: center;
            justify-content: space-between; padding: 0 1.5rem; height: 56px; gap: 0.75rem;
        }
        .nav-xp-row { width: 100%; padding: 0 1.5rem 0.55rem; display: flex; align-items: center; gap: 0.75rem; }
        .nav-xp-label { font-size: 0.75rem; font-weight: 800; color: rgba(255,255,255,0.7); white-space: nowrap; letter-spacing: 0.3px; }
        .nav-xp-track { flex: 1; height: 8px; background: rgba(255,255,255,0.18); border-radius: 50px; overflow: hidden; }
        .nav-xp-fill {
            height: 100%; background: linear-gradient(90deg, var(--gold), #ff9500);
            border-radius: 50px; transition: width 0.8s cubic-bezier(.34,1.56,.64,1);
            box-shadow: 0 0 8px rgba(255,215,0,0.55); position: relative;
        }
        .nav-xp-fill::after {
            content:''; position:absolute; top:0; left:0; right:0; bottom:0;
            background: linear-gradient(90deg,transparent 0%,rgba(255,255,255,0.4) 50%,transparent 100%);
            animation: xp-shine 2.5s infinite; border-radius:50px;
        }
        .nav-xp-count { font-size: 0.8rem; font-weight: 900; color: var(--gold); white-space: nowrap; }

        .btn-lang {
            padding: 0.35rem 0.8rem; background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3); border-radius: 50px; color: #fff;
            font-size: 0.8rem; font-weight: 800; font-family: inherit; cursor: pointer;
            transition: all 0.15s; white-space: nowrap;
        }
        .btn-lang:hover { background: rgba(255,255,255,0.28); border-color: rgba(255,255,255,0.6); }

        .tut-hero { text-align: center; padding: 2rem 1rem 0.8rem; }
        .tut-hero h1 { font-size: clamp(1.5rem, 4.2vw, 2.5rem); font-weight: 900; color: #fff; line-height: 1.2; }
        .tut-hero h1 span { color: var(--gold); }
        .tut-hero p { margin-top: 0.5rem; font-size: 1rem; color: rgba(255,255,255,0.82); font-weight: 600; }

        .slogan-wrap { margin-top: 0.7rem; min-height: 2.2em; display: flex; flex-direction: column; align-items: center; gap: 0.25rem; }
        .slogan-main { font-size: clamp(0.95rem, 2.5vw, 1.1rem); font-weight: 800; color: rgba(255,255,255,0.95); transition: opacity 0.4s; }
        .slogan-sub { font-size: 0.82rem; color: rgba(255,255,255,0.6); font-weight: 600; }

        .hearts-row { display: flex; align-items: center; justify-content: center; gap: 0.3rem; margin-top: 0.6rem; }
        .heart { font-size: 1.3rem; transition: transform 0.3s, filter 0.3s; display: inline-block; }
        .heart.lost { filter: grayscale(1) opacity(0.35); }
        .heart.bounce { animation: heart-bounce 0.45s cubic-bezier(.34,1.56,.64,1); }
        @keyframes heart-bounce { 0%{transform:scale(1)} 40%{transform:scale(1.5) rotate(-10deg)} 70%{transform:scale(0.9)} 100%{transform:scale(1)} }
        .heart.lose-anim { animation: heart-lose 0.4s ease-in forwards; }
        @keyframes heart-lose { 0%{transform:scale(1);opacity:1} 50%{transform:scale(1.3) translateY(-6px)} 100%{transform:scale(0.7);opacity:0.3} }

        .xp-bar-wrap { display: none; }

        .badges-row { max-width: 860px; margin: 0.8rem auto 1.6rem; padding: 0 1.2rem; display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center; }
        .badge-chip { display: flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.75rem; border-radius: 50px; font-size: 0.82rem; font-weight: 700; transition: all 0.3s; cursor: default; }
        .badge-chip.locked { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.1); }
        .badge-chip.locked .badge-icon { filter: grayscale(1); opacity: 0.35; }
        .badge-chip.unlocked { background: rgba(255,215,0,0.18); color: #fff; border: 2px solid rgba(255,215,0,0.5); box-shadow: 0 0 12px rgba(255,215,0,0.25); }
        .badge-chip.unlocked .badge-icon { filter: none; }
        .badge-chip.pop { animation: badge-pop 0.5s cubic-bezier(.34,1.56,.64,1); }
        @keyframes badge-pop { 0%{transform:scale(0.5);opacity:0} 100%{transform:scale(1);opacity:1} }
        .badge-icon { font-size: 1.1rem; }

        .levels-container { max-width: 860px; margin: 0 auto; padding: 0 1.2rem 4rem; display: flex; flex-direction: column; gap: 1.5rem; }

        .level-card { border-radius: 20px; overflow: hidden; background: rgba(255,255,255,0.97); color: var(--text-dark); box-shadow: 0 8px 32px rgba(0,0,0,0.18); transition: transform 0.25s, box-shadow 0.25s; position: relative; }
        .level-card.locked-card { opacity: 0.55; filter: grayscale(0.4); pointer-events: none; }
        .level-card.active-card { box-shadow: 0 0 0 3px var(--blue), 0 12px 40px rgba(24,160,251,0.35); animation: pulse-border 2.5s infinite; }
        @keyframes pulse-border { 0%,100%{box-shadow:0 0 0 3px var(--blue),0 12px 40px rgba(24,160,251,0.25)} 50%{box-shadow:0 0 0 5px var(--blue),0 16px 50px rgba(24,160,251,0.4)} }
        .level-card.done-card { box-shadow: 0 0 0 3px var(--green), 0 12px 40px rgba(25,206,96,0.25); }
        .level-card:not(.locked-card):hover { transform: translateY(-3px); box-shadow: 0 14px 44px rgba(0,0,0,0.24); }
        .level-card.active-card:not(.locked-card):hover { box-shadow: 0 0 0 3px var(--blue), 0 18px 52px rgba(24,160,251,0.4); }

        .level-header { display: flex; align-items: center; gap: 1rem; padding: 1.2rem 1.5rem; border-bottom: 2px solid rgba(0,0,0,0.06); cursor: pointer; user-select: none; }
        .level-dot { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; font-weight: 900; }
        .level-dot.green  { background: linear-gradient(135deg, #19CE60, #0da84d); }
        .level-dot.yellow { background: linear-gradient(135deg, #FFD700, #e6a000); }
        .level-dot.orange { background: linear-gradient(135deg, #FF9500, #e07000); }
        .level-dot.red    { background: linear-gradient(135deg, #FF4757, #cc2233); }
        .level-dot.done   { background: linear-gradient(135deg, #19CE60, #0da84d); }
        .level-dot.locked { background: #ddd; }
        .level-meta { flex: 1; }
        .level-num { font-size: 0.75rem; font-weight: 700; color: #aaa; text-transform: uppercase; letter-spacing: 0.8px; }
        .level-title { font-size: 1.05rem; font-weight: 800; color: var(--text-dark); margin-top: 0.1rem; }
        .level-xp { display: flex; align-items: center; gap: 0.3rem; font-size: 0.88rem; font-weight: 800; color: #e6a000; background: rgba(255,215,0,0.12); padding: 0.3rem 0.7rem; border-radius: 50px; }
        .level-status { font-size: 1.3rem; flex-shrink: 0; }
        .level-chevron { color: #ccc; font-size: 0.9rem; transition: transform 0.3s; flex-shrink: 0; }
        .level-card.expanded .level-chevron { transform: rotate(180deg); }

        .level-body { display: none; padding: 1.5rem; }
        .level-card.expanded .level-body { display: block; }

        .section-label { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #aaa; margin-bottom: 0.6rem; margin-top: 1.4rem; }
        .section-label:first-child { margin-top: 0; }

        .prompt-card { background: #1a1a2e; border-radius: 14px; padding: 1.2rem; position: relative; margin-bottom: 1rem; }
        .prompt-text { font-family: 'Courier New', 'Consolas', monospace; font-size: 0.88rem; color: #e8f4fc; line-height: 1.7; white-space: pre-wrap; word-break: break-word; }
        .prompt-text .fill-placeholder { color: var(--gold); font-weight: 700; }
        .btn-copy-prompt { position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: rgba(255,255,255,0.7); font-size: 0.78rem; font-weight: 700; font-family: inherit; padding: 0.3rem 0.7rem; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
        .btn-copy-prompt:hover { background: rgba(255,255,255,0.2); color: #fff; }
        .btn-copy-prompt.copied { background: rgba(25,206,96,0.3); border-color: var(--green); color: var(--green); }

        .inspire-box { background: rgba(24,160,251,0.06); border: 2px solid rgba(24,160,251,0.15); border-radius: 14px; padding: 1.1rem 1.2rem; margin-bottom: 1rem; }
        .inspire-title { font-size: 0.88rem; font-weight: 800; color: var(--blue-dark); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
        .choices-grid { display: flex; flex-wrap: wrap; gap: 0.55rem; }
        .choice-btn { padding: 0.45rem 1rem; border: 2px solid rgba(24,160,251,0.35); border-radius: 50px; background: #fff; color: var(--blue-dark); font-size: 0.85rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: all 0.18s; }
        .choice-btn:hover { background: var(--blue); color: #fff; border-color: var(--blue); transform: scale(1.04); }
        .choice-btn.selected { background: var(--blue); color: #fff; border-color: var(--blue); box-shadow: 0 4px 14px rgba(24,160,251,0.4); }
        .custom-input-wrap { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.6rem; }
        .custom-input { flex: 1; border: 2px solid rgba(24,160,251,0.3); border-radius: 50px; padding: 0.4rem 1rem; font-size: 0.85rem; font-family: inherit; font-weight: 600; color: var(--text-dark); outline: none; transition: border-color 0.2s; }
        .custom-input:focus { border-color: var(--blue); }
        .custom-input::placeholder { color: #bbb; }

        .debug-cards { display: flex; flex-direction: column; gap: 1rem; }
        .debug-card { border-radius: 12px; border: 2px solid rgba(255,71,87,0.2); overflow: hidden; }
        .debug-card-header { background: rgba(255,71,87,0.08); padding: 0.65rem 1rem; font-size: 0.88rem; font-weight: 800; color: var(--red); display: flex; align-items: center; gap: 0.45rem; }
        .debug-card .prompt-card { margin: 0; border-radius: 0 0 12px 12px; }

        .formula-card { background: linear-gradient(135deg, rgba(167,139,250,0.12), rgba(24,160,251,0.1)); border: 2px solid rgba(167,139,250,0.3); border-radius: 14px; padding: 1.1rem 1.2rem; margin-bottom: 1rem; }
        .formula-title { font-size: 0.85rem; font-weight: 800; color: var(--purple); margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.4rem; }
        .formula-text { font-family: 'Courier New', monospace; font-size: 0.85rem; color: var(--text-dark); line-height: 1.9; }
        .formula-text span { color: var(--blue-dark); font-weight: 700; }

        .learn-tag { display: inline-flex; align-items: center; gap: 0.35rem; background: rgba(25,206,96,0.1); border: 2px solid rgba(25,206,96,0.25); border-radius: 50px; padding: 0.35rem 0.85rem; font-size: 0.82rem; font-weight: 700; color: #0da84d; margin-bottom: 1.2rem; }

        .btn-complete-wrap { margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .btn-complete { padding: 0.75rem 2rem; background: linear-gradient(135deg, var(--green), #0da84d); color: #fff; border: none; border-radius: 50px; font-size: 1rem; font-weight: 800; font-family: inherit; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 16px rgba(25,206,96,0.4); display: flex; align-items: center; gap: 0.5rem; }
        .btn-complete:hover { transform: scale(1.06); box-shadow: 0 6px 24px rgba(25,206,96,0.55); }
        .btn-complete:active { transform: scale(0.98); }
        .btn-complete.already-done { background: linear-gradient(135deg, #aaa, #888); box-shadow: none; cursor: default; }
        .btn-complete.already-done:hover { transform: none; }
        .xp-preview { font-size: 0.9rem; font-weight: 700; color: #aaa; }
        .xp-preview span { color: #e6a000; }

        .xp-popup { position: fixed; font-size: 1.6rem; font-weight: 900; color: var(--gold); text-shadow: 0 2px 12px rgba(0,0,0,0.4); pointer-events: none; z-index: 9999; animation: xp-float 1.4s ease-out forwards; }
        @keyframes xp-float { 0%{opacity:1;transform:translateY(0) scale(1)} 80%{opacity:1;transform:translateY(-80px) scale(1.2)} 100%{opacity:0;transform:translateY(-110px) scale(0.9)} }

        .completion-banner { display: none; max-width: 860px; margin: 0 auto 2rem; padding: 0 1.2rem; }
        .completion-banner.show { display: block; }
        .completion-inner { background: linear-gradient(135deg, #002B50, #0a5fa0); border-radius: 20px; padding: 2rem; text-align: center; border: 2px solid rgba(255,215,0,0.4); box-shadow: 0 0 40px rgba(255,215,0,0.2); }
        .completion-inner h2 { font-size: 2rem; color: var(--gold); margin-bottom: 0.5rem; }
        .completion-inner p { color: rgba(255,255,255,0.8); font-size: 1.05rem; margin-bottom: 1.2rem; }
        .title-badge { display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,215,0,0.15); border: 2px solid rgba(255,215,0,0.6); border-radius: 50px; padding: 0.6rem 1.5rem; font-size: 1.1rem; font-weight: 900; color: var(--gold); margin-bottom: 1.2rem; }
        .btn-store { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.8rem; background: var(--green); color: #002B50; border: none; border-radius: 50px; font-size: 1rem; font-weight: 800; font-family: inherit; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .btn-store:hover { background: var(--green-dark); transform: scale(1.06); }

        .btn-reset { display: flex; align-items: center; gap: 0.4rem; padding: 0.4rem 1rem; background: transparent; border: 2px solid rgba(255,255,255,0.2); border-radius: 50px; color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: all 0.2s; margin: 0.5rem auto 0; }
        .btn-reset:hover { border-color: rgba(255,71,87,0.5); color: var(--red); }

        #confetti-canvas { position: fixed; inset: 0; pointer-events: none; z-index: 9998; display: none; }
        #confetti-canvas.active { display: block; }

        .level-connector { display: flex; justify-content: center; align-items: center; height: 24px; margin: -0.5rem 0; position: relative; z-index: 0; }
        .level-connector::before { content: ''; width: 3px; height: 100%; background: rgba(255,255,255,0.2); border-radius: 3px; }

        .btn-copy-prompt.copy-bounce { animation: copy-bounce 0.3s cubic-bezier(.34,1.56,.64,1); }
        @keyframes copy-bounce { 0%{transform:scale(1)} 50%{transform:scale(1.15)} 100%{transform:scale(1)} }
        .choice-btn { position: relative; overflow: hidden; }
        .ripple { position:absolute; border-radius:50%; background:rgba(255,255,255,0.5); transform:scale(0); animation:ripple-anim 0.5s linear; pointer-events:none; }
        @keyframes ripple-anim { to{transform:scale(4);opacity:0} }

        .streak-chip { display: inline-flex; align-items: center; gap: 0.3rem; background: rgba(255,100,0,0.18); border: 2px solid rgba(255,120,0,0.5); border-radius: 50px; padding: 0.25rem 0.85rem; font-size: 0.85rem; font-weight: 800; color: #ff8c00; animation: streak-pulse 1.5s infinite; }
        @keyframes streak-pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.8;transform:scale(1.04)} }

        .encourage-toast { position: fixed; bottom: 1.5rem; right: 1.5rem; background: linear-gradient(135deg, #002B50, #0a5fa0); border: 2px solid rgba(255,215,0,0.4); border-radius: 16px; padding: 0.85rem 1.2rem; color: #fff; font-size: 0.95rem; font-weight: 800; box-shadow: 0 8px 32px rgba(0,0,0,0.3); z-index: 9997; max-width: 300px; animation: toast-in 0.35s cubic-bezier(.34,1.56,.64,1); pointer-events: none; }
        @keyframes toast-in { from{transform:translateY(80px) scale(0.8);opacity:0} to{transform:translateY(0) scale(1);opacity:1} }
        .toast-out { animation: toast-out 0.3s ease-in forwards; }
        @keyframes toast-out { to{transform:translateY(30px);opacity:0} }

        @keyframes xp-shine { 0%{transform:translateX(-100%)} 100%{transform:translateX(200%)} }

        @media (max-width: 600px) {
            .levels-container { padding: 0 0.75rem 3rem; }
            .level-body { padding: 1rem; }
            .btn-complete-wrap { flex-direction: column; align-items: stretch; }
            .btn-complete { justify-content: center; }
            .badges-row { gap: 0.4rem; }
            .badge-chip { font-size: 0.75rem; padding: 0.25rem 0.6rem; }
        }
    </style>
</head>
<body>

<div class="bg-deco" aria-hidden="true">
    <div class="bg-shape"></div><div class="bg-shape"></div>
    <div class="bg-shape"></div><div class="bg-shape"></div>
    <div class="bg-cross"></div><div class="bg-cross"></div><div class="bg-cross"></div>
    <div class="bg-dot"></div><div class="bg-dot"></div><div class="bg-dot"></div><div class="bg-dot"></div>
    <div class="bg-ring"></div><div class="bg-ring"></div>
    <div class="bg-star"></div><div class="bg-star"></div>
</div>

<canvas id="confetti-canvas" aria-hidden="true"></canvas>

<nav class="top-nav" id="topNav">
    <div class="nav-top-row">
        <a class="nav-brand" href="index.php">MG <span>APP</span> Store</a>
        <div class="nav-right">
            <button class="btn-lang" id="btnLang" onclick="toggleLang()">EN</button>
            <a class="btn-ghost" href="index.php" id="navBack">← 返回商店</a>
        </div>
    </div>
    <div class="nav-xp-row">
        <span class="nav-xp-label" id="navXpLabel">⚡ XP</span>
        <div class="nav-xp-track">
            <div class="nav-xp-fill" id="xpBarFill" style="width:0%"></div>
        </div>
        <span class="nav-xp-count" id="xpCount">0 / 160</span>
    </div>
</nav>

<section class="tut-hero">
    <h1 id="heroTitle">🎮 你的想象力，<span>AI</span> 来实现</h1>
    <div class="slogan-wrap">
        <div class="slogan-main" id="sloganMain">世界上只有一个你 — 做出只属于你的游戏！</div>
        <div class="slogan-sub" id="sloganSub">AI 是你的搭档，创意永远是你的 ✦ 7 关挑战 · 160 XP</div>
    </div>
    <div class="hearts-row" id="heartsRow">
        <span class="heart" id="heart-1">❤️</span>
        <span class="heart" id="heart-2">❤️</span>
        <span class="heart" id="heart-3">❤️</span>
        <span class="heart" id="heart-4">❤️</span>
        <span class="heart" id="heart-5">❤️</span>
    </div>
</section>

<div id="streakDisplay" style="max-width:860px;margin:0.3rem auto 0;padding:0 1.2rem;display:none;">
    <span class="streak-chip" id="streakChip">🔥 x1 连击！</span>
</div>

<div class="badges-row" id="badgesRow">
    <div class="badge-chip locked" id="badge-fire" title="完成 Level 1 解锁">
        <span class="badge-icon">🔥</span><span>点火者</span>
    </div>
    <div class="badge-chip locked" id="badge-creative" title="在 Level 3 填写自己的技能解锁">
        <span class="badge-icon">💡</span><span>创意家</span>
    </div>
    <div class="badge-chip locked" id="badge-debug" title="完成 Level 4 解锁">
        <span class="badge-icon">🔧</span><span>Debug 侠</span>
    </div>
    <div class="badge-chip locked" id="badge-giant" title="完成 Level 6 解锁">
        <span class="badge-icon">🌟</span><span>站在巨人肩上</span>
    </div>
    <div class="badge-chip locked" id="badge-creator" title="完成全部 7 关解锁">
        <span class="badge-icon">🎮</span><span>AI 游戏创作者</span>
    </div>
</div>

<div class="completion-banner" id="completionBanner">
    <div class="completion-inner">
        <div style="font-size:3rem;margin-bottom:0.5rem">🎉</div>
        <h2>恭喜通关！</h2>
        <p>你已经完成了所有 7 关挑战，获得了 160 XP！</p>
        <div class="title-badge">🎮 AI 游戏创作者</div>
        <br>
        <a href="index.php" class="btn-store">🚀 去 MG APP Store 分享你的游戏！</a>
    </div>
</div>

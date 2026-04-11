<?php
$metaFile = __DIR__ . '/apps/meta.json';
$viewsFile = __DIR__ . '/apps/views.json';
$apps = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];
$views = file_exists($viewsFile) ? json_decode(file_get_contents($viewsFile), true) : [];
if (!is_array($views)) $views = [];

usort($apps, function($a, $b) {
    return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
});

$uploadSuccess = isset($_GET['success']);
$uploadError = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Language: default zh
$lang = $_GET['lang'] ?? ($_COOKIE['mg_lang'] ?? 'zh');
if (!in_array($lang, ['zh', 'en'])) $lang = 'zh';
if (isset($_GET['lang'])) setcookie('mg_lang', $lang, time() + 86400 * 365, '/');

$t = [
    'zh' => [
        'title' => 'MG APP Store',
        'subtitle' => '上传你的 HTML 应用，与大家分享',
        'submit_btn' => '+ 提交作品',
        'open_app' => '打开应用',
        'empty_icon' => '📦',
        'empty_text' => '还没有作品，快来第一个提交吧！',
        'modal_title' => '提交作品',
        'label_author' => '作者姓名 *',
        'label_app_name' => '作品名称 *',
        'label_desc' => '作品简介',
        'label_html' => 'HTML 文件 *（仅 .html，最大 2MB）',
        'label_screenshot' => '截图（可选，.png/.jpg）',
        'hint_html' => '上传单页面 HTML 文件',
        'hint_screenshot' => '上传应用截图',
        'hint_desc' => '简要描述你的作品（最多 200 字）',
        'ph_author' => '你的名字',
        'ph_app_name' => '作品名称',
        'ph_desc' => '简单介绍一下你的作品...',
        'btn_submit' => '提交',
        'toast_success' => '🎉 作品提交成功！',
        'views' => '次访问',
        'play' => '开始玩',
        'by' => '作者',
        'card_desc' => '简介',
        'card_views' => '热度',
        'card_author' => '作者',
        'card_app_name' => '作品',
        'dup_warn' => '已存在相同作者和作品名的提交，确定要重复提交吗？',
    ],
    'en' => [
        'title' => 'MG APP Store',
        'subtitle' => 'Upload your HTML app and share it with the world',
        'submit_btn' => '+ Submit Your App',
        'open_app' => 'Open App',
        'empty_icon' => '📦',
        'empty_text' => 'No apps yet. Be the first to submit!',
        'modal_title' => 'Submit Your App',
        'label_author' => 'Author Name *',
        'label_app_name' => 'App Name *',
        'label_desc' => 'Description',
        'label_html' => 'HTML File * (.html only, max 2MB)',
        'label_screenshot' => 'Screenshot (optional, .png/.jpg)',
        'hint_html' => 'Upload a single-page HTML file',
        'hint_screenshot' => 'Upload a screenshot of your app',
        'hint_desc' => 'Briefly describe your app (max 200 chars)',
        'ph_author' => 'Your name',
        'ph_app_name' => 'Name of your app',
        'ph_desc' => 'Tell us about your app...',
        'btn_submit' => 'Submit',
        'toast_success' => '🎉 App submitted successfully!',
        'views' => 'plays',
        'play' => 'Play',
        'by' => 'by',
        'card_desc' => 'About',
        'card_views' => 'Plays',
        'card_author' => 'Author',
        'card_app_name' => 'App',
        'dup_warn' => 'An app with the same author and name already exists. Submit anyway?',
    ],
];
$L = $t[$lang];
$switchLang = $lang === 'zh' ? 'en' : 'zh';
$switchLabel = $lang === 'zh' ? 'EN' : '中文';

// Build existing apps list for JS duplicate check
$existingApps = [];
foreach ($apps as $app) {
    $existingApps[] = ['author' => $app['author'], 'title' => $app['title']];
}
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'zh' ? 'zh-CN' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $L['title'] ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Nunito', 'Segoe UI', system-ui, sans-serif;
            background: #18A0FB;
            min-height: 100vh;
            color: #fff;
            position: relative;
            overflow-x: hidden;
        }

        /* ===== DECORATIVE BACKGROUND ===== */
        .bg-deco {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .bg-deco::before {
            content: '';
            position: absolute;
            top: -120px;
            right: -80px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
            border-radius: 50%;
        }
        .bg-deco::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -60px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0,60,120,0.2) 0%, transparent 70%);
            border-radius: 50%;
        }
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            background: #fff;
        }
        .bg-shape:nth-child(1) { width: 300px; height: 300px; top: 10%; left: 5%; }
        .bg-shape:nth-child(2) { width: 180px; height: 180px; top: 50%; right: 8%; opacity: 0.06; }
        .bg-shape:nth-child(3) { width: 120px; height: 120px; top: 30%; right: 25%; opacity: 0.1; }
        .bg-shape:nth-child(4) { width: 220px; height: 220px; bottom: 15%; left: 30%; opacity: 0.05; }
        .bg-shape:nth-child(5) { width: 80px; height: 80px; top: 15%; right: 40%; opacity: 0.12; }

        .bg-cross, .bg-dot, .bg-ring, .bg-triangle, .bg-star {
            position: absolute;
            opacity: 0.15;
        }

        /* Crosses */
        .bg-cross::before, .bg-cross::after {
            content: '';
            position: absolute;
            background: #fff;
            border-radius: 3px;
        }
        .bg-cross::before { width: 4px; height: 22px; left: 9px; top: 0; }
        .bg-cross::after { width: 22px; height: 4px; left: 0; top: 9px; }
        .bg-cross { width: 22px; height: 22px; }
        .bg-cross:nth-of-type(1) { top: 12%; left: 15%; transform: rotate(15deg); }
        .bg-cross:nth-of-type(2) { top: 45%; right: 12%; transform: rotate(-20deg); opacity: 0.1; }
        .bg-cross:nth-of-type(3) { bottom: 20%; left: 8%; transform: rotate(35deg); opacity: 0.12; }
        .bg-cross:nth-of-type(4) { top: 70%; right: 30%; transform: rotate(10deg); opacity: 0.08; }

        /* Dots */
        .bg-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
        }
        .bg-dot:nth-of-type(1) { top: 20%; right: 18%; opacity: 0.2; }
        .bg-dot:nth-of-type(2) { top: 60%; left: 12%; width: 8px; height: 8px; opacity: 0.18; }
        .bg-dot:nth-of-type(3) { bottom: 30%; right: 22%; width: 10px; height: 10px; opacity: 0.14; }
        .bg-dot:nth-of-type(4) { top: 35%; left: 40%; width: 6px; height: 6px; opacity: 0.22; }
        .bg-dot:nth-of-type(5) { bottom: 12%; left: 55%; width: 14px; height: 14px; opacity: 0.1; }

        /* Rings */
        .bg-ring {
            border: 4px solid #fff;
            border-radius: 50%;
        }
        .bg-ring:nth-of-type(1) { width: 40px; height: 40px; top: 25%; right: 35%; opacity: 0.1; }
        .bg-ring:nth-of-type(2) { width: 28px; height: 28px; bottom: 25%; left: 20%; opacity: 0.12; }
        .bg-ring:nth-of-type(3) { width: 50px; height: 50px; top: 55%; left: 45%; opacity: 0.06; border-width: 5px; }

        /* Triangles */
        .bg-triangle {
            width: 0; height: 0;
            border-left: 14px solid transparent;
            border-right: 14px solid transparent;
            border-bottom: 24px solid #fff;
        }
        .bg-triangle:nth-of-type(1) { top: 18%; left: 50%; transform: rotate(20deg); opacity: 0.1; }
        .bg-triangle:nth-of-type(2) { bottom: 35%; right: 15%; transform: rotate(-15deg); opacity: 0.08; }
        .bg-triangle:nth-of-type(3) { top: 65%; left: 25%; transform: rotate(45deg); opacity: 0.12; border-left-width: 10px; border-right-width: 10px; border-bottom-width: 18px; }

        /* Stars (4-pointed via rotated squares) */
        .bg-star {
            width: 18px; height: 18px;
            background: #fff;
            transform: rotate(45deg);
            border-radius: 2px;
        }
        .bg-star:nth-of-type(1) { top: 8%; right: 25%; opacity: 0.15; }
        .bg-star:nth-of-type(2) { bottom: 18%; right: 40%; width: 12px; height: 12px; opacity: 0.1; }
        .bg-star:nth-of-type(3) { top: 42%; left: 8%; width: 14px; height: 14px; opacity: 0.13; }

        /* ===== NAVBAR ===== */
        .navbar {
            background: rgba(0, 50, 100, 0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .nav-left { display: flex; align-items: center; gap: 1.5rem; }
        .logo {
            font-size: 1.6rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .logo span { color: #FFD700; }
        .nav-subtitle {
            color: rgba(255,255,255,0.45);
            font-size: 0.85rem;
            font-weight: 600;
        }
        .nav-right { display: flex; align-items: center; gap: 0.8rem; }
        .btn-upload {
            padding: 0.55rem 1.4rem;
            background: #19CE60;
            color: #002B50;
            border: none;
            border-radius: 50px;
            font-size: 0.95rem;
            cursor: pointer;
            font-weight: 800;
            font-family: inherit;
            transition: all 0.15s;
        }
        .btn-upload:hover { background: #15b855; transform: scale(1.04); }
        .lang-switch {
            padding: 0.45rem 0.9rem;
            border-radius: 50px;
            border: 2px solid rgba(255,255,255,0.15);
            background: transparent;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
            transition: all 0.15s;
        }
        .lang-switch:hover { border-color: rgba(255,255,255,0.4); color: #fff; }

        /* ===== MAIN CONTENT ===== */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 1.5rem 2rem 3rem;
            position: relative;
            z-index: 1;
        }

        /* ===== GAMES GRID ===== */
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 1.2rem;
        }

        /* ===== GAME CARD (redesigned: info below image) ===== */
        .game-card {
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            background: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            text-decoration: none;
            color: #333;
            display: flex;
            flex-direction: column;
        }
        .game-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.25);
            z-index: 2;
        }

        .card-thumb-wrapper {
            position: relative;
            aspect-ratio: 16/10;
            overflow: hidden;
            background: #1580c7;
        }
        .game-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s;
        }
        .game-card:hover .game-thumb { transform: scale(1.06); }
        .placeholder-thumb {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            background: linear-gradient(135deg, #1580c7, #1070b0);
        }

        /* Hover overlay with play button */
        .card-overlay {
            position: absolute;
            inset: 0;
            background: rgba(10, 80, 150, 0.65);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .game-card:hover .card-overlay { opacity: 1; }

        .play-btn {
            width: 56px;
            height: 56px;
            background: #19CE60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(25, 206, 96, 0.4);
            transition: transform 0.15s;
        }
        .game-card:hover .play-btn { transform: scale(1.1); }
        .play-btn svg { width: 24px; height: 24px; fill: #fff; margin-left: 3px; }

        /* Card info section - white background below image */
        .card-info {
            padding: 0.8rem 1rem 1rem;
            background: #fff;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            min-height: 0;
        }
        .card-title {
            font-size: 1rem;
            font-weight: 800;
            color: #1a1a2e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }
        .card-field {
            display: flex;
            align-items: flex-start;
            gap: 0.3rem;
            font-size: 0.78rem;
            line-height: 1.4;
        }
        .card-field-label {
            color: #999;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .card-field-value {
            color: #555;
            font-weight: 600;
        }
        .card-desc-value {
            color: #777;
            font-weight: 600;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.45;
        }
        .card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 0.45rem;
            border-top: 1px solid #f0f0f0;
        }
        .card-author-info {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.78rem;
        }
        .card-author-info .label { color: #999; font-weight: 700; }
        .card-author-info .value { color: #18A0FB; font-weight: 700; }
        .card-views {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            color: #aaa;
            font-weight: 700;
        }
        .card-views svg { width: 14px; height: 14px; fill: #ccc; }
        .card-views .views-label { color: #bbb; margin-right: 0.1rem; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 5rem 2rem;
            color: rgba(255,255,255,0.35);
        }
        .empty-state .icon { font-size: 4rem; margin-bottom: 1rem; }
        .empty-state p { font-size: 1.1rem; font-weight: 700; }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(5,60,120,0.88);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #0d4a7a;
            border-radius: 20px;
            padding: 2rem;
            width: 90%;
            max-width: 480px;
            border: 1px solid rgba(255,255,255,0.08);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal h2 {
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            text-align: center;
            font-weight: 800;
        }
        .modal .close-btn {
            position: absolute;
            top: 0.8rem;
            right: 1rem;
            background: none;
            border: none;
            color: rgba(255,255,255,0.4);
            font-size: 1.6rem;
            cursor: pointer;
            transition: color 0.15s;
        }
        .modal .close-btn:hover { color: #fff; }

        .form-group { margin-bottom: 1.1rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.35rem;
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
            font-weight: 700;
        }
        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%;
            padding: 0.65rem 0.9rem;
            border-radius: 10px;
            border: 2px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            color: #fff;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.15s;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group input[type="text"]:focus,
        .form-group textarea:focus { outline: none; border-color: #19CE60; }
        .form-group .hint { font-size: 0.72rem; color: rgba(255,255,255,0.3); margin-top: 0.25rem; }

        /* Duplicate warning */
        .dup-warning {
            display: none;
            background: rgba(255, 193, 7, 0.15);
            border: 2px solid rgba(255, 193, 7, 0.5);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #ffc107;
            font-weight: 700;
            align-items: center;
            gap: 0.5rem;
        }
        .dup-warning.show { display: flex; }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: #19CE60;
            color: #002B50;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            cursor: pointer;
            font-weight: 800;
            font-family: inherit;
            transition: all 0.15s;
            margin-top: 0.3rem;
        }
        .btn-submit:hover { background: #15b855; transform: scale(1.02); }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ===== CELEBRATION OVERLAY ===== */
        .celebrate-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 3000;
            pointer-events: none;
        }
        .celebrate-overlay.active { display: block; }

        .celebrate-msg {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            z-index: 3001;
            background: #fff;
            border-radius: 24px;
            padding: 2.5rem 3rem;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: celebPop 0.5s ease forwards;
            pointer-events: auto;
        }
        .celebrate-msg h2 {
            color: #1a1a2e;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .celebrate-msg p {
            color: #666;
            font-size: 1rem;
        }
        .celebrate-msg .emoji {
            font-size: 4rem;
            margin-bottom: 0.5rem;
            display: block;
            animation: celebBounce 0.6s ease infinite alternate;
        }
        @keyframes celebPop {
            0% { transform: translate(-50%, -50%) scale(0); opacity: 0; }
            60% { transform: translate(-50%, -50%) scale(1.1); }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
        @keyframes celebBounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }

        /* Confetti canvas */
        #confettiCanvas {
            position: fixed;
            inset: 0;
            z-index: 2999;
            pointer-events: none;
        }

        /* ===== TOAST ===== */
        .toast {
            position: fixed;
            top: 80px;
            right: 2rem;
            padding: 0.8rem 1.4rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            z-index: 2000;
            animation: slideIn 0.3s ease, fadeOut 0.3s ease 2.7s forwards;
        }
        .toast.success { background: #19CE60; color: #002B50; }
        .toast.error { background: #ff4757; color: #fff; }

        @keyframes slideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .games-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
            .nav-subtitle { display: none; }
        }
        @media (max-width: 600px) {
            .navbar { padding: 0 1rem; height: 56px; }
            .logo { font-size: 1.3rem; }
            .container { padding: 1rem; }
            .games-grid { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
            .game-card { border-radius: 12px; }
            .card-info { padding: 0.6rem 0.7rem 0.8rem; }
            .card-title { font-size: 0.88rem; }
            .card-desc-value { -webkit-line-clamp: 2; }
            .play-btn { width: 44px; height: 44px; }
            .play-btn svg { width: 18px; height: 18px; }
            .celebrate-msg { padding: 1.5rem 2rem; }
            .celebrate-msg h2 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <!-- Decorative background shapes -->
    <div class="bg-deco">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>

        <div class="bg-cross"></div>
        <div class="bg-cross"></div>
        <div class="bg-cross"></div>
        <div class="bg-cross"></div>

        <div class="bg-dot"></div>
        <div class="bg-dot"></div>
        <div class="bg-dot"></div>
        <div class="bg-dot"></div>
        <div class="bg-dot"></div>

        <div class="bg-ring"></div>
        <div class="bg-ring"></div>
        <div class="bg-ring"></div>

        <div class="bg-triangle"></div>
        <div class="bg-triangle"></div>
        <div class="bg-triangle"></div>

        <div class="bg-star"></div>
        <div class="bg-star"></div>
        <div class="bg-star"></div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="logo">MG <span>APP</span> Store</div>
            <span class="nav-subtitle"><?= $L['subtitle'] ?></span>
        </div>
        <div class="nav-right">
            <button class="btn-upload" onclick="document.getElementById('uploadModal').classList.add('active')">
                <?= $L['submit_btn'] ?>
            </button>
            <a href="?lang=<?= $switchLang ?>" class="lang-switch"><?= $switchLabel ?></a>
        </div>
    </nav>

    <div class="container">
        <div class="games-grid">
            <?php if (empty($apps)): ?>
                <div class="empty-state">
                    <div class="icon"><?= $L['empty_icon'] ?></div>
                    <p><?= $L['empty_text'] ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($apps as $app): ?>
                    <?php $viewCount = $views[$app['id']] ?? 0; ?>
                    <a href="play.php?id=<?= urlencode($app['id']) ?>" target="_blank" class="game-card">
                        <!-- Thumbnail area -->
                        <div class="card-thumb-wrapper">
                            <?php if (!empty($app['screenshot']) && file_exists(__DIR__ . '/' . $app['screenshot'])): ?>
                                <img src="<?= htmlspecialchars($app['screenshot']) ?>" alt="<?= htmlspecialchars($app['title']) ?>" class="game-thumb">
                            <?php else: ?>
                                <div class="placeholder-thumb">🎮</div>
                            <?php endif; ?>

                            <!-- Hover overlay -->
                            <div class="card-overlay">
                                <div class="play-btn">
                                    <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Info section below image -->
                        <div class="card-info">
                            <div class="card-title"><?= htmlspecialchars($app['title']) ?></div>

                            <?php if (!empty($app['description'])): ?>
                                <div class="card-field">
                                    <span class="card-field-label"><?= $L['card_desc'] ?>:</span>
                                    <span class="card-desc-value"><?= htmlspecialchars($app['description']) ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="card-bottom">
                                <div class="card-author-info">
                                    <span class="label"><?= $L['card_author'] ?>:</span>
                                    <span class="value"><?= htmlspecialchars($app['author']) ?></span>
                                </div>
                                <div class="card-views">
                                    <span class="views-label"><?= $L['card_views'] ?>:</span>
                                    <svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                    <?= $viewCount ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal-overlay" id="uploadModal">
        <div class="modal">
            <button class="close-btn" onclick="document.getElementById('uploadModal').classList.remove('active')">&times;</button>
            <h2><?= $L['modal_title'] ?></h2>
            <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="form-group">
                    <label for="author"><?= $L['label_author'] ?></label>
                    <input type="text" id="author" name="author" required maxlength="50" placeholder="<?= $L['ph_author'] ?>">
                </div>
                <div class="form-group">
                    <label for="title"><?= $L['label_app_name'] ?></label>
                    <input type="text" id="title" name="title" required maxlength="100" placeholder="<?= $L['ph_app_name'] ?>">
                </div>
                <div class="dup-warning" id="dupWarning">
                    ⚠️ <?= $L['dup_warn'] ?>
                </div>
                <div class="form-group">
                    <label for="description"><?= $L['label_desc'] ?></label>
                    <textarea id="description" name="description" maxlength="200" rows="3" placeholder="<?= $L['ph_desc'] ?>"></textarea>
                    <p class="hint"><?= $L['hint_desc'] ?></p>
                </div>
                <div class="form-group">
                    <label for="htmlfile"><?= $L['label_html'] ?></label>
                    <input type="file" id="htmlfile" name="htmlfile" accept=".html" required>
                    <p class="hint"><?= $L['hint_html'] ?></p>
                </div>
                <div class="form-group">
                    <label for="screenshot"><?= $L['label_screenshot'] ?></label>
                    <input type="file" id="screenshot" name="screenshot" accept=".png,.jpg,.jpeg,.gif,.webp">
                    <p class="hint"><?= $L['hint_screenshot'] ?></p>
                </div>
                <input type="hidden" name="confirm_dup" id="confirmDup" value="">
                <button type="submit" class="btn-submit" id="submitBtn"><?= $L['btn_submit'] ?></button>
            </form>
        </div>
    </div>

    <!-- Confetti canvas -->
    <canvas id="confettiCanvas"></canvas>

    <!-- Celebration overlay -->
    <div class="celebrate-overlay" id="celebrateOverlay">
        <div class="celebrate-msg">
            <span class="emoji">🎉</span>
            <h2><?= $L['toast_success'] ?></h2>
            <p><?= $lang === 'zh' ? '太棒了！你的作品已成功发布！' : 'Awesome! Your app is now live!' ?></p>
        </div>
    </div>

    <?php if ($uploadError): ?>
        <div class="toast error"><?= $uploadError ?></div>
    <?php endif; ?>

    <script>
        // === Existing apps data for duplicate check ===
        var existingApps = <?= json_encode($existingApps, JSON_UNESCAPED_UNICODE) ?>;

        // === Modal close on backdrop ===
        document.getElementById('uploadModal').addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });

        // === Duplicate check ===
        var authorInput = document.getElementById('author');
        var titleInput = document.getElementById('title');
        var dupWarning = document.getElementById('dupWarning');

        function checkDuplicate() {
            var author = authorInput.value.trim().toLowerCase();
            var title = titleInput.value.trim().toLowerCase();
            if (!author || !title) {
                dupWarning.classList.remove('show');
                return;
            }
            var found = existingApps.some(function(app) {
                return app.author.toLowerCase() === author && app.title.toLowerCase() === title;
            });
            if (found) {
                dupWarning.classList.add('show');
            } else {
                dupWarning.classList.remove('show');
            }
        }

        authorInput.addEventListener('input', checkDuplicate);
        titleInput.addEventListener('input', checkDuplicate);

        // If duplicate warning is showing, confirm before submit
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            if (dupWarning.classList.contains('show')) {
                if (!confirm(dupWarning.textContent.trim())) {
                    e.preventDefault();
                } else {
                    document.getElementById('confirmDup').value = '1';
                }
            }
        });

        // === Confetti & Celebration ===
        <?php if ($uploadSuccess): ?>
        (function() {
            // Show celebration overlay
            var overlay = document.getElementById('celebrateOverlay');
            overlay.classList.add('active');

            // Play celebration sound using Web Audio API
            try {
                var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                // Play a cheerful sequence of tones
                var notes = [523, 659, 784, 1047]; // C5, E5, G5, C6
                notes.forEach(function(freq, i) {
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime + i * 0.15);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + i * 0.15 + 0.4);
                    osc.start(audioCtx.currentTime + i * 0.15);
                    osc.stop(audioCtx.currentTime + i * 0.15 + 0.4);
                });
                // Final chord
                setTimeout(function() {
                    [523, 659, 784, 1047].forEach(function(freq) {
                        var osc = audioCtx.createOscillator();
                        var gain = audioCtx.createGain();
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.type = 'triangle';
                        osc.frequency.value = freq;
                        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.8);
                        osc.start(audioCtx.currentTime);
                        osc.stop(audioCtx.currentTime + 0.8);
                    });
                }, 650);
            } catch (e) {}

            // Confetti animation
            var canvas = document.getElementById('confettiCanvas');
            var ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            var confetti = [];
            var colors = ['#ff6b6b', '#feca57', '#48dbfb', '#ff9ff3', '#54a0ff', '#5f27cd', '#01a3a4', '#f368e0', '#ff9f43', '#19CE60'];

            // Create confetti particles
            for (var i = 0; i < 150; i++) {
                confetti.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height - canvas.height,
                    w: Math.random() * 10 + 5,
                    h: Math.random() * 6 + 3,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    vx: (Math.random() - 0.5) * 4,
                    vy: Math.random() * 3 + 2,
                    rotation: Math.random() * 360,
                    rotSpeed: (Math.random() - 0.5) * 8,
                    opacity: 1
                });
            }

            var startTime = Date.now();
            function animateConfetti() {
                var elapsed = Date.now() - startTime;
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                var allDone = true;
                confetti.forEach(function(c) {
                    if (c.y < canvas.height + 50) allDone = false;
                    c.x += c.vx;
                    c.y += c.vy;
                    c.vy += 0.04; // gravity
                    c.rotation += c.rotSpeed;
                    if (elapsed > 2500) c.opacity = Math.max(0, c.opacity - 0.02);

                    ctx.save();
                    ctx.translate(c.x, c.y);
                    ctx.rotate(c.rotation * Math.PI / 180);
                    ctx.globalAlpha = c.opacity;
                    ctx.fillStyle = c.color;
                    ctx.fillRect(-c.w / 2, -c.h / 2, c.w, c.h);
                    ctx.restore();
                });

                if (elapsed < 4000 && !allDone) {
                    requestAnimationFrame(animateConfetti);
                } else {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
            }
            animateConfetti();

            // Auto-hide celebration after 4 seconds
            setTimeout(function() {
                overlay.classList.remove('active');
            }, 4000);
        })();
        <?php endif; ?>

        // Remove error toast
        setTimeout(function() { var t = document.querySelector('.toast'); if (t) t.remove(); }, 3000);
    </script>
</body>
</html>

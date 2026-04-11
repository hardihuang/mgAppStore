<?php
/**
 * includes/header.php  v5.0  — Poki-style theme
 */
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'zh' ? 'zh-CN' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($L['title']) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/share-card.css">
</head>
<body>

<canvas id="stars-canvas" aria-hidden="true"></canvas>
<canvas id="orbs-canvas" aria-hidden="true"></canvas>

<!-- ── Decorative background shapes ──────────────────────── -->
<div class="bg-deco" aria-hidden="true">
    <div class="bg-cross"></div><div class="bg-cross"></div><div class="bg-cross"></div>
    <div class="bg-dot"></div><div class="bg-dot"></div><div class="bg-dot"></div><div class="bg-dot"></div>
    <div class="bg-ring"></div><div class="bg-ring"></div>
    <div class="bg-star"></div><div class="bg-star"></div>
</div>


<!-- ── Top Nav ──────────────────────────────────────────── -->
<nav class="top-nav">
    <a class="nav-brand" href="index.php">MG <span>APP</span> Store</a>
    <div class="nav-right">
        <a class="btn-ghost" href="tutorial.html">🎓 教程</a>
        <a class="btn-ghost" href="?lang=<?= $switchLang ?>"><?= htmlspecialchars($switchLabel) ?></a>
        <button class="btn-primary" id="submitBtn"><?= htmlspecialchars($L['submit_btn']) ?></button>
    </div>
</nav>

<script>
    window.pageLang     = <?= json_encode($lang) ?>;
    window.viewsLabel   = <?= json_encode($L['views']) ?>;
    window.existingApps = <?= json_encode($existingApps, JSON_UNESCAPED_UNICODE) ?>;
    window.dupWarnMsg   = <?= json_encode($L['dup_warn']) ?>;
</script>

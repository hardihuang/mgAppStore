<?php
/**
 * edit.php - Let uploader edit their own app (token-based auth)
 * Supports: update title/author/desc/tag, replace screenshot, replace HTML, delete app
 */

$metaFile      = __DIR__ . '/apps/meta.json';
$viewsFile     = __DIR__ . '/apps/views.json';
$screenshotsDir = __DIR__ . '/screenshots';
$appsDir       = __DIR__ . '/apps';
$maxHtmlSize   = 2 * 1024 * 1024; // 2MB
$maxImgSize    = 5 * 1024 * 1024; // 5MB

$apps = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];
if (!is_array($apps)) $apps = [];

$id         = trim($_GET['id'] ?? $_POST['id'] ?? '');
$tokenParam = trim($_GET['token'] ?? $_POST['token'] ?? '');

// Check cookie tokens
$cookieTokens = [];
if (isset($_COOKIE['mg_edit_tokens'])) {
    $decoded = json_decode($_COOKIE['mg_edit_tokens'], true);
    if (is_array($decoded)) $cookieTokens = $decoded;
}
if ($tokenParam === '' && isset($cookieTokens[$id])) {
    $tokenParam = $cookieTokens[$id];
}

// Find app
$appIndex = null;
$app = null;
foreach ($apps as $i => $a) {
    if ($a['id'] === $id) { $appIndex = $i; $app = $a; break; }
}

if ($app === null) { header('Location: index.php?error=' . urlencode('App not found')); exit; }

$storedHash = $app['edit_token'] ?? '';
$tokenValid = ($storedHash !== '' && hash('sha256', $tokenParam) === $storedHash);
if (!$tokenValid) { header('Location: index.php?error=' . urlencode('No permission to edit this app')); exit; }

$validTags = ['action','puzzle','shooting','racing','platformer','casual','strategy','tool','other'];
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? 'save');

    // ── DELETE ──────────────────────────────────────────────────────────
    if ($action === 'delete') {
        // Remove files
        if (!empty($app['file']) && file_exists(__DIR__ . '/' . $app['file'])) {
            unlink(__DIR__ . '/' . $app['file']);
        }
        if (!empty($app['screenshot']) && file_exists(__DIR__ . '/' . $app['screenshot'])) {
            unlink(__DIR__ . '/' . $app['screenshot']);
        }
        // Remove from meta
        array_splice($apps, $appIndex, 1);
        file_put_contents($metaFile, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        // Remove views entry
        if (file_exists($viewsFile)) {
            $views = json_decode(file_get_contents($viewsFile), true) ?? [];
            unset($views[$id]);
            file_put_contents($viewsFile, json_encode($views, JSON_PRETTY_PRINT));
        }
        header('Location: index.php?msg=' . urlencode('游戏已删除'));
        exit;
    }

    // ── SAVE ────────────────────────────────────────────────────────────
    $newTitle  = mb_substr(trim($_POST['title']       ?? ''), 0, 100);
    $newAuthor = mb_substr(trim($_POST['author']      ?? ''), 0, 50);
    $newDesc   = mb_substr(trim($_POST['description'] ?? ''), 0, 200);
    $newTag    = trim($_POST['tag'] ?? 'other');
    if (!in_array($newTag, $validTags)) $newTag = 'other';

    if ($newTitle === '' || $newAuthor === '') {
        $error = '作品名称和作者不能为空';
    } else {
        $apps[$appIndex]['title']       = $newTitle;
        $apps[$appIndex]['author']      = $newAuthor;
        $apps[$appIndex]['description'] = $newDesc;
        $apps[$appIndex]['tag']         = $newTag;

        // Replace HTML file
        if (isset($_FILES['html_file']) && $_FILES['html_file']['error'] === UPLOAD_ERR_OK) {
            $hf  = $_FILES['html_file'];
            $ext = strtolower(pathinfo($hf['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['html','htm']) && $hf['size'] <= $maxHtmlSize) {
                $dest = __DIR__ . '/' . $app['file'];
                move_uploaded_file($hf['tmp_name'], $dest);
            } else {
                $error = 'HTML 文件格式不对或超过 2MB';
            }
        }

        // Replace screenshot
        if ($error === '' && isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $imgFile = $_FILES['screenshot'];
            $imgExt  = strtolower(pathinfo($imgFile['name'], PATHINFO_EXTENSION));
            if (in_array($imgExt, ['png','jpg','jpeg','gif','webp']) && $imgFile['size'] <= $maxImgSize) {
                $safeImgName = $id . '_edit_' . time() . '.' . $imgExt;
                $imgDest     = $screenshotsDir . '/' . $safeImgName;
                if (move_uploaded_file($imgFile['tmp_name'], $imgDest)) {
                    if (!empty($app['screenshot']) && file_exists(__DIR__ . '/' . $app['screenshot'])) {
                        unlink(__DIR__ . '/' . $app['screenshot']);
                    }
                    $apps[$appIndex]['screenshot'] = 'screenshots/' . $safeImgName;
                }
            } else {
                $error = '截图格式不对或超过 5MB';
            }
        }

        if ($error === '') {
            file_put_contents($metaFile, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $app     = $apps[$appIndex];
            $success = true;
        }
    }
}

$tagLabels = [
    'action'=>'动作','puzzle'=>'益智','shooting'=>'射击','racing'=>'赛车',
    'platformer'=>'横版','casual'=>'休闲','strategy'=>'策略','tool'=>'工具','other'=>'其他'
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑作品 - MG APP Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Nunito',system-ui,sans-serif;
            background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);
            min-height:100vh; color:#fff;
            display:flex; align-items:center; justify-content:center; padding:2rem 1rem;
        }
        .card {
            background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);
            border-radius:24px; padding:2.5rem; width:100%; max-width:520px;
            backdrop-filter:blur(20px);
        }
        .back-link {
            display:inline-flex; align-items:center; gap:0.4rem;
            color:rgba(255,255,255,0.5); text-decoration:none;
            font-size:0.85rem; font-weight:700; margin-bottom:1.5rem; transition:color 0.15s;
        }
        .back-link:hover { color:#fff; }
        h1 { font-size:1.6rem; font-weight:900; margin-bottom:0.3rem; }
        .subtitle { color:rgba(255,255,255,0.4); font-size:0.85rem; margin-bottom:2rem; }
        .form-group { margin-bottom:1.2rem; }
        label {
            display:block; font-size:0.82rem; font-weight:800;
            color:rgba(255,255,255,0.6); margin-bottom:0.4rem;
            text-transform:uppercase; letter-spacing:0.5px;
        }
        input[type="text"], textarea, select {
            width:100%; padding:0.75rem 1rem; border-radius:12px;
            border:2px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.05);
            color:#fff; font-size:0.95rem; font-family:inherit; font-weight:600;
            transition:border-color 0.15s;
        }
        input[type="text"]:focus, textarea:focus, select:focus { outline:none; border-color:#a78bfa; }
        select option { background:#1a1a2e; color:#fff; }
        textarea { resize:vertical; min-height:90px; }
        input[type="file"] {
            width:100%; padding:0.6rem; border-radius:12px;
            border:2px dashed rgba(255,255,255,0.15); background:rgba(255,255,255,0.03);
            color:rgba(255,255,255,0.6); font-family:inherit; font-size:0.85rem; cursor:pointer;
        }
        .current-screenshot {
            margin-top:0.6rem; border-radius:10px;
            max-width:180px; max-height:120px; object-fit:cover;
            border:2px solid rgba(255,255,255,0.1); display:block;
        }
        .btn-save {
            width:100%; padding:0.9rem;
            background:linear-gradient(135deg,#a78bfa,#7c3aed);
            color:#fff; border:none; border-radius:14px;
            font-size:1.05rem; font-weight:900; font-family:inherit;
            cursor:pointer; transition:all 0.15s; margin-top:0.5rem;
        }
        .btn-save:hover { transform:scale(1.02); opacity:0.9; }
        .btn-delete {
            width:100%; padding:0.75rem;
            background:rgba(239,68,68,0.12); border:2px solid rgba(239,68,68,0.35);
            color:#fca5a5; border-radius:14px;
            font-size:0.95rem; font-weight:900; font-family:inherit;
            cursor:pointer; transition:all 0.15s; margin-top:0.75rem;
        }
        .btn-delete:hover { background:rgba(239,68,68,0.25); border-color:rgba(239,68,68,0.6); }
        .divider { border:none; border-top:1px solid rgba(255,255,255,0.08); margin:1.5rem 0; }
        .section-label {
            font-size:0.78rem; font-weight:800; color:rgba(255,255,255,0.35);
            text-transform:uppercase; letter-spacing:1px; margin-bottom:1rem;
        }
        .alert { padding:0.8rem 1rem; border-radius:10px; font-size:0.9rem; font-weight:700; margin-bottom:1.2rem; }
        .alert-error  { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#fca5a5; }
        .alert-success{ background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.4); color:#86efac; }
        .hint { font-size:0.72rem; color:rgba(255,255,255,0.3); margin-top:0.3rem; }

        /* Delete confirm dialog */
        .confirm-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,0.7); z-index:999;
            align-items:center; justify-content:center;
        }
        .confirm-overlay.show { display:flex; }
        .confirm-box {
            background:#1a1a2e; border:1px solid rgba(239,68,68,0.4);
            border-radius:20px; padding:2rem; max-width:360px; width:90%; text-align:center;
        }
        .confirm-box h3 { font-size:1.2rem; margin-bottom:0.5rem; }
        .confirm-box p { color:rgba(255,255,255,0.5); font-size:0.88rem; margin-bottom:1.5rem; }
        .confirm-actions { display:flex; gap:0.75rem; }
        .confirm-actions button { flex:1; padding:0.75rem; border-radius:12px; font-family:inherit; font-weight:800; font-size:0.95rem; cursor:pointer; border:none; }
        .btn-cancel-del { background:rgba(255,255,255,0.08); color:#fff; }
        .btn-confirm-del { background:linear-gradient(135deg,#ef4444,#b91c1c); color:#fff; }
    </style>
</head>
<body>
    <div class="card">
        <a href="index.php" class="back-link">← 返回首页</a>
        <h1>✏️ 编辑作品</h1>
        <p class="subtitle">只有你能编辑自己刚上传的作品</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✅ 保存成功！<a href="index.php" style="color:#86efac;margin-left:0.5rem;">返回首页</a></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="id"     value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="token"  value="<?= htmlspecialchars($tokenParam) ?>">
            <input type="hidden" name="action" value="save" id="formAction">

            <p class="section-label">基本信息</p>

            <div class="form-group">
                <label>作品名称 *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($app['title']) ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label>作者姓名 *</label>
                <input type="text" name="author" value="<?= htmlspecialchars($app['author']) ?>" required maxlength="50">
            </div>
            <div class="form-group">
                <label>游戏类型</label>
                <select name="tag">
                    <?php foreach ($tagLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($app['tag'] ?? 'other') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>作品简介</label>
                <textarea name="description" maxlength="200"><?= htmlspecialchars($app['description'] ?? '') ?></textarea>
                <p class="hint">最多 200 字</p>
            </div>

            <hr class="divider">
            <p class="section-label">更新文件（可选）</p>

            <div class="form-group">
                <label>替换游戏 HTML</label>
                <input type="file" name="html_file" accept=".html,.htm">
                <p class="hint">最大 2MB，只支持 .html 文件</p>
            </div>
            <div class="form-group">
                <label>替换截图</label>
                <input type="file" name="screenshot" accept=".png,.jpg,.jpeg,.gif,.webp">
                <p class="hint">最大 5MB，支持 PNG / JPG / WebP</p>
                <?php if (!empty($app['screenshot']) && file_exists(__DIR__ . '/' . $app['screenshot'])): ?>
                    <img src="<?= htmlspecialchars($app['screenshot']) ?>?t=<?= time() ?>" class="current-screenshot" alt="当前截图">
                <?php else: ?>
                    <p class="hint" style="margin-top:0.5rem;">暂无截图</p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-save">💾 保存修改</button>

            <hr class="divider">
            <button type="button" class="btn-delete" onclick="showDeleteConfirm()">🗑️ 删除这个游戏</button>
        </form>
    </div>

    <!-- Delete confirm dialog -->
    <div class="confirm-overlay" id="confirmOverlay">
        <div class="confirm-box">
            <h3>⚠️ 确认删除？</h3>
            <p>删除后无法恢复，游戏文件和截图都会被永久删除。</p>
            <div class="confirm-actions">
                <button class="btn-cancel-del" onclick="hideDeleteConfirm()">取消</button>
                <button class="btn-confirm-del" onclick="submitDelete()">确认删除</button>
            </div>
        </div>
    </div>

    <script>
        function showDeleteConfirm() {
            document.getElementById('confirmOverlay').classList.add('show');
        }
        function hideDeleteConfirm() {
            document.getElementById('confirmOverlay').classList.remove('show');
        }
        function submitDelete() {
            document.getElementById('formAction').value = 'delete';
            document.getElementById('editForm').submit();
        }
    </script>
</body>
</html>

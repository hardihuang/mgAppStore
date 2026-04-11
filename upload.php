<?php
/**
 * upload.php - Handle HTML app file uploads
 */

$maxFileSize = 2 * 1024 * 1024;     // 2MB for HTML
$maxImgSize  = 5 * 1024 * 1024;     // 5MB for screenshots
$appsDir = __DIR__ . '/apps';
$screenshotsDir = __DIR__ . '/screenshots';
$metaFile = $appsDir . '/meta.json';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$author = trim($_POST['author'] ?? '');
$title = trim($_POST['app_name'] ?? $_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$tag = trim($_POST['tag'] ?? 'other');

$validTags = ['action', 'puzzle', 'shooting', 'racing', 'platformer', 'casual', 'strategy', 'tool', 'other'];
if (!in_array($tag, $validTags)) $tag = 'other';

if ($author === '' || $title === '') {
    header('Location: index.php?error=' . urlencode('Author and app name are required'));
    exit;
}

if (!isset($_FILES['html_file']) || $_FILES['html_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: index.php?error=' . urlencode('Please upload a valid HTML file'));
    exit;
}

$htmlFile = $_FILES['html_file'];
$ext = strtolower(pathinfo($htmlFile['name'], PATHINFO_EXTENSION));
if ($ext !== 'html' && $ext !== 'htm') {
    header('Location: index.php?error=' . urlencode('Only .html files are allowed'));
    exit;
}

if ($htmlFile['size'] > $maxFileSize) {
    header('Location: index.php?error=' . urlencode('File size exceeds 2MB limit'));
    exit;
}

// Server-side duplicate check
$apps = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];
if (!is_array($apps)) $apps = [];
$isDuplicate = false;
foreach ($apps as $existingApp) {
    if (mb_strtolower($existingApp['author']) === mb_strtolower($author)
        && mb_strtolower($existingApp['title']) === mb_strtolower($title)) {
        $isDuplicate = true;
        break;
    }
}
if ($isDuplicate && empty($_POST['confirm_dup'])) {
    header('Location: index.php?error=' . urlencode($title . ' by ' . $author . ' already exists'));
    exit;
}

$uniqueId = time() . '_' . bin2hex(random_bytes(4));
$safeHtmlName = $uniqueId . '.html';
$htmlDest = $appsDir . '/' . $safeHtmlName;

if (!move_uploaded_file($htmlFile['tmp_name'], $htmlDest)) {
    header('Location: index.php?error=' . urlencode('Failed to save file'));
    exit;
}

$screenshotPath = '';
if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
    $imgFile = $_FILES['screenshot'];
    $imgExt = strtolower(pathinfo($imgFile['name'], PATHINFO_EXTENSION));
    $allowedImgExts = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    if (in_array($imgExt, $allowedImgExts) && $imgFile['size'] <= $maxImgSize) {
        $safeImgName = $uniqueId . '.' . $imgExt;
        $imgDest = $screenshotsDir . '/' . $safeImgName;
        if (move_uploaded_file($imgFile['tmp_name'], $imgDest)) {
            $screenshotPath = 'screenshots/' . $safeImgName;
        }
    }
}

// Re-read in case of concurrent writes
$apps = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];
if (!is_array($apps)) $apps = [];

// Generate edit token
$editToken = bin2hex(random_bytes(16));

$apps[] = [
    'id'          => $uniqueId,
    'title'       => mb_substr($title, 0, 100),
    'author'      => mb_substr($author, 0, 50),
    'description' => mb_substr($description, 0, 200),
    'file'        => 'apps/' . $safeHtmlName,
    'screenshot'  => $screenshotPath,
    'timestamp'   => time(),
    'tag'         => $tag,
    'edit_token'  => hash('sha256', $editToken), // store hashed
];

file_put_contents($metaFile, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Store raw token in cookie (30 days), keyed by app id
$tokens = [];
if (isset($_COOKIE['mg_edit_tokens'])) {
    $decoded = json_decode($_COOKIE['mg_edit_tokens'], true);
    if (is_array($decoded)) $tokens = $decoded;
}
$tokens[$uniqueId] = $editToken;
setcookie('mg_edit_tokens', json_encode($tokens), time() + 86400 * 30, '/');

header('Location: index.php?success=1&new_id=' . urlencode($uniqueId));
exit;

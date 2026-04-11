<?php
/**
 * play.php — View counter + game launcher with analytics
 *
 * Modes:
 *   GET ?id=xxx          → increment view count, redirect to game file
 *   GET ?id=xxx&ajax=1   → increment view count, return JSON {ok:true}
 */

$metaFile      = __DIR__ . '/apps/meta.json';
$viewsFile     = __DIR__ . '/apps/views.json';
$analyticsFile = __DIR__ . '/apps/analytics.json';

$id   = trim($_GET['id'] ?? '');
$ajax = isset($_GET['ajax']);

if ($id === '') {
    if ($ajax) { header('Content-Type: application/json'); echo '{"ok":false,"error":"missing id"}'; exit; }
    header('Location: index.php');
    exit;
}

// Find the app
$apps       = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : [];
$targetFile = '';
foreach ($apps as $app) {
    if ($app['id'] === $id) {
        $targetFile = $app['file'];
        break;
    }
}

if ($targetFile === '' || !file_exists(__DIR__ . '/' . $targetFile)) {
    if ($ajax) { header('Content-Type: application/json'); echo '{"ok":false,"error":"not found"}'; exit; }
    header('Location: index.php');
    exit;
}

// Check if user has already viewed this game (Cookie-based anti-spam)
$viewedGames = [];
if (isset($_COOKIE['mg_viewed_games'])) {
    $viewedGames = json_decode($_COOKIE['mg_viewed_games'], true);
    if (!is_array($viewedGames)) $viewedGames = [];
}

$shouldCount = !in_array($id, $viewedGames);

// Get visitor info
$visitorIP    = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$visitorUA    = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$visitorTime  = time();

// Increment view count only if not viewed before (simple file lock via LOCK_EX)
$views = [];
$fp = fopen($viewsFile, file_exists($viewsFile) ? 'r+' : 'w+');
if ($fp && $shouldCount) {
    flock($fp, LOCK_EX);
    rewind($fp);
    $raw = stream_get_contents($fp);
    $views = json_decode($raw, true);
    if (!is_array($views)) $views = [];
    $views[$id] = ($views[$id] ?? 0) + 1;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($views, JSON_PRETTY_PRINT));
    flock($fp, LOCK_UN);
    fclose($fp);

    // Mark this game as viewed in cookie (expires in 30 days)
    $viewedGames[] = $id;
    setcookie('mg_viewed_games', json_encode($viewedGames), time() + 86400 * 30, '/');

    // Record detailed analytics (IP, timestamp, user agent)
    $analytics = [];
    $ap = fopen($analyticsFile, file_exists($analyticsFile) ? 'r+' : 'w+');
    if ($ap) {
        flock($ap, LOCK_EX);
        rewind($ap);
        $raw = stream_get_contents($ap);
        $analytics = json_decode($raw, true);
        if (!is_array($analytics)) $analytics = [];

        // Add new visit record
        if (!isset($analytics[$id])) $analytics[$id] = [];
        $analytics[$id][] = [
            'timestamp' => $visitorTime,
            'ip'        => $visitorIP,
            'ua'        => substr($visitorUA, 0, 200), // Limit UA length
        ];

        // Keep only last 500 records per game to avoid file bloat
        if (count($analytics[$id]) > 500) {
            $analytics[$id] = array_slice($analytics[$id], -500);
        }

        ftruncate($ap, 0);
        rewind($ap);
        fwrite($ap, json_encode($analytics, JSON_PRETTY_PRINT));
        flock($ap, LOCK_UN);
        fclose($ap);
    }
} else if ($fp) {
    // Just read current views without incrementing
    flock($fp, LOCK_SH);
    rewind($fp);
    $raw = stream_get_contents($fp);
    $views = json_decode($raw, true);
    if (!is_array($views)) $views = [];
    flock($fp, LOCK_UN);
    fclose($fp);
}

if ($ajax) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'views' => $views[$id] ?? 1]);
    exit;
}

// Normal redirect
header('Location: ' . $targetFile);
exit;

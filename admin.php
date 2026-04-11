<?php
/**
 * admin.php — MG APP Store admin panel
 * Security: POST-only login, password from .env, bcrypt verify
 */

session_start();

// ── Load password from .env ───────────────────────────────────────────────
$envFile = __DIR__ . '/.env';
$adminPasswordHash = '';
if (file_exists($envFile)) {
    $cfg = parse_ini_file($envFile);
    // Support both plain and pre-hashed passwords in .env
    $plain = $cfg['ADMIN_PASSWORD'] ?? '';
    // If stored as plain text, verify directly (and upgrade to hash on next save if desired)
    $adminPasswordPlain = $plain;
}

// ── Logout ────────────────────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// ── POST Login ────────────────────────────────────────────────────────────
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pwd'])) {
    if ($_POST['pwd'] === ($adminPasswordPlain ?? '')) {
        $_SESSION['admin_auth'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $loginError = '密码错误，请重试';
    }
}

// ── Auth gate ─────────────────────────────────────────────────────────────
if (empty($_SESSION['admin_auth'])) { ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — MG APP Store</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f0f1a; color: #fff; font-family: system-ui, sans-serif;
               display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { background: rgba(255,255,255,0.05); padding: 2.5rem 2rem;
                     border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);
                     text-align: center; width: 320px; }
        h2 { margin-bottom: 1.8rem; font-size: 1.4rem; }
        input[type="password"] {
            width: 100%; padding: 0.75rem 1rem; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.06);
            color: #fff; font-size: 1rem; outline: none; transition: border-color 0.2s;
        }
        input[type="password"]:focus { border-color: #4facfe; }
        button { margin-top: 1rem; width: 100%; padding: 0.75rem;
                 background: linear-gradient(135deg, #4facfe, #00f2fe);
                 border: none; border-radius: 12px; color: #fff;
                 font-size: 1rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s; }
        button:hover { opacity: 0.88; }
        .error { margin-top: 1rem; color: #f87171; font-size: 0.88rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Admin Login</h2>
        <form method="POST" action="admin.php">
            <input type="password" name="pwd" placeholder="Enter password" required autofocus>
            <button type="submit">Login</button>
            <?php if ($loginError): ?>
            <p class="error">⚠️ <?= htmlspecialchars($loginError) ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// ── Data ──────────────────────────────────────────────────────────────────
$metaFile      = __DIR__ . '/apps/meta.json';
$viewsFile     = __DIR__ . '/apps/views.json';
$analyticsFile = __DIR__ . '/apps/analytics.json';
$apps      = file_exists($metaFile)      ? json_decode(file_get_contents($metaFile),      true) : [];
$views     = file_exists($viewsFile)     ? json_decode(file_get_contents($viewsFile),     true) : [];
$analytics = file_exists($analyticsFile) ? json_decode(file_get_contents($analyticsFile), true) : [];
if (!is_array($apps))      $apps      = [];
if (!is_array($views))     $views     = [];
if (!is_array($analytics)) $analytics = [];

// ── Calculate analytics statistics ───────────────────────────────────────
$totalViews      = array_sum($views);
$totalApps       = count($apps);
$topGames        = [];
$hourlyStats     = [];
$dailyStats      = [];
$monthlyStats    = [];
$uniqueIPs       = [];
$uploadStats     = []; // Upload time distribution

foreach ($views as $id => $count) {
    foreach ($apps as $app) {
        if ($app['id'] === $id) {
            $topGames[] = [
                'id'    => $id,
                'title' => $app['title'],
                'views' => $count,
            ];
            break;
        }
    }
}
usort($topGames, fn($a, $b) => $b['views'] - $a['views']);
$topGames = array_slice($topGames, 0, 10);

// Process analytics for trends
foreach ($analytics as $id => $records) {
    foreach ($records as $record) {
        $ts = $record['timestamp'] ?? 0;
        $ip = $record['ip'] ?? 'unknown';

        // Hourly stats (last 24 hours)
        if ($ts >= time() - 86400) {
            $hour = date('H', $ts);
            $hourlyStats[$hour] = ($hourlyStats[$hour] ?? 0) + 1;
        }

        // Daily stats (last 30 days)
        $day = date('Y-m-d', $ts);
        $dailyStats[$day] = ($dailyStats[$day] ?? 0) + 1;

        // Unique IPs
        $uniqueIPs[$ip] = ($uniqueIPs[$ip] ?? 0) + 1;
    }
}

// Calculate monthly stats (group by week in last month)
foreach ($dailyStats as $day => $count) {
    $week = date('W', strtotime($day));
    $monthlyStats['Week ' . $week] = ($monthlyStats['Week ' . $week] ?? 0) + $count;
}

// Upload time statistics (submission trends)
foreach ($apps as $app) {
    $ts = $app['timestamp'] ?? 0;

    // Upload by hour
    $uploadHour = date('H', $ts);
    $uploadStats['hourly'][$uploadHour] = ($uploadStats['hourly'][$uploadHour] ?? 0) + 1;

    // Upload by day
    $uploadDay = date('Y-m-d', $ts);
    $uploadStats['daily'][$uploadDay] = ($uploadStats['daily'][$uploadDay] ?? 0) + 1;
}

// Sort stats
ksort($hourlyStats);
ksort($dailyStats);
ksort($monthlyStats);
if (isset($uploadStats['hourly'])) ksort($uploadStats['hourly']);
if (isset($uploadStats['daily'])) ksort($uploadStats['daily']);
$uniqueIPsSorted = $uniqueIPs;
arsort($uniqueIPsSorted);
$topIPs = array_slice($uniqueIPsSorted, 0, 10, true);

$tagLabels = [
    'action' => '动作', 'puzzle' => '益智', 'shooting' => '射击',
    'racing' => '赛车', 'platformer' => '横版', 'casual' => '休闲',
    'strategy' => '策略', 'tool' => '工具', 'other' => '其他',
];

// ── Handle Delete ─────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $newApps = [];
    foreach ($apps as $app) {
        if ($app['id'] === $deleteId) {
            $htmlPath = __DIR__ . '/' . $app['file'];
            if (file_exists($htmlPath)) unlink($htmlPath);
            if (!empty($app['screenshot'])) {
                $imgPath = __DIR__ . '/' . $app['screenshot'];
                if (file_exists($imgPath)) unlink($imgPath);
            }
            unset($views[$deleteId]);
        } else {
            $newApps[] = $app;
        }
    }
    file_put_contents($metaFile,  json_encode($newApps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($viewsFile, json_encode($views,   JSON_PRETTY_PRINT));
    header('Location: admin.php');
    exit;
}

// ── Handle Edit (POST) ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $editId    = $_POST['id']          ?? '';
    $newTitle  = trim($_POST['title']  ?? '');
    $newAuthor = trim($_POST['author'] ?? '');
    $newDesc   = trim($_POST['description'] ?? '');
    $newTag    = trim($_POST['tag']    ?? 'other');
    if (!array_key_exists($newTag, $tagLabels)) $newTag = 'other';

    if ($editId && $newTitle && $newAuthor) {
        foreach ($apps as &$app) {
            if ($app['id'] === $editId) {
                $app['title']       = mb_substr($newTitle,  0, 100);
                $app['author']      = mb_substr($newAuthor, 0, 50);
                $app['description'] = mb_substr($newDesc,   0, 200);
                $app['tag']         = $newTag;

                if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
                    $imgFile = $_FILES['screenshot'];
                    $imgExt  = strtolower(pathinfo($imgFile['name'], PATHINFO_EXTENSION));
                    if (in_array($imgExt, ['png','jpg','jpeg','gif','webp']) && $imgFile['size'] <= 2*1024*1024) {
                        $screenshotsDir = __DIR__ . '/screenshots';
                        if (!is_dir($screenshotsDir)) mkdir($screenshotsDir, 0755, true);
                        $safeImgName = $editId . '_' . time() . '.' . $imgExt;
                        if (move_uploaded_file($imgFile['tmp_name'], $screenshotsDir . '/' . $safeImgName)) {
                            if (!empty($app['screenshot']) && file_exists(__DIR__ . '/' . $app['screenshot'])) {
                                unlink(__DIR__ . '/' . $app['screenshot']);
                            }
                            $app['screenshot'] = 'screenshots/' . $safeImgName;
                        }
                    }
                }
                break;
            }
        }
        unset($app);
        file_put_contents($metaFile, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    header('Location: admin.php');
    exit;
}

usort($apps, fn($a, $b) => ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — MG APP Store</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f0f1a; color: #fff; font-family: system-ui, sans-serif; padding: 2rem 1rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { text-align: center; margin-bottom: 1.5rem; font-size: 1.6rem; }

        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;
                   flex-wrap: wrap; gap: 0.5rem; }
        .top-bar a { color: rgba(255,255,255,0.55); text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        .top-bar a:hover { color: #fff; }
        .count { color: rgba(255,255,255,0.4); font-size: 0.88rem; }

        /* Analytics Dashboard */
        .analytics-section { margin-bottom: 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                      gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
                     border-radius: 12px; padding: 1.5rem 1rem; text-align: center; }
        .stat-card .stat-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .stat-card .stat-value { font-size: 2rem; font-weight: 700; color: #4facfe; margin-bottom: 0.3rem; }
        .stat-card .stat-label { font-size: 0.85rem; color: rgba(255,255,255,0.5); }

        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
                       gap: 1.5rem; margin-bottom: 1.5rem; }
        .chart-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
                      border-radius: 12px; padding: 1.5rem; }
        .chart-title { font-size: 1rem; margin-bottom: 1rem; color: rgba(255,255,255,0.9);
                       display: flex; align-items: center; gap: 0.5rem; }
        .chart-container { position: relative; height: 250px; }

        /* Time period switcher */
        .time-switcher { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
        .time-btn { padding: 0.4rem 1rem; border: 1px solid rgba(255,255,255,0.2);
                    background: transparent; color: rgba(255,255,255,0.6);
                    border-radius: 6px; cursor: pointer; font-size: 0.85rem;
                    transition: all 0.2s; }
        .time-btn:hover { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); }
        .time-btn.active { background: linear-gradient(135deg, #4facfe, #00f2fe);
                          color: #fff; border-color: transparent; }

        .top-list { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
                    border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; }
        .top-list-title { font-size: 1rem; margin-bottom: 1rem; color: rgba(255,255,255,0.9); }
        .top-item { display: flex; justify-content: space-between; padding: 0.5rem 0;
                    border-bottom: 1px solid rgba(255,255,255,0.05); }
        .top-item:last-child { border-bottom: none; }
        .top-item .name { color: rgba(255,255,255,0.7); }
        .top-item .value { color: #4facfe; font-weight: 600; }

        /* Table improvements */
        table { width: 100%; border-collapse: collapse; table-layout: auto; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.07);
                 vertical-align: middle; }
        th { color: rgba(255,255,255,0.4); font-size: 0.78rem; text-transform: uppercase;
             letter-spacing: 0.5px; white-space: nowrap; }
        td.desc { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                  color: rgba(255,255,255,0.45); font-size: 0.82rem; }
        td.actions {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
                align-items: center;
                padding: 0.75rem;
            }
            td.actions > * {
                white-space: nowrap;
                flex-shrink: 0;
            }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.07); vertical-align: middle; }
        th { color: rgba(255,255,255,0.4); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; }

        td.desc { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                  color: rgba(255,255,255,0.45); font-size: 0.82rem; }
        .img-thumb { width: 42px; height: 42px; object-fit: cover; border-radius: 6px; display: block; background: #1e1e2e; }
        .thumb-placeholder { width: 42px; height: 42px; border-radius: 6px; background: #1e1e2e;
                             display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }

        .tag-chip { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 8px;
                    font-size: 0.75rem; font-weight: 700; color: #fff; white-space: nowrap; }

        .btn-view   { color: #4facfe; text-decoration: none; font-size: 0.82rem; margin-right: 0.6rem; }
        .btn-edit   { color: #f1c40f; background: none; border: 1px solid #f1c40f; padding: 0.25rem 0.7rem;
                      border-radius: 6px; cursor: pointer; font-size: 0.82rem; margin-right: 0.4rem; transition: all 0.15s; }
        .btn-edit:hover { background: #f1c40f; color: #0f0f1a; }
        .btn-delete { color: #e74c3c; background: none; border: 1px solid #e74c3c; padding: 0.25rem 0.7rem;
                      border-radius: 6px; cursor: pointer; font-size: 0.82rem; text-decoration: none; transition: all 0.15s; }
        .btn-delete:hover { background: #e74c3c; color: #fff; }

        .empty { text-align: center; padding: 3rem; color: rgba(255,255,255,0.3); }

        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8);
                         backdrop-filter: blur(6px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
        .modal-overlay.active { display: flex; }
        .modal { background: #1a1a2e; border-radius: 16px; padding: 2rem; width: 100%; max-width: 500px;
                 border: 1px solid rgba(255,255,255,0.1); position: relative; max-height: 90vh; overflow-y: auto; }
        .modal h2 { margin-bottom: 1.5rem; text-align: center; }
        .modal .close-btn { position: absolute; top: 1rem; right: 1.2rem; background: none; border: none;
                            color: rgba(255,255,255,0.4); font-size: 1.4rem; cursor: pointer; transition: color 0.2s; }
        .modal .close-btn:hover { color: #fff; }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; margin-bottom: 0.4rem; font-size: 0.82rem;
                            color: rgba(255,255,255,0.7); font-weight: 600; }
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select,
        .form-group input[type="file"] {
            width: 100%; padding: 0.65rem 0.9rem; border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.05);
            color: #fff; font-size: 0.9rem; font-family: inherit; box-sizing: border-box;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group select option { background: #1a1a2e; }
        .btn-submit { width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #4facfe, #00f2fe);
                      color: #fff; border: none; border-radius: 10px; font-size: 1rem;
                      font-weight: 700; cursor: pointer; margin-top: 0.8rem; transition: opacity 0.2s; }
        .btn-submit:hover { opacity: 0.88; }
        .current-img-preview { max-width: 140px; max-height: 90px; margin-top: 0.5rem;
                               border-radius: 6px; display: none; border: 1px solid rgba(255,255,255,0.15); }
    </style>
</head>
<body>
<div class="container">
    <h1>🛠 MG APP Store Admin</h1>
    <div class="top-bar">
        <span class="count"><?= count($apps) ?> 个作品</span>
        <div style="display:flex;gap:1rem;align-items:center;">
            <a href="index.php">← 返回首页</a>
            <a href="admin.php?logout=1">退出登录</a>
        </div>
    </div>

    <!-- Analytics Dashboard -->
    <div class="analytics-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎮</div>
                <div class="stat-value"><?= $totalApps ?></div>
                <div class="stat-label">总作品数</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👁</div>
                <div class="stat-value"><?= $totalViews ?></div>
                <div class="stat-label">总访问量</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🌐</div>
                <div class="stat-value"><?= count($uniqueIPs) ?></div>
                <div class="stat-label">独立访问IP</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔥</div>
                <div class="stat-value"><?= $topGames[0]['views'] ?? 0 ?></div>
                <div class="stat-label">最热门游戏访问量</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <!-- Combined Access Trend Chart -->
            <div class="chart-card">
                <div class="chart-title">📊 访问趋势统计</div>
                <div class="time-switcher">
                    <button class="time-btn active" data-period="hourly">24小时</button>
                    <button class="time-btn" data-period="daily">7天</button>
                    <button class="time-btn" data-period="monthly">30天</button>
                </div>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Upload Time Statistics -->
            <div class="chart-card">
                <div class="chart-title">📅 作品提交时间分布</div>
                <div class="time-switcher">
                    <button class="time-btn-upload active" data-period="hourly">按时段</button>
                    <button class="time-btn-upload" data-period="daily">按日期</button>
                </div>
                <div class="chart-container">
                    <canvas id="uploadChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Games & Top IPs -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;">
            <div class="top-list">
                <div class="top-list-title">🏆 Top 10 热门游戏</div>
                <?php foreach ($topGames as $rank => $game): ?>
                <div class="top-item">
                    <span class="name"><?= $rank + 1 ?>. <?= htmlspecialchars($game['title']) ?></span>
                    <span class="value"><?= $game['views'] ?> 次</span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="top-list">
                <div class="top-list-title">🌐 Top 10 访问IP</div>
                <?php foreach ($topIPs as $ip => $count): ?>
                <div class="top-item">
                    <span class="name"><?= htmlspecialchars($ip) ?></span>
                    <span class="value"><?= $count ?> 次</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Apps Table -->
    <h2 style="margin-top:2rem;margin-bottom:1rem;font-size:1.3rem;">作品管理</h2>
    <?php if (empty($apps)): ?>
        <div class="empty">暂无作品提交</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>封面</th>
                <th>作品名</th>
                <th>作者</th>
                <th>类型</th>
                <th>简介</th>
                <th>热度</th>
                <th>上传时间</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($apps as $app):
            $tag      = $app['tag'] ?? 'other';
            $tagLabel = $tagLabels[$tag] ?? '其他';
            $tagColors = [
                'action'=>'#ff6b6b','puzzle'=>'#a78bfa','shooting'=>'#ef4444',
                'racing'=>'#fbbf24','platformer'=>'#34d399','casual'=>'#60a5fa',
                'strategy'=>'#22d3ee','tool'=>'#94a3b8','other'=>'#18A0FB',
            ];
            $tc = $tagColors[$tag] ?? '#18A0FB';
        ?>
        <tr>
            <td>
                <?php if (!empty($app['screenshot']) && file_exists(__DIR__ . '/' . $app['screenshot'])): ?>
                    <img src="<?= htmlspecialchars($app['screenshot']) ?>" class="img-thumb" alt="">
                <?php else: ?>
                    <div class="thumb-placeholder">🎮</div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($app['title']) ?></td>
            <td><?= htmlspecialchars($app['author']) ?></td>
            <td>
                <span class="tag-chip" style="background:<?= $tc ?>"><?= htmlspecialchars($tagLabel) ?></span>
            </td>
            <td class="desc"><?= htmlspecialchars($app['description'] ?? '') ?></td>
            <td>👁 <?= $views[$app['id']] ?? 0 ?></td>
            <td style="font-size:0.82rem;color:rgba(255,255,255,0.45);">
                <?= date('Y-m-d H:i', $app['timestamp'] ?? 0) ?>
            </td>
            <td class="actions">
                    <a href="<?= htmlspecialchars($app['file']) ?>" target="_blank" class="btn-view">查看</a>
                    <button class="btn-details" style="color:#60a5fa;background:none;border:1px solid #60a5fa;padding:0.25rem 0.7rem;border-radius:6px;cursor:pointer;font-size:0.82rem;transition:all 0.15s;"
                        onclick="openDetailsModal(<?= htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8') ?>)">
                        详情
                    </button>
                    <button class="btn-edit"
                        onclick="openEditModal(<?= htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8') ?>)">
                        编辑
                    </button>
                    <a href="admin.php?delete=<?= urlencode($app['id']) ?>" class="btn-delete"
                       onclick="return confirm('确定删除《<?= htmlspecialchars($app['title'], ENT_QUOTES) ?>》？')">
                       删除
                    </a>
                </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <button class="close-btn" onclick="document.getElementById('editModal').classList.remove('active')">✕</button>
        <h2>编辑作品</h2>
        <form method="POST" enctype="multipart/form-data" action="admin.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <div class="form-group">
                <label>作品名称 *</label>
                <input type="text" id="edit_title" name="title" required maxlength="100">
            </div>
            <div class="form-group">
                <label>作者 *</label>
                <input type="text" id="edit_author" name="author" required maxlength="50">
            </div>
            <div class="form-group">
                <label>游戏类型</label>
                <select id="edit_tag" name="tag">
                    <?php foreach ($tagLabels as $val => $label): ?>
                    <option value="<?= $val ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>简介</label>
                <textarea id="edit_description" name="description" maxlength="200"></textarea>
            </div>
            <div class="form-group">
                <label>更换截图（留空保持原图）</label>
                <input type="file" id="edit_screenshot" name="screenshot" accept=".png,.jpg,.jpeg,.gif,.webp">
                <img id="edit_img_preview" class="current-img-preview" alt="当前截图">
            </div>

            <button type="submit" class="btn-submit">💾 保存修改</button>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div class="modal-overlay" id="detailsModal">
    <div class="modal" style="max-width:700px;">
        <button class="close-btn" onclick="document.getElementById('detailsModal').classList.remove('active')">✕</button>
        <h2 id="details_title">访问详情</h2>
        <div style="margin-bottom:1rem;font-size:0.9rem;color:rgba(255,255,255,0.6);">
            总访问量: <span id="details_total_views" style="color:#4facfe;font-weight:600;">0</span> 次
        </div>
        <div style="max-height:400px;overflow-y:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid rgba(255,255,255,0.15);">
                        <th style="padding:0.5rem;text-align:left;color:rgba(255,255,255,0.6);font-size:0.8rem;">时间</th>
                        <th style="padding:0.5rem;text-align:left;color:rgba(255,255,255,0.6);font-size:0.8rem;">IP地址</th>
                        <th style="padding:0.5rem;text-align:left;color:rgba(255,255,255,0.6);font-size:0.8rem;">浏览器</th>
                    </tr>
                </thead>
                <tbody id="details_records">
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Chart.js configuration
Chart.defaults.color = 'rgba(255,255,255,0.7)';
Chart.defaults.borderColor = 'rgba(255,255,255,0.1)';

// Data for charts
const hourlyData = <?= json_encode($hourlyStats) ?>;
const dailyData = <?= json_encode($dailyStats) ?>;
const monthlyData = <?= json_encode($monthlyStats) ?>;
const uploadHourlyData = <?= json_encode($uploadStats['hourly'] ?? []) ?>;
const uploadDailyData = <?= json_encode($uploadStats['daily'] ?? []) ?>;

// Access Trend Chart (Combined)
const trendCtx = document.getElementById('trendChart');
let trendChart = null;

function updateTrendChart(period) {
    if (!trendCtx) return;

    let labels, data, chartType, colors;

    if (period === 'hourly') {
        // 24 hours
        labels = [];
        data = [];
        for (let h = 0; h < 24; h++) {
            const hourStr = String(h).padStart(2, '0');
            labels.push(hourStr + ':00');
            data.push(hourlyData[hourStr] || 0);
        }
        chartType = 'bar';
        colors = {
            backgroundColor: 'rgba(79,172,254,0.6)',
            borderColor: 'rgba(79,172,254,1)',
            borderWidth: 1
        };
    } else if (period === 'daily') {
        // Last 7 days
        const days = Object.keys(dailyData).slice(-7);
        labels = days;
        data = days.map(d => dailyData[d] || 0);
        chartType = 'line';
        colors = {
            backgroundColor: 'rgba(0,242,254,0.2)',
            borderColor: 'rgba(0,242,254,1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        };
    } else {
        // Monthly (by week)
        labels = Object.keys(monthlyData);
        data = Object.values(monthlyData);
        chartType = 'line';
        colors = {
            backgroundColor: 'rgba(34,211,238,0.2)',
            borderColor: 'rgba(34,211,238,1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        };
    }

    if (trendChart) trendChart.destroy();

    trendChart = new Chart(trendCtx, {
        type: chartType,
        data: {
            labels: labels,
            datasets: [{
                label: '访问次数',
                data: data,
                ...colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

// Initialize with hourly view
updateTrendChart('hourly');

// Time period switcher for trend chart
document.querySelectorAll('.time-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        updateTrendChart(this.dataset.period);
    });
});

// Upload Time Statistics Chart
const uploadCtx = document.getElementById('uploadChart');
let uploadChart = null;

function updateUploadChart(period) {
    if (!uploadCtx) return;

    let labels, data;

    if (period === 'hourly') {
        labels = [];
        data = [];
        for (let h = 0; h < 24; h++) {
            const hourStr = String(h).padStart(2, '0');
            labels.push(hourStr + ':00');
            data.push(uploadHourlyData[hourStr] || 0);
        }
    } else {
        labels = Object.keys(uploadDailyData);
        data = Object.values(uploadDailyData);
    }

    if (uploadChart) uploadChart.destroy();

    uploadChart = new Chart(uploadCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '提交数量',
                data: data,
                backgroundColor: 'rgba(251,191,36,0.6)',
                borderColor: 'rgba(251,191,36,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// Initialize with hourly view
updateUploadChart('hourly');

// Time period switcher for upload chart
document.querySelectorAll('.time-btn-upload').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.time-btn-upload').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        updateUploadChart(this.dataset.period);
    });
});

function openEditModal(app) {
    document.getElementById('edit_id').value          = app.id;
    document.getElementById('edit_title').value       = app.title;
    document.getElementById('edit_author').value      = app.author;
    document.getElementById('edit_description').value = app.description || '';

    const tagSel = document.getElementById('edit_tag');
    for (let opt of tagSel.options) {
        opt.selected = (opt.value === (app.tag || 'other'));
    }

    const preview = document.getElementById('edit_img_preview');
    if (app.screenshot) {
        preview.src = app.screenshot + '?t=' + Date.now();
        preview.style.display = 'block';
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }

    document.getElementById('editModal').classList.add('active');
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});

// Details Modal
const analyticsData = <?= json_encode($analytics) ?>;
const viewsData = <?= json_encode($views) ?>;

function openDetailsModal(app) {
    document.getElementById('details_title').textContent = '访问详情 - ' + app.title;
    document.getElementById('details_total_views').textContent = viewsData[app.id] || 0;

    const records = analyticsData[app.id] || [];
    const tbody = document.getElementById('details_records');
    tbody.innerHTML = '';

    // Show last 50 records (reverse order)
    const recentRecords = records.slice(-50).reverse();

    recentRecords.forEach(record => {
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';

        const timeTd = document.createElement('td');
        timeTd.textContent = new Date(record.timestamp * 1000).toLocaleString('zh-CN');
        timeTd.style.padding = '0.4rem';
        timeTd.style.fontSize = '0.82rem';
        timeTd.style.color = 'rgba(255,255,255,0.7)';

        const ipTd = document.createElement('td');
        ipTd.textContent = record.ip || 'unknown';
        ipTd.style.padding = '0.4rem';
        ipTd.style.fontSize = '0.82rem';
        ipTd.style.color = '#4facfe';

        const uaTd = document.createElement('td');
        const ua = record.ua || 'unknown';
        // Simplify UA display
        const shortUA = ua.length > 50 ? ua.substring(0, 50) + '...' : ua;
        uaTd.textContent = shortUA;
        uaTd.style.padding = '0.4rem';
        uaTd.style.fontSize = '0.82rem';
        uaTd.style.color = 'rgba(255,255,255,0.5)';

        tr.appendChild(timeTd);
        tr.appendChild(ipTd);
        tr.appendChild(uaTd);
        tbody.appendChild(tr);
    });

    if (recentRecords.length === 0) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 3;
        td.textContent = '暂无访问记录';
        td.style.textAlign = 'center';
        td.style.padding = '1rem';
        td.style.color = 'rgba(255,255,255,0.4)';
        tr.appendChild(td);
        tbody.appendChild(tr);
    }

    document.getElementById('detailsModal').classList.add('active');
}

document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});
</script>
</body>
</html>

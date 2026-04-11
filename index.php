<?php
/* ============================================================
   index.php  —  MG APP Store · Main Page  v3.0
   ============================================================ */

$metaFile  = __DIR__ . '/apps/meta.json';
$viewsFile = __DIR__ . '/apps/views.json';
$apps  = file_exists($metaFile)  ? json_decode(file_get_contents($metaFile),  true) : [];
$views = file_exists($viewsFile) ? json_decode(file_get_contents($viewsFile), true) : [];
if (!is_array($apps))  $apps  = [];
if (!is_array($views)) $views = [];

usort($apps, fn($a, $b) => ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0));

$uploadSuccess = isset($_GET['success']);
$newId         = $_GET['new_id'] ?? '';
$uploadError   = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

$lang = $_GET['lang'] ?? ($_COOKIE['mg_lang'] ?? 'zh');
if (!in_array($lang, ['zh', 'en'])) $lang = 'zh';
if (isset($_GET['lang'])) setcookie('mg_lang', $lang, time() + 86400 * 365, '/');

$t = [
    'zh' => [
        'title'             => 'MG APP Store',
        'subtitle'          => '上传你的 HTML APP，爱因分享而滋长 ❤️',
        'submit_btn'        => '+ 提交作品',
        'empty_text'        => '还没有作品，快来第一个提交吧！',
        'modal_title'       => '提交作品',
        'label_author'      => '作者姓名 *',
        'label_app_name'    => '作品名称 *',
        'label_desc'        => '作品简介',
        'label_tag'         => '游戏类型',
        'label_html'        => 'HTML 文件 *（仅 .html，最大 2MB）',
        'label_screenshot'  => '截图（可选，.png/.jpg）',
        'hint_html'         => '上传单页面 HTML 文件',
        'hint_screenshot'   => '上传应用截图',
        'hint_desc'         => '简要描述你的作品（最多 200 字）',
        'ph_author'         => '你的名字',
        'ph_app_name'       => '作品名称',
        'ph_desc'           => '简单介绍一下你的作品...',
        'btn_submit'        => '提交',
        'views'             => '次',
        'play'              => '▶ 开始',
        'by'                => '作者',
        'dup_warn'          => '已存在相同作者和作品名的提交，确定要重复提交吗？',
        'hot_title'         => '🏆 热度排行榜',
        'filter_all'        => '全部',
        'tag_action'        => '动作', 'tag_puzzle'     => '益智', 'tag_shooting'  => '射击',
        'tag_racing'        => '赛车', 'tag_platformer' => '横版', 'tag_casual'    => '休闲',
        'tag_strategy'      => '策略', 'tag_tool'       => '工具', 'tag_other'     => '其他',
        'sort_new'          => '🕐 最新', 'sort_hot' => '🔥 最热', 'sort_random' => '🎲 随机',
        'search_ph'         => '搜索游戏或作者...',
        'ai_btn'            => '💡 AI改进',
        'ai_title'          => '🤖 AI 改进建议',
        'ai_body'           => "把你的游戏代码粘贴给 AI，然后说：\n\n\"请帮我改进这个HTML游戏：\n1. 增加背景音乐和音效\n2. 添加开始界面和游戏结束界面\n3. 增加分数排行榜\n4. 优化移动端触屏操作\n5. 添加更多关卡或难度选择\"",
        'ai_copy'           => '📋 复制提示词',
        'hot_badge_title'   => '🔥 这个游戏很受欢迎！',
        'hot_badge_body1'   => '已有',
        'hot_badge_body2'   => '人玩过这个游戏！',
        'hot_badge_tip'     => "想挑战它吗？\n1. 下载这个游戏的代码\n2. 用 AI 帮你做一个升级版\n3. 上传你的版本，看谁更受欢迎！",
        'download'          => '⬇️ 下载',
        'upload_my'         => '🚀 上传我的版本',
        'celebrate_title'   => '你的作品已加入宇宙！',
        'celebrate_sub'     => '太棒了！你的作品已成功发布！',
        'celebrate_edit_hint' => '在30天内你可以随时编辑这个作品 ✏️',
        'edit_btn'          => '✏️ 编辑',
    ],
    'en' => [
        'title'             => 'MG APP Store',
        'subtitle'          => 'Upload your HTML APP, love grows through sharing ❤️',
        'submit_btn'        => '+ Submit Game',
        'empty_text'        => 'No apps yet. Be the first to submit!',
        'modal_title'       => 'Submit Your App',
        'label_author'      => 'Author Name *',
        'label_app_name'    => 'App Name *',
        'label_desc'        => 'Description',
        'label_tag'         => 'Game Type',
        'label_html'        => 'HTML File * (.html only, max 2MB)',
        'label_screenshot'  => 'Screenshot (optional)',
        'hint_html'         => 'Upload a single-page HTML file',
        'hint_screenshot'   => 'Upload a screenshot',
        'hint_desc'         => 'Briefly describe your app (max 200 chars)',
        'ph_author'         => 'Your name',
        'ph_app_name'       => 'App name',
        'ph_desc'           => 'Tell us about your app...',
        'btn_submit'        => 'Submit',
        'views'             => 'plays',
        'play'              => '▶ Play',
        'by'                => 'by',
        'dup_warn'          => 'An app with the same author and name already exists. Submit anyway?',
        'hot_title'         => '🏆 Leaderboard',
        'filter_all'        => 'All',
        'tag_action'        => 'Action', 'tag_puzzle'     => 'Puzzle', 'tag_shooting'  => 'Shooting',
        'tag_racing'        => 'Racing', 'tag_platformer' => 'Platformer', 'tag_casual' => 'Casual',
        'tag_strategy'      => 'Strategy', 'tag_tool'     => 'Tool', 'tag_other'       => 'Other',
        'sort_new'          => '🕐 New', 'sort_hot' => '🔥 Hot', 'sort_random' => '🎲 Random',
        'search_ph'         => 'Search games or authors...',
        'ai_btn'            => '💡 AI Tips',
        'ai_title'          => '🤖 AI Improvement Tips',
        'ai_body'           => "Paste your game code to AI and say:\n\n\"Please help me improve this HTML game:\n1. Add background music and sound effects\n2. Add start screen and game over screen\n3. Add a scoreboard\n4. Optimize for mobile touch\n5. Add more levels or difficulty options\"",
        'ai_copy'           => '📋 Copy Prompt',
        'hot_badge_title'   => '🔥 This game is popular!',
        'hot_badge_body1'   => '',
        'hot_badge_body2'   => 'people have played this game!',
        'hot_badge_tip'     => "Want to challenge it?\n1. Download the game code\n2. Use AI to make an upgraded version\n3. Upload your version and see who wins!",
        'download'          => '⬇️ Download',
        'upload_my'         => '🚀 Upload My Version',
        'celebrate_title'   => 'Your app joined the universe!',
        'celebrate_sub'     => 'Awesome! Your app is now live!',
        'celebrate_edit_hint' => 'You can edit this app within 30 days ✏️',
        'edit_btn'          => '✏️ Edit',
    ],
];

$L           = $t[$lang];
$switchLang  = $lang === 'zh' ? 'en' : 'zh';
$switchLabel = $lang === 'zh' ? 'EN' : '中文';

$tagColors = [
    'action'     => '#E02020',
    'puzzle'     => '#2356BE',
    'shooting'   => '#8B0000',
    'racing'     => '#F5C518',
    'platformer' => '#5D9E3A',
    'casual'     => '#17A589',
    'strategy'   => '#6C3483',
    'tool'       => '#7E7E7E',
    'other'      => '#4A7A8A',
];

$existingApps = array_map(fn($a) => ['author' => $a['author'], 'title' => $a['title']], $apps);

$appsWithViews = array_map(function($app) use ($views) {
    $app['viewCount'] = $views[$app['id']] ?? 0;
    return $app;
}, $apps);

// Top 10 for leaderboard
$topApps = $appsWithViews;
usort($topApps, fn($a, $b) => $b['viewCount'] - $a['viewCount']);
$topApps = array_slice($topApps, 0, 10);

$authorCounts = [];
foreach ($apps as $app) {
    $authorCounts[$app['author']] = ($authorCounts[$app['author']] ?? 0) + 1;
}

function getAuthorBadge(int $count): string {
    if ($count >= 8) return '👑';
    if ($count >= 5) return '🔥';
    if ($count >= 3) return '⚡';
    return '🌱';
}

require __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ──────────────────────────────────────────────────────────── -->
<section class="hero">
    <h1>MG <span>APP</span> Store</h1>
    <p><?= htmlspecialchars($L['subtitle']) ?></p>
</section>

<!-- ── Page Layout: Leaderboard sidebar + Main content ───────────────── -->
<div class="page-layout">

    <!-- ── Leaderboard Sidebar ─────────────────────────────────────── -->
    <aside class="leaderboard-sidebar">
        <!-- Statistics Cards -->
        <div class="stats-overview">
            <div class="stat-box">
                <div class="stat-icon">🎮</div>
                <div class="stat-number" data-target="<?= count($apps) ?>">0</div>
                <div class="stat-label">游戏总数</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">👁</div>
                <div class="stat-number" data-target="<?= array_sum($views) ?>">0</div>
                <div class="stat-label">访问总次数</div>
            </div>
        </div>

        <div class="leaderboard-panel">
            <div class="leaderboard-header">
                <div class="leaderboard-title"><?= htmlspecialchars($L['hot_title']) ?></div>
            </div>
            <div class="leaderboard-list">
                <?php foreach ($topApps as $rank => $app):
                    $thumb = !empty($app['screenshot']) && file_exists(__DIR__ . '/' . $app['screenshot'])
                           ? htmlspecialchars($app['screenshot']) : null;
                    $medals = ['🥇','🥈','🥉'];
                    $rankClass = $rank === 0 ? 'lb-rank-1' : ($rank === 1 ? 'lb-rank-2' : ($rank === 2 ? 'lb-rank-3' : 'lb-rank-n'));
                    $rankLabel = isset($medals[$rank]) ? $medals[$rank] : ($rank + 1);
                ?>
                <a class="lb-item" href="#"
                   onclick="event.preventDefault();openPreview('<?= htmlspecialchars($app['id'], ENT_QUOTES) ?>','<?= htmlspecialchars($app['title'], ENT_QUOTES) ?>','<?= htmlspecialchars($app['file'], ENT_QUOTES) ?>')">
                    <span class="lb-rank <?= $rankClass ?>"><?= $rankLabel ?></span>
                    <?php if ($thumb): ?>
                        <img class="lb-thumb" src="<?= $thumb ?>" alt="" loading="lazy">
                    <?php else: ?>
                        <div class="lb-thumb-ph">🎮</div>
                    <?php endif; ?>
                    <div class="lb-info">
                        <div class="lb-name"><?= htmlspecialchars($app['title']) ?></div>
                        <div class="lb-meta">
                            <span class="lb-author"><?= htmlspecialchars($app['author']) ?></span>
                            <span class="lb-dot">•</span>
                            <span class="lb-views">👁 <?= $app['viewCount'] ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

    <!-- ── Main Content ────────────────────────────────────────────── -->
    <div class="main-content">

        <!-- Filter & Sort Bar -->
        <div class="filter-bar">
            <input class="search-box" id="searchBox" type="search"
                   placeholder="<?= htmlspecialchars($L['search_ph']) ?>"
                   autocomplete="off">

            <button class="tag-filter-btn active" data-tag="all"><?= htmlspecialchars($L['filter_all']) ?></button>
            <?php foreach (['action','puzzle','shooting','racing','platformer','casual','strategy','tool','other'] as $tagKey): ?>
            <button class="tag-filter-btn" data-tag="<?= $tagKey ?>"><?= htmlspecialchars($L['tag_' . $tagKey]) ?></button>
            <?php endforeach; ?>

            <button class="sort-btn active" data-sort="new"><?= htmlspecialchars($L['sort_new']) ?></button>
            <button class="sort-btn" data-sort="hot"><?= htmlspecialchars($L['sort_hot']) ?></button>
            <button class="sort-btn" data-sort="random"><?= htmlspecialchars($L['sort_random']) ?></button>
        </div>

        <!-- App Grid -->
        <main id="appGrid" class="app-grid">
        <?php if (empty($apps)): ?>
            <div class="empty-state" style="grid-column:1/-1"><?= htmlspecialchars($L['empty_text']) ?></div>
        <?php else:
            $now = time();
            foreach ($appsWithViews as $app):
                $tag       = $app['tag'] ?? 'other';
                $tagColor  = $tagColors[$tag] ?? '#4A7A8A';
                $tagLabel  = $L['tag_' . $tag] ?? $tag;
                $viewCount = $app['viewCount'];
                $ts        = $app['timestamp'] ?? 0;
                $age       = $now - $ts;
                $thumb     = !empty($app['screenshot']) && file_exists(__DIR__ . '/' . $app['screenshot'])
                             ? htmlspecialchars($app['screenshot']) : null;
                $badge     = getAuthorBadge($authorCounts[$app['author']] ?? 0);
                $appFile   = htmlspecialchars($app['file']);
                $appId     = htmlspecialchars($app['id'], ENT_QUOTES);
                $appTitleQ = htmlspecialchars($app['title'], ENT_QUOTES);
                $isHot     = $viewCount >= 50;
                $isWarm    = !$isHot && $viewCount >= 20;
                $isCold    = $viewCount < 5 && $age > 259200;
                $cardClass = 'app-card' . ($isHot ? ' card-hot-glow' : ($isWarm ? ' card-warm' : ''));
        ?>
        <div class="<?= $cardClass ?>"
             data-tag="<?= htmlspecialchars($tag) ?>"
             data-title="<?= strtolower(htmlspecialchars($app['title'])) ?>"
             data-title-orig="<?= $appTitleQ ?>"
             data-author="<?= strtolower(htmlspecialchars($app['author'])) ?>"
             data-views="<?= $viewCount ?>"
             data-ts="<?= $ts ?>"
             data-id="<?= $appId ?>"
             data-file="<?= $appFile ?>">

            <div class="card-thumb-wrap">
                <?php if ($thumb): ?>
                    <img class="card-thumb" src="<?= $thumb ?>" alt="<?= htmlspecialchars($app['title']) ?>" loading="lazy">
                <?php else: ?>
                    <div class="card-thumb-placeholder">🎮</div>
                <?php endif; ?>

                <span class="card-tag" style="background:<?= $tagColor ?>"><?= htmlspecialchars($tagLabel) ?></span>

                <?php if ($isHot): ?>
                    <span class="card-hot-badge">💎 TOP</span>
                <?php elseif ($isWarm): ?>
                    <span class="card-warm-badge">⭐ HOT</span>
                <?php elseif ($isCold): ?>
                    <span class="card-cold-badge">💡 NEW</span>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="card-title"><?= htmlspecialchars($app['title']) ?></div>
                <div class="card-author">
                    <?= htmlspecialchars($L['by']) ?>&nbsp;<?= htmlspecialchars($app['author']) ?>
                    <span class="author-badge"><?= $badge ?></span>
                    <span class="card-time" data-ts="<?= $ts ?>"></span>
                </div>
                <div class="card-desc"><?= htmlspecialchars($app['description'] ?? '') ?></div>
                <div class="card-footer">
                    <span class="card-views">👁 <?= $viewCount ?> <?= htmlspecialchars($L['views']) ?></span>
                    <div class="card-actions">
                        <a class="btn-download-card"
                           href="<?= $appFile ?>"
                           download="<?= htmlspecialchars($app['title']) ?>.html"
                           onclick="event.stopPropagation()" title="下载">⬇</a>
                        <button class="btn-share-card"
                                data-app-id="<?= htmlspecialchars($appId) ?>"
                                data-title="<?= htmlspecialchars($app['title']) ?>"
                                data-author="<?= htmlspecialchars($app['author']) ?>"
                                data-views="<?= $viewCount ?>"
                                data-desc="<?= htmlspecialchars($app['description'] ?? '') ?>"
                                data-thumb="<?= $thumb ?? '' ?>"
                                onclick="event.stopPropagation();handleShareClick(this)"
                                title="分享">🔗</button>
                        <a class="btn-edit-card"
                           href="edit.php?id=<?= urlencode($app['id']) ?>"
                           onclick="event.stopPropagation()"><?= htmlspecialchars($L['edit_btn']) ?></a>
                        <button class="btn-play" onclick="event.stopPropagation()"><?= htmlspecialchars($L['play']) ?></button>
                    </div>
                </div>
            </div>

            <button class="btn-ai-card" onclick="event.stopPropagation();openModal('aiModal')">💡 AI</button>
        </div>
        <?php endforeach; endif; ?>
        </main>

    </div><!-- /.main-content -->
</div><!-- /.page-layout -->

<?php require __DIR__ . '/includes/footer.php'; ?>

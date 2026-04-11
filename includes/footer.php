<?php
/**
 * includes/footer.php
 * All modals (submit / preview / celebrate / hot-badge / AI) + JS
 * Requires: $L, $uploadSuccess, $newId, $uploadError (from index.php)
 */
?>

<!-- ── Confetti Canvas ────────────────────────────────────────────────── -->
<canvas id="confetti-canvas" aria-hidden="true"></canvas>

<!-- ── Submit Modal ──────────────────────────────────────────────────── -->
<div class="modal-overlay" id="submitModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('submitModal')" aria-label="关闭">✕</button>
        <div class="modal-title"><?= htmlspecialchars($L['modal_title']) ?></div>

        <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="author"><?= htmlspecialchars($L['label_author']) ?></label>
                <input type="text" id="author" name="author"
                       placeholder="<?= htmlspecialchars($L['ph_author']) ?>"
                       maxlength="50" required autocomplete="name">
            </div>

            <div class="form-group">
                <label for="app_name"><?= htmlspecialchars($L['label_app_name']) ?></label>
                <input type="text" id="app_name" name="app_name"
                       placeholder="<?= htmlspecialchars($L['ph_app_name']) ?>"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="desc"><?= htmlspecialchars($L['label_desc']) ?></label>
                <textarea id="desc" name="description"
                          placeholder="<?= htmlspecialchars($L['ph_desc']) ?>"
                          maxlength="200"></textarea>
                <div class="char-count" id="charCount">0 / 200</div>
            </div>

            <div class="form-group">
                <label for="tag"><?= htmlspecialchars($L['label_tag']) ?></label>
                <select id="tag" name="tag">
                    <?php foreach (['action','puzzle','shooting','racing','platformer','casual','strategy','tool','other'] as $tagKey): ?>
                    <option value="<?= $tagKey ?>"><?= htmlspecialchars($L['tag_' . $tagKey]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="html_file"><?= htmlspecialchars($L['label_html']) ?></label>
                <input type="file" id="html_file" name="html_file" accept=".html,.htm" required>
                <div class="form-hint"><?= htmlspecialchars($L['hint_html']) ?></div>
            </div>

            <div class="form-group">
                <label for="screenshot"><?= htmlspecialchars($L['label_screenshot']) ?></label>
                <input type="file" id="screenshot" name="screenshot" accept=".png,.jpg,.jpeg,.webp">
                <div class="form-hint"><?= htmlspecialchars($L['hint_screenshot']) ?></div>
            </div>

            <button type="submit" class="btn-submit-form">
                <?= htmlspecialchars($L['btn_submit']) ?>
            </button>
        </form>
    </div>
</div>

<!-- ── Game Preview Modal (iframe) ───────────────────────────────────── -->
<div class="modal-overlay" id="previewModal" role="dialog" aria-modal="true">
    <div class="preview-modal-box">
        <div class="preview-header">
            <span class="preview-title" id="previewTitle"></span>
            <div class="preview-actions">
                <button class="btn-fullscreen" id="previewFullscreen">🔲 <?= $lang === 'zh' ? '全屏' : 'Fullscreen' ?></button>
                <a class="btn-ghost" id="previewDownload" href="#" download style="font-size:0.82rem;padding:0.35rem 0.85rem;color:rgba(255,255,255,0.85);border-color:rgba(255,255,255,0.3);">
                    <?= htmlspecialchars($L['download']) ?>
                </a>
                <a class="btn-ghost" id="previewEditBtn" href="#" style="font-size:0.82rem;padding:0.35rem 0.85rem;display:none;color:#a78bfa;border-color:rgba(167,139,250,0.4);">
                    <?= htmlspecialchars($L['edit_btn']) ?>
                </a>
                <button class="modal-close" id="previewCloseBtn" style="position:static;font-size:1.2rem;" aria-label="关闭">✕</button>
            </div>
        </div>
        <iframe class="preview-iframe" id="previewIframe"
                sandbox="allow-scripts allow-same-origin allow-forms"
                title="Game Preview"
                src="about:blank"></iframe>
    </div>
</div>

<!-- ── Celebrate Modal ────────────────────────────────────────────────── -->
<div class="modal-overlay" id="celebrateModal"
     data-autoopen="<?= $uploadSuccess ? '1' : '0' ?>"
     data-newid="<?= htmlspecialchars($newId) ?>"
     role="dialog" aria-modal="true">
    <div class="modal-box celebrate-box">
        <button class="modal-close" onclick="closeModal('celebrateModal')" aria-label="关闭">✕</button>
        <div class="celebrate-emoji">🚀</div>
        <div class="celebrate-title"><?= htmlspecialchars($L['celebrate_title']) ?></div>
        <p class="celebrate-sub"><?= htmlspecialchars($L['celebrate_sub']) ?></p>
        <p class="celebrate-edit-hint"><?= htmlspecialchars($L['celebrate_edit_hint']) ?></p>
        <?php if ($newId): ?>
        <button class="btn-primary" onclick="closeModal('celebrateModal');openPreview('<?= htmlspecialchars($newId, ENT_QUOTES) ?>','','')">
            ▶ <?= htmlspecialchars($L['play']) ?>
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ── Hot Badge Modal ────────────────────────────────────────────────── -->
<div class="modal-overlay" id="hotBadgeModal" role="dialog" aria-modal="true">
    <div class="modal-box hot-badge-modal">
        <button class="modal-close" onclick="closeModal('hotBadgeModal')" aria-label="关闭">✕</button>
        <div class="modal-title"><?= htmlspecialchars($L['hot_badge_title']) ?></div>
        <div class="plays-count">0</div>
        <p style="color:rgba(255,255,255,0.5);font-size:0.88rem;">
            <?= htmlspecialchars($L['hot_badge_body1']) ?>
            <?= htmlspecialchars($L['hot_badge_body2']) ?>
        </p>
        <div class="tip-text"><?= htmlspecialchars($L['hot_badge_tip']) ?></div>
        <div class="hot-badge-actions">
            <a href="#" class="btn-ghost btn-download" download>
                <?= htmlspecialchars($L['download']) ?>
            </a>
            <button class="btn-primary btn-upload-my" onclick="closeModal('hotBadgeModal');openModal('submitModal')">
                <?= htmlspecialchars($L['upload_my']) ?>
            </button>
        </div>
    </div>
</div>

<!-- ── AI Tips Modal ──────────────────────────────────────────────────── -->
<div class="modal-overlay" id="aiModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('aiModal')" aria-label="关闭">✕</button>
        <div class="modal-title"><?= htmlspecialchars($L['ai_title']) ?></div>
        <div class="ai-body-text" id="aiBodyText"><?= htmlspecialchars($L['ai_body']) ?></div>
        <button class="btn-primary" id="aiCopyBtn" style="width:100%">
            <?= htmlspecialchars($L['ai_copy']) ?>
        </button>
    </div>
</div>

<!-- ── Upload Error Toast ─────────────────────────────────────────────── -->
<?php if ($uploadError): ?>
<div id="errorToast" class="alert alert-error"
     style="position:fixed;bottom:1.2rem;right:1.2rem;z-index:9999;max-width:360px;">
    ⚠️ <?= $uploadError ?>
</div>
<script>setTimeout(() => document.getElementById('errorToast')?.remove(), 5000);</script>
<?php endif; ?>

<!-- ── Share Card Modal ─────────────────────────────────────────────── -->
<div class="modal-overlay" id="shareCardModal" role="dialog" aria-modal="true">
    <div class="share-modal-box">
        <button class="modal-close" onclick="closeModal('shareCardModal')" aria-label="关闭">✕</button>
        <div class="share-title">🎉 分享你的游戏</div>
        <div class="share-canvas-wrapper">
            <canvas id="shareCanvas" width="500" height="650"></canvas>
        </div>
        <div class="share-actions">
            <button class="btn-share-download" onclick="downloadShareCard()">⬇️ 保存图片</button>
            <button class="btn-share-copy" onclick="copyShareLink()">📋 复制链接</button>
        </div>
        <div class="share-tip">分享给朋友，让更多人玩你的游戏！</div>
    </div>
</div>


<!-- ── GitHub Footer ─────────────────────────────────────────────────── -->
<footer style="text-align:center;padding:2rem 1rem 3rem;color:rgba(255,255,255,0.35);font-size:0.82rem;">
    <span style="color:rgba(255,255,255,0.45);font-size:0.88rem;font-weight:600;">Made By MGSpace & AI agent with ❤️</span>
    <span style="color:rgba(255,255,255,0.3);margin:0 0.8rem;">|</span>
    <a href="https://github.com/hardihuang/mgAppStore" target="_blank" rel="noopener"
       style="color:rgba(255,255,255,0.5);text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;transition:color 0.2s;"
       onmouseover="this.style.color='rgba(255,255,255,0.9)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
        </svg>
        hardihuang/mgAppStore
    </a>
</footer>
<!-- ── Main Script ────────────────────────────────────────────────────── -->
<script src="assets/app.js"></script>
<script src="assets/share-card.js"></script>
</body>
</html>

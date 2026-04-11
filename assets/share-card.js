/* ============================================================
   Share Card Functionality - 炫酷分享卡片生成（增强版）
   ============================================================ */

let shareGameData = null;

// 处理分享按钮点击
function handleShareClick(btn) {
    const appId = btn.dataset.appId;
    const title = btn.dataset.title;
    const author = btn.dataset.author;
    const views = parseInt(btn.dataset.views, 10);
    const description = btn.dataset.desc;
    const screenshot = btn.dataset.thumb || null;

    openShareCard(appId, title, author, views, description, screenshot);
}

// 打开分享卡片 - 增加简介和封面图参数
function openShareCard(appId, title, author, views, description, screenshot) {
    shareGameData = { appId, title, author, views, description, screenshot };

    // 生成卡片
    generateShareCard(appId, title, author, views, description, screenshot);

    // 打开modal
    openModal('shareCardModal');

    // 添加闪光效果
    setTimeout(() => addFlashEffect(), 100);

    // 播放庆祝音效
    mcSound.celebrate();
}

// 添加闪光效果
function addFlashEffect() {
    const wrapper = document.querySelector('.share-canvas-wrapper');
    if (!wrapper) return;

    // 创建闪光元素
    const flash = document.createElement('div');
    flash.className = 'share-flash-effect';
    wrapper.appendChild(flash);

    // 动画结束后移除
    setTimeout(() => {
        flash.remove();
    }, 1500);
}

// 生成分享卡片 - 包含封面图和简介（布局参考游戏卡片）
function generateShareCard(appId, title, author, views, description, screenshot) {
    const canvas = document.getElementById('shareCanvas');
    const ctx = canvas.getContext('2d');

    // 清空画布
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // 背景渐变 - 蓝色主题
    const bgGradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
    bgGradient.addColorStop(0, '#18A0FB');
    bgGradient.addColorStop(1, '#0a5fa0');
    ctx.fillStyle = bgGradient;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // 添加装饰圆圈
    ctx.fillStyle = 'rgba(255,255,255,0.08)';
    ctx.beginPath();
    ctx.arc(450, 50, 60, 0, Math.PI * 2);
    ctx.fill();
    ctx.beginPath();
    ctx.arc(50, 600, 70, 0, Math.PI * 2);
    ctx.fill();

    // 顶部Logo区域
    ctx.fillStyle = 'rgba(255,255,255,0.2)';
    ctx.fillRect(0, 0, canvas.width, 80);

    ctx.fillStyle = '#fff';
    ctx.font = 'bold 32px Nunito, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('MG APP Store', canvas.width / 2, 50);

    // 如果有封面图，绘制封面（保持原比例）
    if (screenshot) {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            // 计算图片比例，保持原比例绘制
            const imgWidth = img.width;
            const imgHeight = img.height;
            const imgRatio = imgWidth / imgHeight;

            // 最大显示区域：440x240，但保持比例
            const maxWidth = 440;
            const maxHeight = 240;
            let displayWidth, displayHeight;

            if (imgRatio > maxWidth / maxHeight) {
                // 图片更宽，以宽度为准
                displayWidth = maxWidth;
                displayHeight = maxWidth / imgRatio;
            } else {
                // 图片更高，以高度为准
                displayHeight = maxHeight;
                displayWidth = maxHeight * imgRatio;
            }

            // 计算居中位置
            const x = (canvas.width - displayWidth) / 2;
            const y = 100;

            // 绘制封面图（保持比例，居中）
            drawImageWithRadius(ctx, img, x, y, displayWidth, displayHeight, 16);

            // 绘制文字信息（在图片加载后，去掉热度）
            drawTextInfo(ctx, title, author, description, canvas, y + displayHeight);
        };
        img.src = screenshot;
    } else {
        // 没有封面图，绘制游戏图标占位符
        ctx.fillStyle = 'rgba(255,255,255,0.15)';
        const placeholderY = 100;
        roundRect(ctx, 30, placeholderY, 440, 240, 16, true, false);

        ctx.fillStyle = 'rgba(255,255,255,0.4)';
        ctx.font = 'bold 80px Nunito';
        ctx.textAlign = 'center';
        ctx.fillText('🎮', canvas.width / 2, placeholderY + 140);

        // 绘制文字信息
        drawTextInfo(ctx, title, author, description, canvas, placeholderY + 240);
    }
}

// 绘制文字信息（支持换行，去掉热度）
function drawTextInfo(ctx, title, author, description, canvas, startY) {
    // 游戏标题（大字，支持换行）
    ctx.fillStyle = '#fff';
    ctx.font = 'bold 38px Nunito, sans-serif';
    ctx.textAlign = 'center';

    // 标题换行逻辑
    const maxWidth = canvas.width - 60;
    const titleLines = wrapText(ctx, title, maxWidth);
    const titleY = startY + 30;
    titleLines.forEach((line, index) => {
        ctx.fillText(line, canvas.width / 2, titleY + index * 45);
    });

    // 作者信息
    ctx.fillStyle = 'rgba(255,255,255,0.8)';
    ctx.font = '600 24px Nunito, sans-serif';
    const authorY = titleY + titleLines.length * 45 + 20;
    ctx.fillText(`by ${author}`, canvas.width / 2, authorY);

    // 简介（支持多行，最多显示150字）
    if (description) {
        const shortDesc = description.length > 150 ? description.substring(0, 150) + '...' : description;
        ctx.fillStyle = 'rgba(255,255,255,0.6)';
        ctx.font = '500 18px Nunito, sans-serif';

        const descLines = wrapText(ctx, shortDesc, maxWidth - 20);
        const descY = authorY + 30;
        descLines.forEach((line, index) => {
            if (index < 3) { // 最多显示3行简介
                ctx.fillText(line, canvas.width / 2, descY + index * 24);
            }
        });
    }

    // 底部链接和装饰
    drawFooter(ctx, canvas);
}

// 绘制底部信息（固定在最底部）
function drawFooter(ctx, canvas) {
    const bottomY = canvas.height; // 650

    // 链接 - 固定在底部70px处
    ctx.fillStyle = 'rgba(255,255,255,0.5)';
    ctx.font = '600 16px Nunito, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('mgspace.wiki/appstore', canvas.width / 2, bottomY - 70);

    // 底部标语 - 固定在底部40px处
    ctx.fillStyle = 'rgba(255,255,255,0.4)';
    ctx.font = '600 15px Nunito, sans-serif';
    ctx.fillText('爱因分享而滋长 ❤️', canvas.width / 2, bottomY - 40);

    // 装饰星星 - 固定在底部20px处
    drawStar(ctx, 60, bottomY - 20, 14, '#ffd700', 0.7);
    drawStar(ctx, 440, bottomY - 20, 14, '#ffd700', 0.7);
}

// 文字换行辅助函数
function wrapText(ctx, text, maxWidth) {
    const words = text.split('');
    const lines = [];
    let currentLine = '';

    words.forEach(char => {
        const testLine = currentLine + char;
        const metrics = ctx.measureText(testLine);
        if (metrics.width > maxWidth && currentLine !== '') {
            lines.push(currentLine);
            currentLine = char;
        } else {
            currentLine = testLine;
        }
    });

    if (currentLine !== '') {
        lines.push(currentLine);
    }

    return lines;
}

// 绘制带圆角的图片
function drawImageWithRadius(ctx, img, x, y, width, height, radius) {
    ctx.save();
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
    ctx.clip();
    ctx.drawImage(img, x, y, width, height);
    ctx.restore();
}

// 圆角矩形辅助函数
function roundRect(ctx, x, y, width, height, radius, fill, stroke) {
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
    if (fill) ctx.fill();
    if (stroke) ctx.stroke();
}

// 绘制星星辅助函数
function drawStar(ctx, cx, cy, size, color, alpha) {
    ctx.fillStyle = color;
    ctx.globalAlpha = alpha;
    ctx.beginPath();
    ctx.moveTo(cx, cy - size);
    ctx.lineTo(cx + size * 0.3, cy - size * 0.3);
    ctx.lineTo(cx + size, cy);
    ctx.lineTo(cx + size * 0.3, cy + size * 0.3);
    ctx.lineTo(cx, cy + size);
    ctx.lineTo(cx - size * 0.3, cy + size * 0.3);
    ctx.lineTo(cx - size, cy);
    ctx.lineTo(cx - size * 0.3, cy - size * 0.3);
    ctx.closePath();
    ctx.fill();
    ctx.globalAlpha = 1;
}

// 圆角矩形辅助函数
function roundRect(ctx, x, y, width, height, radius, fill, stroke) {
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
    if (fill) ctx.fill();
    if (stroke) ctx.stroke();
}

// 下载分享卡片
function downloadShareCard() {
    const canvas = document.getElementById('shareCanvas');
    const link = document.createElement('a');
    link.download = `${shareGameData.title}_分享卡片.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
    mcSound.download();
}

// 复制分享链接
function copyShareLink() {
    const link = `https://mgspace.wiki/appstore`;
    navigator.clipboard.writeText(link).then(() => {
        const btn = document.querySelector('.btn-share-copy');
        const originalText = btn.textContent;
        btn.textContent = '✅ 已复制!';
        setTimeout(() => {
            btn.textContent = originalText;
        }, 2000);
        mcSound.ai();
    }).catch(err => {
        alert('复制失败，请手动复制链接: ' + link);
    });
}
<div align="center">

<img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
<img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white"/>
<img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white"/>
<img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black"/>
<img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge"/>

# 🎮 MG APP Store

**中文** | [English](#english)

一个 Poki 风格的 HTML5 游戏分享平台，专为学习用 AI 编程的孩子们打造。
上传你的 HTML APP → 爱因分享而滋长 ❤️ → 与全世界一起升级打怪。

[**🌐 在线体验 →**](http://mgspace.wiki/appstore/) &nbsp;|&nbsp; [**📖 编程教程 →**](http://mgspace.wiki/appstore/tutorial.html)

![MG APP Store](https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/1774061178_ea21d21b.png)

</div>

---

## ✨ 这是什么？

MG APP Store 是一个轻量级、零依赖的游戏分享平台。学生上传自己的单文件 HTML5 游戏，平台以精美的卡片画廊展示它们。

项目还内置了一套 **7 关互动编程教程**，引导完全零基础的初学者从零开始，借助 AI（如 Claude 或 ChatGPT）一步步做出并发布自己的第一个游戏。

> 🧒 专为 8–14 岁参加 AI 编程工作坊的孩子设计，同样适合任何想用 AI 学做游戏的人。

---

## 🚀 功能特性

### 🏪 游戏商店
- **Poki 风格 UI** — 动态卡片网格、玻璃拟态导航、浮动光球背景、星空特效
- **一键游玩** — 游戏在沙盒 iframe 中运行，无需安装
- **智能访问计数** — Cookie防刷机制，同一用户30天内访问同一游戏只计数一次
- **热度排行榜优化** — 显示游戏总数、访问总次数、作者信息、数字滚动动画
- **标签筛选** — 动作 / 益智 / 射击 / 赛车 / 横版 / 休闲 / 策略 / 工具 / 其他
- **排序选项** — 最新上传 / 最多游玩 / 随机推荐
- **中英双语** — 🇨🇳 中文 / 🇺🇸 英文一键切换，Cookie 持久化
- **上传弹窗** — 支持作者、标题、简介、标签、封面截图
- **重复检测** — 相同作者+标题再次提交时自动提醒
- **炫酷分享卡片** — 点击分享按钮生成精美分享图片（含封面、作者、简介），支持下载保存和链接复制，炫酷淡入动画+闪光特效
- **分享按钮** — 简洁的链接图标，一键生成分享卡片

### 📊 数据统计与分析（`admin.php` 增强）
- **访问趋势可视化** — Chart.js 调用趋势图，支持24小时/7天/30天切换
- **作品提交统计** — 按时段/日期显示提交分布
- **Top排行榜** — 热门游戏Top10、访问IP Top10
- **访问详情查看** — 点击"详情"按钮查看最近50条访问记录（时间、IP、浏览器）
- **统计概览卡片** — 总作品数、总访问量、独立IP数、最热门游戏
- **数据持久化** — `analytics.json` 详细记录每次访问（时间戳、IP、UA）

### 🔒 管理后台（`admin.php`）
- `.env` 文件密码保护
- 删除任意已上传游戏
- 查看所有提交及元数据
- 编辑游戏信息（标题、作者、简介、标签、截图）
- 查看详细访问分析数据

### 📚 互动教程（`tutorial.html`）
- **7 个递进关卡** — 从"做一个恐龙跑酷游戏"到"发布到 Poki"
- **XP 与连续打卡系统** — 每关获得 XP，每日连续记录存入 localStorage
- **动态提示词生成器** — 选择主角、障碍物、技能、特效 → 提示词自动更新
- **一键复制到 AI** — 支持 HTTPS Clipboard API + HTTP execCommand 双重兼容
- **简介吸引力评分** — 实时反馈你的游戏简介质量
- **彩带庆祝 & 音效** — 每完成一关都有庆祝动画
- **进阶解锁章节** — 完成 7 关后解锁 Poki / itch.io 发布指南
- **全程双语** — 中英文完整支持

---

## 📸 截图预览

<table>
<tr>
<td align="center"><b>🏪 游戏商店</b></td>
<td align="center"><b>📊 统计分析</b></td>
</tr>
<tr>
<td><img src="https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/1774061178_ea21d21b.png" width="380"/></td>
<td><img src="https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/1774164941_2e0e57b6.png" width="380"/></td>
</tr>
<tr>
<td align="center"><b>🎉 分享卡片</b></td>
<td align="center"><b>📚 编程教程</b></td>
</tr>
<tr>
<td><img src="https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/share-card-demo.png" width="380"/></td>
<td><img src="https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/1775361570_2c7618ae_1775622737.png" width="380"/></td>
</tr>
</table>

---

## 📁 项目结构

```
mgappstore/
├── index.php          # 商店主页
├── upload.php         # 文件上传处理
├── play.php           # 智能浏览计数 + 游戏启动 + 详细访问记录
├── edit.php           # 编辑游戏元数据（支持30天内编辑）
├── admin.php          # 管理后台（含统计分析面板）
├── tutorial.html      # 7 关互动教程
├── .env               # 管理员密码配置
├── assets/
│   ├── style.css      # 全局样式（Poki 主题）
│   ├── app.js         # 商店 JS（筛选、上传、光球、音效、数字动画）
│   ├── share-card.css # 分享卡片样式
│   └── share-card.js  # 分享卡片生成（Canvas绘制、换行、图片比例）
├── includes/
│   ├── header.php     # 公共导航头
│   ├── footer.php     # 公共底部（含分享Modal）
│   └── tutorial-header.php
├── apps/
│   ├── meta.json      # 所有游戏元数据
│   ├── views.json     # 浏览计数
│   ├── analytics.json # 详细访问记录（时间戳、IP、UA）
│   └── [id]/          # 每个游戏的 HTML 文件
└── screenshots/       # 游戏封面图片
```

---

## 🛠️ 部署教程

### 环境要求
- PHP 8.0+
- 任意 Web 服务器（Apache / Nginx / Caddy）
- 无需数据库 — 全部使用 JSON 平面文件存储

### 1. 克隆仓库

```bash
git clone https://github.com/hardihuang/mgAppStore.git
cd mgAppStore
```

### 2. 设置目录权限

```bash
chmod 755 apps/ screenshots/
chmod 644 apps/meta.json apps/views.json apps/analytics.json
```

### 3. 配置管理员密码

在项目根目录创建 `.env` 文件：

```env
ADMIN_PASSWORD=你的密码
```

> ⚠️ 不要把 `.env` 提交到 Git，请加入 `.gitignore`。

### 4. Web 服务器配置

**Apache** — 在 `.htaccess` 中添加：
```apache
Options -Indexes
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/mgAppStore;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 5. 完成

访问 `http://yourdomain.com/` — 商店上线。
访问 `http://yourdomain.com/admin.php` — 后台管理（输入 `.env` 中的密码）。
访问 `http://yourdomain.com/tutorial.html` — 教程就绪。

---

## 🎯 新功能亮点

### 🔐 智能防刷机制
- **Cookie追踪**：同一用户30天内访问同一游戏只计数一次
- **浏览器指纹**：通过Cookie识别，防止重复打开刷热度
- **数据安全**：防刷不影响正常用户体验，游戏仍可正常打开

### 📈 详细访问分析
- **时间戳记录**：精确记录每次访问时间
- **IP地址追踪**：记录访问者IP，识别异常访问模式
- **浏览器信息**：记录User Agent，分析用户设备类型
- **数据量限制**：每个游戏最多保存500条记录，避免文件膨胀

### 🎨 炫酷分享卡片
- **Canvas动态生成**：使用HTML5 Canvas绘制精美卡片
- **保持图片比例**：智能计算截图原始比例，不压缩变形
- **完整信息展示**：标题、作者、简介、封面图自动换行显示
- **动画效果**：淡入缩放动画 + 白色径向闪光脉冲
- **一键下载**：PNG格式保存，方便分享到社交平台
- **链接复制**：一键复制 `mgspace.wiki/appstore` 链接

### 📊 后台统计面板
- **趋势图表**：24小时柱状图、7天折线图、30天周统计一键切换
- **提交统计**：按时段/日期显示作品提交分布
- **排行榜**：热门游戏Top10、访问IP Top10实时更新
- **详情查看**：每个游戏最近50条访问记录，包含时间、IP、浏览器

---

## 🎓 教程关卡一览

| 关卡 | 主题 | XP |
|------|------|----|
| 🟢 Level 1 | 用 AI 做出第一个恐龙跑酷游戏 | 10 |
| 🟢 Level 2 | 加入计分系统 | 15 |
| 🟡 Level 3 | 给主角设计专属技能 | 20 |
| 🟡 Level 4 | 让游戏越来越快 | 20 |
| 🟠 Level 5 | 加入音效与视觉反馈 | 25 |
| 🟠 Level 6 | 加入隐藏彩蛋机制 | 30 |
| 🔴 Level 7 | 打磨界面、设计封面、写简介并发布 | 35 |

每一关都包含：
- **可定制 AI 提示词** — 学生选择主角、障碍物、技能等，提示词自动生成
- **一键复制按钮** — 直接发送给 AI 工具
- **知识点标签** — 解释这一步背后的编程概念

---

## 💡 使用场景

- **编程工作坊**：学生完成游戏后上传分享，展示成果获得成就感
- **课堂教学**：老师收集学生作品，统一展示和分析学习进度
- **家庭学习**：孩子在家自学AI编程，分享作品给家人朋友
- **竞赛展示**：作品竞赛平台，实时统计热度排行
- **社区分享**：游戏开发者社区，分享创意HTML5游戏

---

## 🤝 参与贡献

欢迎提交 PR！以下是一些可以贡献的方向：

- [ ] 用户注册系统（账号管理）
- [ ] 点赞/评论功能
- [ ] 深色模式切换
- [ ] 游戏外嵌Widget
- [ ] 支持ZIP包上传（多资源文件）
- [ ] 移动端优化（触摸手势）
- [ ] 多人游戏支持
- [ ] 游戏评分系统

```bash
git checkout -b feature/你的想法
```

---

## 📄 开源协议

MIT © 2024 — 可自由使用、修改并部署到你自己的编程工作坊。

---

## 🌟 特别感谢

本项目由 **MGSpace** 与 **AI Agent** 共同打造 ❤️

---

<div align="center">

Made By MGSpace & AI agent with ❤️

[🌐 在线体验](http://mgspace.wiki/appstore/) · [🐛 报告问题](https://github.com/hardihuang/mgAppStore/issues) · [💡 功能建议](https://github.com/hardihuang/mgAppStore/issues)

⭐ 如果对你有帮助，点个 Star 吧！

</div>

---

<a name="english"></a>

<div align="center">

# 🎮 MG APP Store

[中文](#-mg-app-store) | **English**

**A Poki-style HTML5 game sharing platform built for kids learning to code with AI.**
Upload your HTML APP → Love grows through sharing ❤️ → Level up together with the world.

[**🌐 Live Demo →**](http://mgspace.wiki/appstore/) &nbsp;|&nbsp; [**📖 Tutorial →**](http://mgspace.wiki/appstore/tutorial.html)

![MG APP Store](https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/1774061178_ea21d21b.png)

</div>

---

## ✨ What is this?

MG APP Store is a lightweight, zero-dependency game sharing platform. Students upload their single-file HTML5 games and the platform displays them in a beautiful card gallery.

It also ships with a **7-level interactive tutorial** that guides complete beginners from zero to a published game — using AI (like Claude or ChatGPT) as their coding partner.

> 🧒 Designed for kids aged 8–14 in AI coding workshops, but works for anyone learning to build games with AI.

---

## 🚀 Features

### 🏪 App Store
- **Poki-inspired UI** — animated card grid, glassmorphism nav, floating orbs, starfield
- **One-click play** — games run in a sandboxed iframe, no install needed
- **Smart view counter** — Cookie-based anti-spam, one user counts once per 30 days per game
- **Enhanced leaderboard** — shows total games, total views, author info, smooth animations
- **Tag filtering** — Action / Puzzle / Shooting / Racing / Platformer / Casual / Strategy / Tool / Other
- **Sort options** — Newest / Most played / Random
- **Bilingual** — 🇨🇳 Chinese / 🇺🇸 English toggle, persisted via cookie
- **Upload modal** — author, title, description, tag, screenshot
- **Duplicate detection** — warns before re-submitting same author + title
- **Share card generator** — click share button to generate beautiful share image (cover, author, description), download PNG, copy link, fade-in animation + flash effect
- **Share button** — clean link icon, one-click card generation

### 📊 Analytics Dashboard (`admin.php` enhanced)
- **Traffic trends visualization** — Chart.js trend graphs, switch between 24h/7d/30d
- **Submission stats** — hourly/daily distribution of uploads
- **Top rankings** — Top 10 hot games, Top 10 visitor IPs
- **Visit details** — click "Details" to see last 50 visit records (time, IP, browser)
- **Overview cards** — total games, total views, unique IPs, hottest game
- **Data persistence** — `analytics.json` logs each visit (timestamp, IP, UA)

### 🔒 Admin Panel (`admin.php`)
- Password-protected via `.env`
- Delete any uploaded game
- View all submissions with metadata
- Edit game info (title, author, description, tag, screenshot)
- View detailed analytics data

### 📚 Interactive Tutorial (`tutorial.html`)
- **7 progressive levels** — from "build a Dino game" to "publish on Poki"
- **XP & streak system** — earn XP per level, daily streak tracking via localStorage
- **Dynamic prompt builder** — choose hero, obstacle, skill, effects → prompt auto-updates
- **Copy-to-AI button** — one click, works on both HTTP and HTTPS
- **Appeal score meter** — real-time feedback on your game description quality
- **Confetti & sound FX** — celebrate every level completion
- **Bonus advanced section** — unlocks after all 7 levels, covers Poki / itch.io publishing
- **Full bilingual support** — ZH/EN throughout

---

## 📸 Screenshots

<table>
<tr>
<td align="center"><b>🏪 Store</b></td>
<td align="center"><b>📊 Analytics</b></td>
</tr>
<tr>
<td><img src="https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/1774061178_ea21d21b.png" width="380"/></td>
<td><img src="https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/1774164941_2e0e57b6.png" width="380"/></td>
</tr>
<tr>
<td align="center"><b>🎉 Share Card</b></td>
<td align="center"><b>📚 Tutorial</b></td>
</tr>
<tr>
<td><img src="https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/share-card-demo.png" width="380"/></td>
<td><img src="https://raw.githubusercontent.com/hardihuang/mgAppStore/main/screenshots/1775361570_2c7618ae_1775622737.png" width="380"/></td>
</tr>
</table>

---

## 📁 Project Structure

```
mgappstore/
├── index.php          # Main store page
├── upload.php         # File upload handler
├── play.php           # Smart view counter + game launcher + detailed analytics
├── edit.php           # Edit game metadata (editable within 30 days)
├── admin.php          # Admin panel (with analytics dashboard)
├── tutorial.html      # 7-level interactive tutorial
├── .env               # Admin password config
├── assets/
│   ├── style.css      # Global styles (Poki-inspired theme)
│   ├── app.js         # Store JS (filters, upload, orbs, sfx, number animations)
│   ├── share-card.css # Share card styles
│   └── share-card.js  # Share card generation (Canvas, wrapping, aspect ratio)
├── includes/
│   ├── header.php     # Shared nav header
│   ├── footer.php     # Shared footer (includes share Modal)
│   └nd tutorial-header.php
├── apps/
│   ├── meta.json      # All game metadata
│   ├── views.json     # View counts
│   ├── analytics.json # Detailed visit records (timestamp, IP, UA)
│   └── [id]/          # Each game's HTML file
└── screenshots/       # Game cover images
```

---

## 🛠️ Setup

### Requirements
- PHP 8.0+
- Any web server (Apache / Nginx / Caddy)
- No database — everything is flat JSON

### 1. Clone

```bash
git clone https://github.com/hardihuang/mgAppStore.git
cd mgAppStore
```

### 2. Set permissions

```bash
chmod 755 apps/ screenshots/
chmod 644 apps/meta.json apps/views.json apps/analytics.json
```

### 3. Configure admin password

Create a `.env` file in the root:

```env
ADMIN_PASSWORD=your_password_here
```

> ⚠️ Never commit `.env` to version control.

### 4. Web server config

**Apache** — add to `.htaccess`:
```apache
Options -Indexes
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/mgAppStore;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 5. Done

Visit `http://yourdomain.com/` — store is live.
Visit `http://yourdomain.com/admin.php` — admin panel (enter password from `.env`).
Visit `http://yourdomain.com/tutorial.html` — tutorial is ready.

---

## 🎯 New Features Highlights

### 🔐 Smart Anti-Spam Mechanism
- **Cookie tracking**: One user counts once per 30 days per game
- **Browser fingerprint**: Identifies via Cookie, prevents repetitive opening to boost views
- **Data security**: Anti-spam doesn't affect normal user experience, games open normally

### 📈 Detailed Visit Analytics
- **Timestamp recording**: Precise time of each visit
- **IP address tracking**: Visitor IPs, detect abnormal patterns
- **Browser info**: User Agent, analyze device types
- **Data limit**: Max 500 records per game, avoids file bloat

### 🎨 Beautiful Share Card
- **Canvas dynamic generation**: HTML5 Canvas draws beautiful cards
- **Preserve image ratio**: Smart aspect ratio calculation, no compression distortion
- **Full info display**: Title, author, description, cover with auto line wrapping
- **Animation effects**: Fade-in scale animation + white radial flash pulse
- **One-click download**: Save as PNG, share to social platforms
- **Link copy**: One-click copy `mgspace.wiki/appstore` link

### 📊 Admin Analytics Dashboard
- **Trend charts**: 24h bar chart, 7d line chart, 30d weekly stats toggle
- **Submission stats**: Hourly/daily upload distribution
- **Rankings**: Top 10 hot games, Top 10 visitor IPs real-time update
- **Details view**: Last 50 visit records per game, includes time, IP, browser

---

## 🎓 Tutorial Levels

| Level | Topic | XP |
|-------|-------|----|
| 🟢 Level 1 | Build your first Dino-style runner with AI | 10 |
| 🟢 Level 2 | Add a score system | 15 |
| 🟡 Level 3 | Give your hero a special skill | 20 |
| 🟡 Level 4 | Make it harder over time | 20 |
| 🟠 Level 5 | Add sound effects & visual feedback | 25 |
| 🟠 Level 6 | Add a surprise mechanic | 30 |
| 🔴 Level 7 | Polish UI, design cover & write a description | 35 |

Each level includes a customizable AI prompt, a one-click copy button, and a concept tag explaining the coding idea behind the step.

---

## 💡 Use Cases

- **Coding workshops**: Students upload games after completion, showcase achievements with sense of accomplishment
- **Classroom teaching**: Teachers collect student works, unified display and progress analysis
- **Home learning**: Kids self-study AI coding, share works with family and friends
- **Competition showcase**: Game competition platform, real-time popularity ranking
- **Community sharing**: Game developer community, share creative HTML5 games

---

## 🤝 Contributing

PRs welcome! Some ideas:

- [ ] User registration system (account management)
- [ ] Likes/comments functionality
- [ ] Dark mode toggle
- [ ] Game embed widget
- [ ] ZIP upload support (multi-resource files)
- [ ] Mobile optimization (touch gestures)
- [ ] Multiplayer game support
- [ ] Game rating system

```bash
git checkout -b feature/your-idea
```

---

## 📄 License

MIT © 2024 — free to use, modify, and deploy for your own coding workshops.

---

## 🌟 Special Thanks

This project is crafted by **MGSpace** and **AI Agent** with ❤️

---

<div align="center">

Made By MGSpace & AI agent with ❤️

[🌐 Live Demo](http://mgspace.wiki/appstore/) · [🐛 Report Bug](https://github.com/hardihuang/mgAppStore/issues) · [💡 Request Feature](https://github.com/hardihuang/mgAppStore/issues)

⭐ Star this repo if it helped you!

</div>
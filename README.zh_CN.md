# ReqLock

[English](README.md) · [日本語](README.ja.md) · **简体中文** · [Español](README.es_ES.md) · [Deutsch](README.de_DE.md) · [Français](README.fr_FR.md) · [فارسی](README.fa_IR.md)

*也写作 **RequestLock** 或 **Request Lock**。*

> 一款充当总开关的 WordPress 插件，可**禁用来自 WordPress 核心、你的主题和插件的外部（互联网）调用** —— 让你的站点在外部互联网被切断或受限时依然正常运行。

专为连接受限时的韧性而打造：拨动一个开关，站点便停止向互联网发起请求，仅从本地资源提供服务。作为附带好处，由于被拦截的调用会立即失败，而不是等待超时，**wp-admin 不再卡在无响应的外部请求上**。

> **不只是为了断网** —— 它同样可以作为应对**缓慢网络请求**的通用方案：拦截那些迟缓或不可靠的第三方调用，它们会拖累 WordPress **前台和后台（管理界面）**的性能，即便互联网畅通也是如此。被拦截的调用会立即失败，因此不会因等待超时而拖慢页面加载。

## 拦截哪些内容

**服务器端（PHP / WP HTTP API）**
- 发往外部主机的 `wp_remote_*` 出站请求：WordPress.org 更新与版本检查、统计分析、OpenAI / Gemini 接口、远程字体等。
- 外部调用会*立即*失败（而不是等待超时）→ 离线时后台更快。

**浏览器端（渲染出的 HTML）**
- 外部 `<script src="…">`
- 外部 `<link rel="stylesheet">`（例如 Google Fonts）
- 资源提示：`preconnect` / `dns-prefetch` / `preload` / `prefetch`
- 外部 `<iframe>` → 替换为干净的本地占位符
- 内联统计代码片段：Google Analytics / Tag Manager、Microsoft Clarity、Ahrefs、Meta Pixel、Hotjar、Yandex Metrica
- *（可选）* 外部 `<img>` → 透明占位符

## 关键行为

- **你自己的域名及其全部子域名始终被允许**（例如 `my.yoursite.com`）。
- **允许列表**：添加任何其他应保持可达的主机。
- **检测到的主机面板**：插件见到的每一个外部主机都会被记录下来，便于你快速构建允许列表。
- **分类开关**：每一层拦截都可独立开启/关闭。
- **关闭即无效**：主开关关闭时，插件什么都不做 —— 可以安心长期保留安装，仅在需要时开启。
- **管理工具栏指示器**会显示 ReqLock 何时处于活动状态。

## 安装

1. 将 `reqlock` 文件夹复制到 `wp-content/plugins/`。
2. 在插件页面启用 **ReqLock**。
3. 进入 **设置 → ReqLock**。
4. 当外部互联网不可用时，将**主开关**打开。

## 设置总览

| 分组 | 选项 | 默认值 |
|---|---|---|
| 主开关 | ReqLock | **关** |
| 服务器端 | 拦截出站 WP HTTP API | 开 |
| 浏览器端 | 外部脚本 | 开 |
| 浏览器端 | 外部样式表 | 开 |
| 浏览器端 | 资源提示（preconnect/dns-prefetch/…） | 开 |
| 浏览器端 | 外部 iframe | 开 |
| 浏览器端 | 内联统计代码片段 | 开 |
| 浏览器端 | 外部图片 | 关 |
| 范围 | 同时净化 wp-admin | 关 |
| 范围 | 记录检测到的主机 | 开 |
| — | 主机允许列表 | 空 |

## 系统要求

- WordPress 5.0+
- PHP 7.2+（已在 7.4 / 8.1 / 8.2 上测试）

## 工作原理

- **HTTP API**：挂钩 `pre_http_request`，对任何外部主机返回 `WP_Error`（遵循允许列表），在请求离开服务器之前将其短路。
- **入队资源**：取消队列/注销任何来源为外部的已注册脚本/样式。
- **渲染出的 HTML**：缓冲页面输出，并以保守、锚定良好的模式剥离外部资源标签和已知的内联统计代码片段。每个被移除的元素都会留下一条可追溯的 `<!-- ReqLock blocked … -->` 注释（不会发起任何请求）。

它会刻意保留外部 `rel="canonical"` / `alternate` / 图标 `<link>` 不动（对 SEO 安全），并且绝不触碰相对/内部 URL。

> 注意：完全绕过 WordPress 的独立 PHP 入口点（例如使用原生 `curl` 的自定义脚本）无法被 WordPress 插件拦截，必须自行处理其外部调用。

## 许可证

GPL-2.0-or-later。参见 [LICENSE](LICENSE)。

## 致谢

由 **Rackset DevOps Team** 开发和维护。

网站：**[https://rackset.com](https://rackset.com)**

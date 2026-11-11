<?php
//版本重算落地: VERSION→1.14.5 + CHANGELOG 推算更正 + 新版本段 (执行后删除)
//1. VERSION
file_put_contents(__DIR__.'/../VERSION', "1.14.5\n");

//2. CHANGELOG: 更正推算说明 + 补 0.x 映射 + 新版本段
$f = __DIR__.'/../CHANGELOG.md';
$c = file_get_contents($f);
$crlf = (strpos($c, "\r\n") !== false);
$c = str_replace("\r\n", "\n", $c);

$oldIntro = "## 版本推算说明

- 基线 **1.0.0 = 6c50145**（SQLite 迁移 + 界面重构落地，对外接口与存储布局自此稳定；此前 MySQL→SQLite 属不兼容变更）
- 基线后共 31 个提交：feat 13 次（→MINOR）、fix 14 次（其中末次 feat \`1af229c\` 之后 3 个 fix →PATCH）、chore 4 次与文档变更不计
- 当前版本 **1.13.3**";
$newIntro = "## 版本推算说明

- 基线 **0.1.0 = 4ae4c45**（仓库首个提交，按规则新项目从 0.1.0 开始）
- 0.x 期：0.2.0←d1d2492（初始代码）｜0.3.0←485edcb（联系/关于页）｜0.4.0←234db8e（服务条款）｜0.5.0←9df4117（压缩/秒传/后台）
- **1.0.0 = 2c15603**（MySQL→SQLite 属不兼容变更，三级归档/OTP 架构落地，对外接口自此稳定）
- 1.0.0 后 feat 13 次（→MINOR 至 1.14.0）、末次 feat \`1af229c\` 之后 fix 4 个（→PATCH）、chore/docs 不计（历史共 40+ 提交）
- 2026-11-11 更正：此前按 1.0.0=6c50145 推算漏计 0.x 期 4 个 feat，现以首个 git 记录 4ae4c45 为准重算
- 当前版本 **1.14.5**";
if (strpos($c, $oldIntro) === false) { echo "INTRO-MISS\n"; exit(1); }
$c = str_replace($oldIntro, $newIntro, $c);

$newReleases = <<<'MD'
## [1.14.5] - 2026-11-11

### 修复
- 版本推算更正：以仓库首个 git 记录 4ae4c45 为 0.1.0 基线重算（修正此前漏计 0.x 期 feat 的偏差）

### 新增
- 管理后台移动端导航：≤900px 侧边栏隐藏后顶栏显示汉堡下拉（8 个入口含退出，active 态跟随）

## [1.14.4] - 2026-11-11

### 修复
- 会话 cookie 安全参数（HttpOnly/SameSite=Lax）与会话严格模式
- 上传日志文件名搜索的 LIKE 通配符转义（%/_ 不再破坏匹配语义）
- 轻量限速器槽位低频清理（防 rate_limit 表膨胀）
- 归档导入校验路径必须为图床结构（脏数据跳过）
- 后台页面安全响应头（X-Frame-Options: DENY / nosniff / Referrer-Policy）

## [1.14.3] - 2026-11-10
MD;
$anchor = "## [1.13.3] - 2026-11-10";
if (strpos($c, $anchor) === false) { echo "REL-MISS\n"; exit(1); }
$c = str_replace($anchor, $newReleases . "\n" . $anchor, $c);

if ($crlf) { $c = str_replace("\n", "\r\n", $c); }
file_put_contents($f, $c);
echo "changelog updated\n";

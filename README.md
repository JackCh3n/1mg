# 1mg 图床

## 实现功能

- [x] 鉴别图片等级 (moderatecontent 接口)
- [x] 图片压缩 (上传时 GD 重编码压缩 JPEG/PNG、超大图等比缩放至 2560px、BMP 自动转 PNG、按 EXIF 自动转正、GIF 保持原样)
- [x] 图片秒传 (前端 SparkMD5 分块计算 md5 预检 check.php,服务端 md5 去重,秒级返回已有图片地址)
- [x] 今日图片
- [x] 用户中心 (注册/登录/注销,我的图片管理,上传自动归属)
- [x] 上传限速/匿名删除链接/WebP转存/粘贴与全页拖拽上传/跟随系统暗色/磁盘统计与孤儿清理/内容审核UI/操作审计/API令牌与开放文档
- [x] 管理员 (登录/注销、登录限速、上传日志分页搜索、图片删除与恢复、网站设置、修改密码)
- [x] SQLite 存储 (单文件免安装,首次运行自动建表,已移除 MySQL 依赖)
- [x] 数据三级保留 (在线默认 7 天 → 归档压缩备份 data/archive/yyyymmdd.csv.gz 默认 180 天 → 到期自动删除)
- [x] 活跃日历 (GitHub contributions 风格的每日上传统计墙,颜色跟随站点皮肤)
- [x] 两步验证 (账号 + 密码 + TOTP 动态码,兼容 Google Authenticator 等)
- [x] 皮肤系统 (明亮/暗黑 + 自定义强调色,管理员设站点默认值,访客本地记忆切换)

## 使用帮助(与首页帮助区一致)

**怎么上传**: 点击「Browse」选择 / 拖拽图片到页面任意位置 / 截图后 `Ctrl+V` 粘贴;完成后自动生成 URL/HTML/BBCode/Markdown/删除链接五种格式,相同文件自动秒传。

**图片限制**: 单张 ≤5MB、一次 ≤10 张;格式 jpg/png/gif/bmp/webp(gif 保动图,其余自动压缩转 WebP);最长边超 2560px 自动等比缩放;按 EXIF 自动转正;每 IP 每小时限上传(默认 60 次,后台可调)。

**保存有效期**: 图片外链长期有效(文件不自动删除);上传记录在线保留默认 7 天,超期转入压缩归档备份保留 180 天(后台可调);违规图片随时删除,上传者也可用删除链接自主删除。

**API/第三方接入**: 标准 HTTP 上传接口见 `api.php` 文档页;已可接入 PicGo(custom web-uploader 插件)、uTools 及任何支持自定义 Web 上传的工具;开启接口令牌后需携带 `api_token` 或 `X-API-Token` 头。

**禁止内容**: 色情/暴力/恐怖血腥、侵权、违规二维码、违反法律法规的图片,详见服务条款。

## 本地测试

Windows 下双击 `dev.bat`(或命令行运行)即自动启动 PHP 内置服务器并打开浏览器;加参数 `--no-browser` 不打开浏览器。中文提示为 GBK 编码,适配中文 Windows 的 cmd。

## 安装

1. 需要 PHP 8.x,启用 pdo_sqlite / gd / zlib / openssl / curl 扩展
2. 上传全部文件,确保 `data/`、`i/`、`system/` 目录可写
3. 首次运行自动创建 `data/1mg.sqlite` 并建表
4. 前台用户可注册账号,上传自动归属,在「用户中心」管理自己的图片
5. 默认管理员账号 `admin` / `admin123456`,登录后台 `admin/login.php` 后请立即:
   - 修改密码
   - 在「网站设置 → 两步验证」绑定验证器

## 数据保留策略

| 层级 | 位置 | 默认保留 | 说明 |
|------|------|----------|------|
| 在线 | data/1mg.sqlite 主库 imginfo 表 | 7 天 | 超期记录自动归档 |
| 归档 | data/archive/yyyymmdd.csv.gz | 180 天 | CSV+gzip 按天压缩备份,后台可下载 |
| 清理 | — | — | 超过归档保留期的备份文件自动删除 |

- 归档只搬数据库记录,图片文件本身不受影响(不破坏外链)
- 触发方式: 后台仪表盘 6 小时自动执行一次 / 「立即归档」按钮 / cron 接口 `admin/archive.php?type=cron&who=鉴黄口令`
- 每日上传统计独立保存在 stats_daily 表,不随保留策略清理(活跃日历长期可看)

## 皮肤系统

- CSS 变量驱动:`view/site.css` 顶部设计令牌,`html[data-skin="dark"]` 覆盖为暗色
- 后台「网站设置 → 界面皮肤」设置站点默认皮肤与强调色(写入 system/config.user.json)
- 访客点击页面右上角月亮/太阳按钮切换,选择存 localStorage,优先于站点默认值

## 安全说明

- 后台所有页面需要登录,写操作全部校验 CSRF token;登录失败 5 次锁定 10 分钟
- 两步验证密钥加密存储;登录为 账号+密码+动态码 两步流程
- 上传文件校验:大小、扩展名白名单、getimagesize MIME、图片内容 webshell 特征扫描
- JPEG/PNG/BMP 落盘前经 GD 重编码,剥离夹杂在图片中的恶意代码
- `i/.htaccess` 禁止图片目录执行脚本;`data/.htaccess` 禁止访问数据库与归档
- 数据库操作统一走 Medoo 参数化查询;后台模板输出全部转义,防 SQL 注入与 XSS
- 生产环境关闭错误回显;后台设置保存在 `system/config.user.json`(不写 PHP 文件,避免代码注入)

## PHP 8 兼容性说明

依赖的 Medoo 1.5.7 与 Smarty 3.1.32 发布早于 PHP 8,vendor 内已打最小补丁:

- `vendor/catfan/medoo/src/Medoo.php`: 修复 10 处 `implode()` 参数顺序(PHP 8.0 起移除旧写法)
- `vendor/smarty/.../smartycompilerexception.php`: 移除与 `Exception::$line` 冲突的属性声明
- `vendor/smarty/.../smarty_internal_templatecompilerbase.php`: 不再直接写 `$e->line`(行号已在错误消息中)

若执行 `composer update` 升级依赖,以上补丁会被覆盖,请改用 medoo >= 1.7 / smarty >= 3.1.42。

## 借鉴参考

- 上传控件 —— [Bootstrap fileinput](https://github.com/kartik-v/bootstrap-fileinput)
- 数据库驱动扩展 —— [Medoo](https://medoo.in/)
- 模板引擎 —— [Smarty](https://www.smarty.net/)

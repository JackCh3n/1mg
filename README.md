# 1mg 图床

## 实现功能

- [x] 鉴别图片等级
- [x] 图片压缩 (上传时GD重编码压缩JPEG/PNG、超大图等比缩放至2560px、BMP自动转PNG、按EXIF自动转正、GIF保持原样)
- [x] 图片秒传 (前端SparkMD5分块计算md5预检check.php,服务端md5去重,秒级返回已有图片地址)
- [x] 今日图片
- [ ] 用户中心
- [x] 管理员 (登录/注销、登录限速、上传日志分页搜索、图片删除与恢复、网站设置、修改密码)

## 安装

1. 导入 `sql/1mg.sql` 到 MySQL
2. 修改 `system/config.php` 中的数据库配置
3. 确保 `i/`、`system/` 目录可写
4. 默认管理员账号 `admin` / `admin123456`,登录后台 `admin/login.php` 后请立即修改密码

## 安全说明

- 后台所有页面需要登录,写操作全部校验 CSRF token
- 上传文件校验:大小、扩展名白名单、getimagesize MIME、图片内容webshell特征扫描
- JPEG/PNG/BMP 落盘前经 GD 重编码,剥离夹杂在图片中的恶意代码
- `i/.htaccess` 禁止图片目录执行任何脚本
- 数据库操作统一走 Medoo 参数化查询;后台模板输出全部转义,防 SQL 注入与 XSS
- 生产环境关闭错误回显,后台设置保存在 `system/config.user.json`(不写PHP文件,避免代码注入)

## PHP 8 兼容性说明

本项目依赖的 Medoo 1.5.7 与 Smarty 3.1.32 发布早于 PHP 8,存在已知的兼容性致命错误,
已直接在 vendor 内打了最小补丁(未升级版本,避免行为变化):

- `vendor/catfan/medoo/src/Medoo.php`: 修复 10 处 `implode()` 参数顺序(PHP 8.0 起移除旧写法)
- `vendor/smarty/.../smartycompilerexception.php`: 移除与 `Exception::$line` 冲突的属性声明
- `vendor/smarty/.../smarty_internal_templatecompilerbase.php`: 不再直接写 `$e->line`(行号已在错误消息中)

若执行 `composer update` 升级依赖,以上补丁会被覆盖,请改用 medoo >= 1.7 / smarty >= 3.1.42。

## 借鉴参考

- 前端UI极大程度上Copy了  [sm.ms](https://sm.ms/)
- 上传控件 —— [Bootstrap file inpu](https://github.com/kartik-v/bootstrap-fileinput)
- 数据库驱动扩展 —— [Medoo](https://medoo.in/)

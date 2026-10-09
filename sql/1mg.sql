-- 1mg 图床 SQLite 数据结构
-- 说明: 程序首次运行时会在 data/1mg.sqlite 自动创建以下结构,此文件仅作参考
-- 默认管理员: admin / admin123456 (登录后台后请立即修改密码并绑定两步验证)

CREATE TABLE IF NOT EXISTS imginfo (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  path TEXT DEFAULT '',            --图片相对路径 i/yy mm/dd/xxx.jpg
  ip TEXT DEFAULT '',
  ua TEXT DEFAULT '',
  date TEXT DEFAULT '',            --Y-m-d H:i:s
  dir TEXT DEFAULT '',
  compress INTEGER DEFAULT 0,      --是否被压缩/转码
  level INTEGER DEFAULT 0,         --鉴黄等级 1大众 2青少年 3成人
  see INTEGER DEFAULT 1,           --1正常 0已删除/违规
  md5 TEXT DEFAULT '',             --文件md5(秒传)
  name TEXT DEFAULT '',
  size TEXT DEFAULT ''
);
CREATE UNIQUE INDEX IF NOT EXISTS uniq_md5 ON imginfo(md5);
CREATE INDEX IF NOT EXISTS idx_date ON imginfo(date);

CREATE TABLE IF NOT EXISTS admin (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE,
  password_hash TEXT,
  otp_secret TEXT DEFAULT '',      --TOTP密钥(加密存储)
  otp_enabled INTEGER DEFAULT 0,   --两步验证开关
  last_login TEXT DEFAULT ''
);

--每日上传统计(活跃日历数据源,独立于imginfo保留策略长期保存)
CREATE TABLE IF NOT EXISTS stats_daily (
  date TEXT PRIMARY KEY,           --Y-m-d
  count INTEGER DEFAULT 0
);

--数据归档说明(三级保留: 在线7天 -> 归档180天 -> 删除)
--归档文件为 data/archive/yyyymmdd.csv.gz,由 system/archive.php 生成,不存数据库

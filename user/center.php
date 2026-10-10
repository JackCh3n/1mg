<?php
/**
 * 用户中心:我的图片(在线保留期内的自己上传记录)
 */
require '../system/config.php';
require_once SYSTEM_ROOT.'auth.php';
require_once SYSTEM_ROOT.'user_auth.php';

if (empty($_SESSION['user_id'])) {
	header('Location: login.php');
	exit();
}

$msg='';
//删除自己的图片(软删+删文件,同匿名删除逻辑)
if (!empty($_POST['do']) && $_POST['do']==='del' && isset($_POST['key'])) {
	if (csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$key=(int)$_POST['key'];
		$row=$db->get('imginfo',['id','path','see'],['id'=>$key,'user_id'=>$_SESSION['user_id']]);
		if (!empty($row) && $row['see']) {
			$db->update('imginfo',['see'=>0],['id'=>$key]);
			trash_put($row['path']);
			$msg='图片已删除';
		}else{
			$msg='记录不存在或不属于你';
		}
	}else{
		$msg='页面已过期,请重试';
	}
}

//统计
$my_count=$db->count('imginfo',['user_id'=>$_SESSION['user_id'],'see'=>1]);
$my_size=0;
foreach ($db->select('imginfo',['size'],['user_id'=>$_SESSION['user_id'],'see'=>1]) as $r) {
	$my_size+=(int)$r['size'];
}

//分页
$page=isset($_GET['page'])?(int)$_GET['page']:1;
if ($page<1) $page=1;
$per_page=24;
$total_pages=max(1,(int)ceil($my_count/$per_page));
if ($page>$total_pages) $page=$total_pages;

$imgs=$db->select('imginfo',['id','path','name','size','date'],
	['user_id'=>$_SESSION['user_id'],'see'=>1,'ORDER'=>['id'=>'DESC'],'LIMIT'=>[$page*$per_page-$per_page,$per_page]]);

$e_title=htmlspecialchars($config['web']['title'],ENT_QUOTES,'UTF-8');
$e_user=htmlspecialchars($_SESSION['user_name'],ENT_QUOTES,'UTF-8');
$e_skin=htmlspecialchars($config['web']['default_skin']??'light',ENT_QUOTES,'UTF-8');
$e_accent=htmlspecialchars($config['web']['accent']??'',ENT_QUOTES,'UTF-8');
$e_msg=$msg?htmlspecialchars($msg,ENT_QUOTES,'UTF-8'):'';
$e_csrf=htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8');
?><!DOCTYPE html>
<html lang="zh-CN" data-default-skin="<?php echo $e_skin; ?>" data-default-accent="<?php echo $e_accent; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $e_title; ?> · 用户中心</title>
	<link href="../view/site.css" rel="stylesheet">
	<script src="../view/theme.js"></script>
</head>
<body>
<nav class="site-nav">
	<div class="nav-inner">
		<a class="brand" href="../"><span class="logo-dot"></span><?php echo $e_title; ?></a>
		<div class="nav-links"><a href="../">上传图片</a></div>
		<div class="nav-right">
			<a href="center.php"><b><?php echo $e_user; ?></b></a>
			<a href="logout.php">退出</a>
			<button type="button" class="theme-toggle" data-mg-theme-toggle title="切换明暗皮肤">
				<svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
				<svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M2 12h2.5M19.5 12H22M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/></svg>
			</button>
		</div>
	</div>
</nav>

<div class="container">
	<div class="hero" style="padding:34px 0 10px">
		<h1>我的图片</h1>
		<p>共 <?php echo $my_count; ?> 张 · 占用 <?php echo format_size($my_size); ?><?php $qm=(int)($config['web']['user_quota_mb'] ?? 0); if ($qm>0) { $pct=min(100, round($my_size/($qm*1048576)*100)); echo ' / 配额 '.$qm.'MB('.$pct.'%)'; } ?><?php $qd=(int)($config['web']['user_daily_files'] ?? 0); if ($qd>0) { echo ' · 今日 '.$db->count('imginfo',['user_id'=>$_SESSION['user_id'],'date[~]'=>date('Y-m-d')]).'/'.$qd.' 张'; } ?> · 仅显示在线保留期内(<?php echo (int)$config['web']['retention_online']; ?>天)的记录</p>
	</div>
	<?php if ($e_msg): ?><div class="alert alert-success"><?php echo $e_msg; ?></div><?php endif; ?>

	<div class="card">
		<?php if (empty($imgs)): ?>
		<div class="empty-tip" style="color:var(--muted);text-align:center;padding:40px 0">还没有图片,<a href="../">去上传</a></div>
		<?php else: ?>
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:16px">
			<?php foreach ($imgs as $im): ?>
			<?php
				$e_path=htmlspecialchars(url_path($im['path']),ENT_QUOTES,'UTF-8');
				$e_name=htmlspecialchars($im['name'],ENT_QUOTES,'UTF-8');
			?>
			<div style="border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;background:var(--panel-2)">
				<a href="../img.php?id=<?php echo (int)$im['id']; ?>"><img src="../<?php echo $e_path; ?>" style="width:100%;height:120px;object-fit:cover;display:block" loading="lazy" alt=""></a>
				<div style="padding:8px 10px">
					<div class="text-muted" style="font-size:11.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo $e_name; ?>"><?php echo $e_name; ?></div>
					<div class="text-muted" style="font-size:11px"><?php echo format_size((int)$im['size']); ?> · <?php echo htmlspecialchars($im['date'],ENT_QUOTES,'UTF-8'); ?></div>
					<form method="post" action="center.php" style="margin-top:8px" onsubmit="return confirm('确认删除该图片?')">
						<input type="hidden" name="_csrf" value="<?php echo $e_csrf; ?>">
						<input type="hidden" name="key" value="<?php echo (int)$im['id']; ?>">
						<input type="hidden" name="do" value="del">
						<button type="submit" class="btn btn-xs btn-danger" style="width:100%">删除</button>
					</form>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php if ($total_pages>1): ?>
		<div class="pager" style="margin-top:18px;justify-content:center">
			<?php for ($p=max(1,$page-4); $p<=min($total_pages,$page+4); $p++): ?>
				<?php if ($p==$page): ?><span class="current"><?php echo $p; ?></span>
				<?php else: ?><a href="center.php?page=<?php echo $p; ?>"><?php echo $p; ?></a><?php endif; ?>
			<?php endfor; ?>
		</div>
		<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<footer class="site-footer"><div class="container">© <?php echo (int)($config['web']['since_year'] ?? 2018); ?><?php if ((int)date('Y') > (int)($config['web']['since_year'] ?? 2018)) echo '-'.date('Y'); ?> <?php echo $e_title; ?> · Powered by 1mg</div></footer>
</body>
</html>

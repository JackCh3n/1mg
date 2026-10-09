<?php
/**
 * 注销登录
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_logout();
header('Location: login.php');

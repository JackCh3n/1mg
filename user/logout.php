<?php
require '../system/config.php';
require_once SYSTEM_ROOT.'user_auth.php';
user_logout();
header('Location: login.php');

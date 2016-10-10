<?php
session_start();
require '../core/ZCaptcha.php';  
$_vc = new ZCaptcha(); 
$_vc->doimg();
$_SESSION['authnum_session'] = $_vc->getCode();

?>


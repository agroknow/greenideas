<?php

$JQUERY_BASE="../js/";
$JVAL_PATH="js/jvalidate/";
$AUTO_BASE="js/autocomplete/";
$SELECT_BOXES_BASE="js/";
$CUSTOM_SCRIPTS_BASE="js/";
$scriptname=explode('/',$_SERVER['PHP_SELF']);
$urllinkbase="http://".$_SERVER['SERVER_NAME']."/".$scriptname[1]."/";


//PHPMailer Config
//more configuration in portal\custom\contact\Classes\phpmailer\\mailer_conf.php
$EMAIL_TO[0]="stoitsis@gmail.com";
$EMAIL_NAME_TO[0]="test";
$EMAIL_FORMAT="1"; //1 html, 0 plain text

?>
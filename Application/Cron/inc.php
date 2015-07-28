<?php
$begin = microtime(true);
$c = include dirname(__DIR__) .  '/Common/Conf/database.php';
e("开始于 " . date('Y-m-d H:i:s') . " ..."); 

$mysqli = new mysqli($c['DB_HOST'] . ':' . $c['DB_PORT'], $c['DB_USER'], $c['DB_PWD'], $c['DB_NAME']) or finish($mysqli->connect_error); 
$mysqli->query("SET NAMES '{$c['DB_CHARSET']}'");

function finish($errStr = '') {
    global $mysqli, $fp;
    if ($errStr) e($errStr);
    e("耗时：" . (microtime(true) - $GLOBALS['begin']) . "\n");
    $mysqli->close();
    fclose($fp);
    die();
}

function e($errStr) {
    global $fp;
    fwrite($fp, $errStr . "\n");
}

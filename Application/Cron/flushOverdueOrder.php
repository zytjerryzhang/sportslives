<?php
/**
 * 刷新半小时内超时订单
 * 每两分钟一次
 * "*\/2 * * * * php flushOverdueOrder.php"
 */
set_time_limit(0);
$fp = fopen(dirname(__DIR__) . '/Runtime/Logs/flushorder.log', 'a');
include __DIR__ . '/inc.php';

$to     = $c['DB_PREFIX'] . 'order';
$toi    = $c['DB_PREFIX'] . 'order_item';
$ts     = $c['DB_PREFIX'] . 'site';
$time = time();
$before900Sec = date('Y-m-d H:i:s', $time - 900);
$before1800Sec = date('Y-m-d H:i:s', $time - 1800);
$sql = "UPDATE $to o,$toi oi,$ts s 
    SET s.saled_num=s.saled_num-oi.order_number,o.pay_status=3 
    WHERE s.id=oi.site_id AND o.id=oi.order_id AND o.pay_status=0
        AND o.create_time BETWEEN '$before1800Sec' AND '$before900Sec'";
$res = $mysqli->query($sql);
finish();

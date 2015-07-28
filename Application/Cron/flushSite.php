<?php
/**
 * 生成每天的场次价格 
 * 每天一次
 * "1 0 * * * php flushSite.php"
 */
set_time_limit(0);
$fp = fopen(dirname(__DIR__) . '/Runtime/Logs/flushSite.log', 'a');
include __DIR__ . '/inc.php';

$tab = $c['DB_PREFIX'] . 'site';

$time = strtotime('+6 days');
$sixDaysLater = date('Y-m-d', $time);
$week  = date('w', $time);

$sql = "SELECT distinct 
    gym_id gid, pro_id pid, site_id sid 
    FROM $tab";
$res = $mysqli->query($sql);
if (!$res) {
    e("还没有录入场地信息。");
    finish();
}

while ($r = $res->fetch_assoc()) {
    $gid = $r['gid'];
    $pid = $r['pid'];
    $sid = $r['sid'];
    //检查是否有今天的场地信息
    $sql = "SELECT id FROM $tab 
        WHERE gym_id=$gid 
        AND pro_id=$pid
        AND site_id=$sid
        AND date='$sixDaysLater'";
    $res2 = $mysqli->query($sql);
    if (!$res2 || $res2->num_rows) continue;

    //寻找合适的继承对象
    $sql = "SELECT date FROM $tab 
        WHERE gym_id=$gid
        AND pro_id=$pid
        AND site_id=$sid
        AND date < '$sixDaysLater'
        AND ((DATE_FORMAT(date, '%w')=$week AND is_onetime>1) 
            OR is_onetime=3)
        ORDER BY date DESC
        LIMIT 1";
    $res2 = $mysqli->query($sql);
    if (!$res2->num_rows) {
        e("gym_id=$gid AND pro_id=$pid AND site_id=$sid 没有找到合适的继承对象"); 
        continue;
    }
    $date = $res2->fetch_assoc();
    $date = $date['date'];

    $sql = "SELECT * FROM $tab 
        WHERE gym_id=$gid
        AND pro_id=$pid
        AND site_id=$sid
        AND date = '$date'";
    $res2 = $mysqli->query($sql);
    $val  = "";
    while ($r2 = $res2->fetch_assoc()) {
        unset($r2['id'], $r2['create_time'], $r2['update_time']);
        $r2['date']         = $sixDaysLater;
        $r2['saled_num']    = 0;
        $r2['is_onetime']   = 2;
        $sql = "INSERT INTO $tab(" .join(',', array_keys($r2)) . ") VALUES";
        $val .= "('" . join("','", $r2) . "'),";
    }
    $sql = trim($sql . $val, ','); 
    $res2 = $mysqli->query($sql);
}
finish();

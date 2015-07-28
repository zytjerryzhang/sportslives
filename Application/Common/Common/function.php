<?php
/**
 * 打印
 * @param $data
 * @param int $is_die
 */
function pr($data, $is_die = 0)
{
    echo '<pre>';
    print_r($data);
    echo '</pre><br>';
    if ($is_die == 1) {
        exit;
    }
}

/**
 * 帐号显示
 * @param $str
 * @return string
 */
function name_fmt($str)
{
    return mb_substr($str, 0, 3, 'utf-8') . '****';
}

/**
 * 可使用 手机显示、身份证显示、银行卡显示 等等
 * @param $str 过滤的字符
 * @param $start 保留前几个字符
 * @param $last 保留后几个字符
 * @return string
 */
function str_fmt($str,$start,$last){
    $len = strlen($str);
    $begin = substr($str, 0, $start);
    $end = substr($str, $len-$last, $last);
    $middle = str_pad('',$len -$start -$last,'*');
    return $begin.$middle.$end;
}

/**
 * 替换数据
 * @param $str
 * @param array $data
 * @return string
 */
function strreplace($str, $data = array(),$start='#',$end='#')
{
    if(is_array($data)){
        foreach ($data as $k => $v) {
            $str = str_replace($start.$k.$end, $v, $str);
        }
    }
    return trim($str);
}

/**
 * 检查参数，如username
 * @param string $param
 * @param mixed $val
 */
function check_param($param, $val) {
	if (!($param)) {
		return false;
	}

	$dictParam = $param;
	$dataType = $dictParam['DataType'];
	$pattern = $dictParam['Pattern'];

	switch ($dataType) {
		case 'int':
		case 'float':
			if ($val && is_numeric($val)) {
				return preg_match($pattern, $val);
			}
			break;
		case 'string':
			if ($val && is_string($val)) {
				return preg_match($pattern, $val);
			}
			break;
		default:
			return false;
			break;
	}
	return false;
}


/**
 * 以post的方式发送http请求，并返回http请求的结果
 * @param string $url
 * @param array $param
 * @return array|false    只返回文件内容，不包括http header
 */
function curl_post($url, $param)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    //设置该选项，表示函数curl_exec执行成功时会返回执行的结果，失败时返回 FALSE
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);    //POST请求
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));    //http的Get方式，请求字段
    curl_setopt($ch, CURLOPT_VERBOSE, 1);    //启用时会汇报所有的信息
    $rs = curl_exec($ch);
    if (curl_errno($ch)) {
        return false;
    }
    curl_close($ch);
    return json_decode($rs, true);
}

/**
 * 设置Cookie
 * @param string $name
 * @param string $value
 * @param int $sec
 */
function my_setcookie($name, $value, $sec = 0)
{
    $nowTime = time();
    $expire = $sec ? $nowTime + $sec : 0;
// 		return setcookie($name, $value, $expires, '/', $_SERVER['HTTP_HOST'], false, true);
    return setcookie($name, $value, $expire, '/');
}

/**
 * 将数据格式化成树结构
 * 此方法简单,且效率较高,但是有一个缺点,即父类必须在子类前面出现,否则子类将成为最大一级单位。
 * 在此分类管理中,必须先建立父类,才允许建立子类,故此方法够用。
 * @param array $items
 * @param array $k 兼容子目录出现在前
 * @return array
 */
function genTree($itree)
{
    foreach ($itree as $item) {
        $item['key'] = count(explode('/', $item['level'])) - 1;  //等级
        $temp[$item['id']] = $item;
        if (isset($temp[$item['pid']])) {
            $temp[$item['pid']]['son'][] = &$temp[$item['id']];
        } else {
            $tree[$item['id']] = &$temp[$item['id']];
        }
    }
    ksort($tree);
    return $tree;
}

/**
 * 获取是否存在父目录
 * Enter description here ...
 * @param unknown_type $id
 */
function getPaentId($id)
{
    $menu = M('AMenu');
    $menu_info = $menu->where(array('id' => $id))->find();
    return (empty($menu_info)) ? false : $menu_info;
}

/**
 * 邮箱验证
 * Enter description here ...
 * @param unknown_type $str
 */
function fun_email($str)
{
    if (strlen($str) > 0 && !preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,8}$/ix", $str)) {
        return false;
    } else {
        return true;
    }
}


/**
 * 计算身份证校验码，根据国家标准 GB 11643-1999
 * 其中的$idcard_base是指身份证中的本位码，本位码是18位身份证里才有的，
 * 也就是18位身份证的前17位，最后一位称为校验码,
 * 一般在使用的时候不用直接调用idcard_verify_number()，
 * 平时的应用大多是使用后两个函数，这些函数都没有关心身份证字符串的格式问题，
 * 在调用前自行进行格式检查。
 */
function idcard_verify_number($idcard_base)
{
    if (strlen($idcard_base) != 17) {
        return false;
    }
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);// 加权因子
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');// 校验码对应值
    $checksum = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++) {
        $checksum += substr($idcard_base, $i, 1) * $factor[$i];
    }
    $mod = $checksum % 11;
    $verify_number = $verify_number_list[$mod];
    return $verify_number;
}


/**
 * 18位身份证校验码有效性检查
 * Enter description here ...
 * @param unknown_type $idcard
 */
function idcard_checksum18($idcard)
{
    if (strlen($idcard) != 18) {
        return false;
    }
    $idcard_base = substr($idcard, 0, 17);
    if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
        return false;
    } else {
        return true;
    }
}

/**
 * 检查是否全中文.仅限UTF-8编码
 * @param String $string
 * @return Boolean
 */
function checkChinese($string)
{
    return preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $string) ? true : false;
}

/**
 * 是否为电话号码
 * Enter description here ...
 * @param unknown_type $val
 */
function isMobile($val)
{
    return preg_match("/^((\(\d{3}\))|(\d{3}\-))?(1)\d{10}$/", $val) ? true : false;
}


/**
 * 判断是否为座机
 * Enter description here ...
 * @param unknown_type $val
 */
function isphone($val)
{
    return preg_match("/^(((\(\d{3}\))|(\d{3}\-))|(\(0\d{2,3}\)|0\d{2,3}-))?[1-9]\d{6,7}(\-\d{3,4})?$/", $val);
}


/**
 * 只能为数字验证
 * Enter description here ...
 * @param unknown_type $value
 */
function onlyNumber($value)
{
    return preg_match("/^\d*$/", $value) ? true : false;
}

/**
 * 格式化数据类型
 * Enter description here ...
 * @param unknown_type $money
 */
function m_format($money)
{
    return number_format($money, 2);
}

/**
 * 记录两个数组值的变化
 * Enter description here ...
 * @param unknown_type $target
 * @param unknown_type $soure
 */
function changeRecord($target, $soure)
{
    $desc = '';
    if (!$target || !$soure || !is_array($target) || !is_array($soure)) {
        return $desc;
    }
    foreach ($target as $tK => $tV) {
        if (@$soure[$tK] != $tV) {
            $desc .= $tK . ':' . $soure[$tK] . '=>' . $tV . ';';
        }
    }
    return trim($desc, ';');
}

/**
 * unset数组中的多个键
 * Enter description here ...
 * @param unknown_type $aTarget
 * @param unknown_type $aVars
 * example unsetArray(array('a'=>1,'b'=>2,'c'=>3),array('a','c'))
 * return array('b'=>2)
 */
function unsetArray($aTarget, $aVars)
{
    if (!$aVars) {
        return $aTarget;
    } elseif (is_string($aVars)) {
        $aVars = explode(',', $aVars);
    }
    foreach ($aVars as $v) {
        if (isset($aTarget[$v])) {
            unset($aTarget[$v]);
        }
    }
    return $aTarget;
}


/**
 * 计算两个日期相差天数
 * @param unknown_type $date1
 * @param unknown_type $date2
 * @param unknown_type $t
 * @return number
 */
function days($date1, $date2)
{
    $temp = strtotime($date1) - strtotime($date2);
    $days = $temp / (60 * 60 * 24);
    return $days;
}


/**
 * 对象转数组（支持多层次）
 * @param unknown_type $d
 * @return multitype:|multitype:
 */
function objectToArray($d)
{
    if (is_object($d)) {
        $d = get_object_vars($d);
    }
    if (is_array($d)) {
        return array_map(__FUNCTION__, $d);
    } else {
        return $d;
    }
}

/**
 * 数组转对象（支持多层次）
 * @param unknown_type $d
 * @return multitype:|multitype:
 */
function arrayToObject($d)
{
    if (is_array($d)) {
        return (object)array_map(__FUNCTION__, $d);
    } else {
        return $d;
    }
}

/**
 * 判断是否为日期
 * Enter description here ...
 * @param unknown_type $value
 */
function is_date($value)
{
    $arr = explode('-', $value);
    $bool = checkdate($arr[1], $arr[2], $arr[0]) ? true : false;
    if ($bool) {
        return strtotime($value);
    }
    return '';
}

/**
 * 查询客户端IP
 * Enter description here ...
 */
function getIp(){
	 $unknown = 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    /*
	处理多层代理的情况
	或者使用正则方式：$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
	*/
    if (false !== strpos($ip, ',')) $ip = reset(explode(',', $ip));
    return $ip;
}

/**
 * 查看IP地址所在区域
 * Enter description here ...
 * @param unknown_type $ip
 */
function getIpLookup($ip = ''){
    if(empty($ip)){
        $ip = getIp();
    }
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if(empty($res)){ return false; }
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $res, $jsonMatches);
    if(!isset($jsonMatches[0])){ return false; }
    $json = json_decode($jsonMatches[0], true);
    if(isset($json['ret']) && $json['ret'] == 1){
        $json['ip'] = $ip;
        unset($json['ret']);
    }else{
        return false;
    }
    return $json;
}

/**
 * 计算两个时间段间的所有月份
 * Enter description here ...
 * @param unknown_type $start
 * @param unknown_type $end
 */
function getDifferMonth($start, $end)
{
    $monarr = array();
    if (!$start || !$end) return false;
    if ($start > $end) return false;
    $monarr[] = date('Ym', $start); // 取的当前月;
    while (($start = strtotime('+1 month', $start)) <= $end) {
        $monarr[] = date('Ym', $start); // 取得递增月;
    }
    return $monarr;
}


/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1)
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * +----------------------------------------------------------
 * @return string
+----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '')
{
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) {//位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
    } else {
// 中文随机字
        for ($i = 0; $i < $len; $i++) {
            $str .= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}


/**
 * 获取下期还款时间
 * @static
 * @param $date
 * @return int
 */
function getNextMonth($date, $period)
{
    $firstday = date('Y-m-01 H:i:s', $date);
    $next_month = strtotime("$firstday +" . ($period + 1) . " month -1 day");
    if (date('d', $date) > date('d', $next_month)) {
        $r = mktime(23, 59, 59, date('m', $next_month), date('d', $next_month), date('Y', $next_month));
    } else {
        $date = date('Y-m-d H:i:s', $date);
        $r = strtotime("$date +$period month");
    }
    return $r;
}

function to_num($num){
    return $num;
}

/**
 ** 小数点后截取函数
 * pengbo' 20130329
 * @param $number 要截取的变量  $pointpos 小数后的位置
 * @return 如 ：2.589   截取 2.58 不足时补回
 */
function subnumber($number,$pointpos,$pFull=true)
{
    $pos1= strpos($number,'.');
    if($pFull){
        if($pos1>0){
            $xiaoshuw=strlen($number)-$pos1-1;
            if($xiaoshuw<$pointpos)
            {
                $tmpstr=str_pad($number,strlen($number)+($pointpos-$xiaoshuw),'0',STR_PAD_RIGHT);
                $back=substr($tmpstr,0, $pos1+$pointpos+1);
                $newstr=substr($back,strlen($back)-1,1);
                if($newstr=='.'){
                    $back=substr($back,0,strlen($back)-1);
                }
                return $back;
            }else {
                $back=substr($number,0, $pos1+$pointpos+1);
                $newstr=substr($back,strlen($back)-1,1);
                if($newstr=='.'){
                    $back=substr($back,0,strlen($back)-1);
                }
                return  $back;
            }
        }else {
            $number=$number .'.';
            $tmpstr=str_pad($number,strlen($number)+$pointpos,'0',STR_PAD_RIGHT);
            return  $tmpstr;
        }
    }else{
        if($pos1>0){
            $xiaoshuw=strlen($number)-$pos1-1;
            if($xiaoshuw<$pointpos)
            {
                return $number;
            }else{
                $len=strlen($number);
                $newstr=substr($number,$len-1,1);
                while ($newstr=='0' ) {
                    $number=substr($number,0,$len-1);
                    $len-=1;
                    $newstr=substr($number,$len-1,1);;
                }
                if( $newstr=='.'){$number=substr($number,0,$len-1);}
                return substr($number,0, $pos1+$pointpos+1);;
            }
        }else{
            return   $number;
        }
    }
}



/**
 * 数字金额转换成中文大写金额的函数
 * String Int $num 要转换的小写数字或小写字符串
 * return 大写字母
 * 小数位为两位
 * */
function capital_money($num = 0) {
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num * 100;

    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        $num = $num / 10;
        $num = (int) $num;
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j - 3;
            $slen = $slen - 3;
        }
        $j = $j + 3;
    }

    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    if (empty($c)) {
        return "零元整";
    } else {
        return $c . "整";
    }
}


//$result=repayMethod(1,10000,18,1);
//
//pr($result,1);
/**
 * 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null){
    $config = C('THINK_EMAIL');
    vendor('PHPMailer#class'); //从PHPMailer目录导class.phpmailer.php类文件
    $mail             = new phpmailer(); //PHPMailer对象
    $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();  // 设定使用SMTP服务
    $mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';                 // 使用安全协议 tls
    $mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
    $mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
    $mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
    $mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
    $mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
    $replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
    $replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];

    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject    = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);

    if(is_array($attachment)){ // 添加附件
        foreach ($attachment as $file){
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}


/**
 * 剩余天数计算
 * @param $second
 * @return float|int
 */
function last_day($second) {
    $day = floor($second / (3600 * 24));
    $second = $second % (3600 * 24);
    if ($second == 0) {
        return $day;
    } else if ($second > 0) {
        return $day + 1;
    } else {
        return 0;
    }
}

/**
 * 得到新订单号
 * @static
 * @param string $prefix
 * @return string
 */
function get_order_sn($prefix = 'JK') {
    mt_srand((double) microtime() * 1000000);
    return $prefix . '_' . date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 计算两个时间间隔多少天小时分秒
 * Enter description here ...
 * @param unknown_type $begin_time
 * @param unknown_type $end_time
 */
function timediff($begin_time, $end_time){
  $begin_time = ($begin_time>0) ? $begin_time : time();
  if ( $begin_time < $end_time ) {
    	$starttime = $begin_time;
    	$endtime = $end_time;
  } else {
  		return '0天0小时0分';
  }
  $timediff = $endtime - $starttime;
  $days = intval( $timediff / 86400 );
  $remain = $timediff % 86400;
  $hours = intval( $remain / 3600 );
  $remain = $remain % 3600;
  $mins = intval( $remain / 60 );
  $secs = $remain % 60;
  $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
  return $days.'天'.$hours.'小时'.$secs.'分';
}

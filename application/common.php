<?php
/*
'软件名称：苹果CMS
'开发作者：MagicBlack  QQ：479025  官方网站：http://www.maccms.com/
'--------------------------------------------------------
'适用本程序需遵循 CC BY-ND 许可协议
'这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
'不允许对程序代码以任何形式任何目的的再发布。
'--------------------------------------------------------
*/
error_reporting(E_ERROR | E_PARSE);

use think\Db;

// 打印函数
function p($array, $ext = 0)
{
    echo "<pre>";
    print_r($array);
    if ($ext == 0) {
        die;
    }
}

// 打印函数
function v($array, $ext = 0)
{
    echo "<pre>";
    var_dump($array);
    if ($ext == 0) {
        die;
    }
}

// 获取国际排序
function getCountrySort($country)
{
    if ($country == "") {
        return 0;
    }

    $countryArray = [
        '中国大陆' => 10,
        '大陆' => 10,
        '中国' => 10,
        '内地' => 10,
        '中国香港' => 9,
        '香港' => 9,
        '中国台湾' => 8,
        '台湾' => 8,
        '日本' => 7,
        '韩国' => 6,
        '美国' => 5,
        '英国' => 4,
        '法国' => 4,
        '德国' => 4,
        '西班牙' => 3,
        '泰国' => 3,
        '印度' => 3,
        '加拿大' => 3,
        '俄罗斯' => 3,
        '意大利' => 3,
        '澳大利亚' => 3,
        '新加坡' => 3,
        '巴西' => 3,
        '墨西哥' => 3,
        '荷兰' => 3,
        '丹麦' => 3,
        '马来西亚' => 2,
        '瑞典' => 2,
        '其它' => 1
    ];

    $sort = $countryArray[$country] ?? 0;
    return $sort;
}

function randomFloat($min = 0, $max = 10)
{
    $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
    return sprintf("%.1f", $num);

}

function type_extend($extend)
{
    $type = isset($extend['class']) && $extend['class'] != "" ? explode(',', '全部,' . $extend['class']) : "";
    $area = isset($extend['area']) ? explode(',', '全部,' . $extend['area']) : "";
    $year = isset($extend['year']) ? explode(',', '全部,' . $extend['year']) : "";
    $sort = ['排序', '评分最高', '最近更新'];

    $data = [];
    if ($type != "") {
        array_push($data, ['name' => 'type', "data" => $type]);
    }
    $data[] = ['name' => 'area', "data" => $area];
    $data[] = ['name' => 'year', "data" => $year];
    $data[] = ['name' => 'sort', "data" => $sort];

    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

function getScreen($id)
{
    $where = [
        'type_id' => $id
    ];
    $res = model("Type")->infoData($where);
    $res = $res['info'] ?? [];

    $type = explode(',', '全部,' . $res["type_extend"]["class"]);
    $area = explode(',', '全部,' . $res["type_extend"]["area"]);
    $year = explode(',', '全部,' . $res["type_extend"]["year"]);
    $sort = ['排序', '评分最高', '最近更新'];
    return array(
        array('name' => 'type', "data" => $type),
        array('name' => 'area', "data" => $area),
        array('name' => 'year', "data" => $year),
        array('name' => 'sort', "data" => $sort),
    );
}

function selectOption($id, $type_name)
{
    $where = [
        'type_id' => $id
    ];
    $res = model("Type")->infoData($where);
    $res = $res['info'] ?? [];
    $classKey = 0;  // 类型
    $areaKey = 0;  // 地区

    if ($id == 1) {
        $class = explode(',', '全部,' . $res["type_extend"]["class"]);
        $classKey = array_search($type_name, $class) ?? 0;
    } else {
        $typeNameArray = [
            2 => ['国产剧' => '大陆', '港台剧' => '香港', '日韩剧' => '韩国', '欧美剧' => '美国', '泰剧' => '泰国'],
            3 => ['内地综艺' => '大陆', '港台综艺' => '台湾', '日韩综艺' => '韩国', '欧美综艺' => '欧美'],
            4 => ['国产动漫' => '国产', '日韩动漫' => '日本', '港台动漫' => '其他', '欧美动漫' => '欧美'],
            33 => []
        ];

        $type_name = $typeNameArray[$id][$type_name] ?? "";
        $class = explode(',', '全部,' . $res["type_extend"]["area"]);
        $areaKey = array_search($type_name, $class) ?? "0";
    }

    return [intval($classKey), intval($areaKey), 0, 0];
}

// 电视剧 备注
function vodRemark($data)
{
    $remark = $data['vod_remarks'];
    if ($data['type_id'] == 2 || $data['type_id_1'] == 2) {
        if ($data['vod_serial'] != 0) {
            $remark = "更新至" . intval($data['vod_serial']) . "集";
        }
    }
    return $remark;
}

/**
 * 多维对象转换为数组
 */
function objectToArray($object)
{
    //数据处理
    return json_decode(json_encode($object), true);
}

// 应用公共文件
function json_return($msg, $code = 1, $data = '')
{
    if (is_array($msg)) {
        return json_encode($msg, JSON_UNESCAPED_UNICODE);
    } else {
        $rs = ['code' => $code, 'msg' => $msg, 'data' => ''];
        if (is_array($data)) $rs['data'] = $data;
        return json_encode($rs, JSON_UNESCAPED_UNICODE);
    }
}

// 应用公共文件
function mac_return($msg, $code = 1, $data = '')
{
    if (is_array($msg)) {
        return json_encode($msg);
    } else {
        $rs = ['code' => $code, 'msg' => $msg, 'data' => ''];
        if (is_array($data)) $rs['data'] = $data;
        return json_encode($rs);
    }
}

function mac_run_statistics()
{
    $t2 = microtime(true) - MAC_START_TIME;
    $size = memory_get_usage();
    $memory = mac_format_size($size);
    unset($unit);
    return 'Processed in: ' . round($t2, 4) . ' second(s),&nbsp;' . $memory . ' Mem On.';
}

function mac_format_size($s = 0)
{
    if ($s == 0) {
        return '0 kb';
    }
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return round($s / pow(1024, ($i = floor(log($s, 1024)))), 2) . ' ' . $unit[$i];
}

function mac_read_file($f)
{
    return @file_get_contents($f);
}

function mac_write_file($f, $c = '')
{
    $dir = dirname($f);
    if (!is_dir($dir)) {
        mac_mkdirss($dir);
    }
    return @file_put_contents($f, $c);
}

function mac_mkdirss($path, $mode = 0777)
{
    if (!is_dir(dirname($path))) {
        mac_mkdirss(dirname($path));
    }
    if (!file_exists($path)) {
        return mkdir($path, $mode);
    }
    return true;
}

function mac_rmdirs($dirname, $withself = true)
{
    if (!is_dir($dirname))
        return false;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    if ($withself) {
        @rmdir($dirname);
    }
    return true;
}

function mac_copydirs($source, $dest)
{
    if (!is_dir($dest)) {
        mkdir($dest, 0755);
    }
    foreach (
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            $sontDir = $dest . DS . $iterator->getSubPathName();
            if (!is_dir($sontDir)) {
                mkdir($sontDir);
            }
        } else {
            copy($item, $dest . DS . $iterator->getSubPathName());
        }
    }
}

function mac_arr2file($f, $arr = '')
{
    if (is_array($arr)) {
        $con = var_export($arr, true);
    } else {
        $con = $arr;
    }
    $con = "<?php\nreturn $con;";
    mac_write_file($f, $con);
}

function mac_replace_text($txt, $type = 1)
{
    if ($type == 1) {
        return str_replace('#', Chr(13), $txt);
    }
    return str_replace(chr(13), '#', str_replace(chr(10), '', $txt));
}

function mac_compress_html($s)
{
    $s = str_replace(array("\r\n", "\n", "\t"), array('', '', ''), $s);
    $pattern = array(
        "/> *([^ ]*) *</",
        "/[\s]+/",
        "/<!--[\\w\\W\r\\n]*?-->/",
        // "/\" /",
        "/ \"/",
        "'/\*[^*]*\*/'"
    );
    $replace = array(
        ">\\1<",
        " ",
        "",
        //"\"",
        "\"",
        ""
    );
    return preg_replace($pattern, $replace, $s);
}

function mac_build_regx($regstr, $regopt)
{
    return '/' . str_replace('/', '\/', $regstr) . '/' . $regopt;
}

function mac_reg_replace($str, $rule, $value)
{
    $res = '';
    $rule = mac_build_regx($rule, "is");
    if (!empty($str)) {
        $res = preg_replace($rule, $value, $str);
    }
    return $res;
}

function mac_reg_match($str, $rule)
{
    $res = '';
    $rule = mac_build_regx($rule, "is");
    preg_match_all($rule, $str, $mc);
    $mfv = $mc[1];
    foreach ($mfv as $f => $v) {
        $res = trim(preg_replace("/[ \r\n\t\f]{1,}/", " ", $v));
        break;
    }
    unset($mc);
    return $res;
}

function mac_redirect($url, $obj = '')
{
    echo '<script>' . $obj . 'location.href="' . $url . '";</script>';
    exit;
}

function mac_alert($str)
{
    echo '<script>alert("' . $str . '\t\t");history.go(-1);</script>';
}

function mac_alert_url($str, $url)
{
    echo '<script>alert("' . $str . '\t\t");location.href="' . $url . '";</script>';
}

function mac_jump($url, $sec = 0)
{
    echo '<script>setTimeout(function (){location.href="' . $url . '";},' . ($sec * 1000) . ');</script><span>暂停' . $sec . '秒后继续  >>>  </span><a href="' . $url . '" >如果您的浏览器没有自动跳转，请点击这里</a><br>';
}


function switchChnNumber($time)
{
    if (!empty($time)) {
        $replaceValueC = [
            "零" => 0,
            "一" => 1,
            "二" => 2,
            "两" => 2,
            "三" => 3,
            "四" => 4,
            "五" => 5,
            "六" => 6,
            "七" => 7,
            "八" => 8,
            "九" => 9,
            "十" => '10',
        ];
        //拆分含有中文的字符串
        $arrTime = preg_split('/(?<!^)(?!$)/u', $time);
        foreach ($arrTime as $key => $value) {
            if (isset($replaceValueC[$value]) && $replaceValueC[$value] != '') {
                $arrTime[$key] = $replaceValueC[$value];
            } else {
                $arrTime[$key] = $value;
            }
        }

        return implode("", $arrTime);
    } else {
        return $time;
    }
}

function msectime() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.3f',(floatval($t1)+floatval($t2)));
}

//截取指定两个字符之间的字符串
function cut($begin,$end,$str){
    $b = mb_strpos($str,$begin) + mb_strlen($begin);
    $e = mb_strpos($str,$end) - $b;
    return mb_substr($str,$b,$e);
}
function findTitle($k_p_val, $type = 0)
{
    $array = explode('#', $k_p_val['m3u8_url']);
    foreach ($array as $k => $v) {
        $count = substr_count($v, '$');
        if ($count > 0) {
            return explode("$", $v)[0] ?? '';
        }
    }
    return '';
}

function findNumAll($str = '')
{
    $count = substr_count($str, '集');
    $count1 = substr_count($str, '第');
    if ($count > 0 && $count1 > 0) {
        $str = cut('第','集',$str);
//        $count3 = substr_count($str, '-');
//        if ($count3 > 0) {
//            $str = explode('-',$str)[0]??$str;
//        }
    }
    $str = switchChnNumber($str);
    $str = trim($str);
    if (empty($str)) {
        return '';
    }
    $temp = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '');
    $result = '';
    for ($i = 0; $i < strlen($str); $i++) {
        if (in_array($str[$i], $temp)) {
            $result .= $str[$i];
        }
    }
    if (empty($result)) {
        return '1';
    }
    return $result;
}

function mac_echo($str)
{
    echo $str . '<br>';
    \think\Log::log($str);
    if (substr(PHP_SAPI_NAME(), 0, 3) !== 'cli') {
        ob_flush();
        flush();
    }

    $dir = LOG_PATH . 'cjLog' . DS;
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    \think\Log::init([
        'type' => \think\Env::get('log.type', 'test'),
        'path' => $dir,
        'level' => ['info'],
    ]);
    \think\Log::info($str);
}

function findNum($str = '')
{
    $str = trim($str);
    if (empty($str)) {
        return 0;
    }
    $temp = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
    $result = '';
    for ($i = 0; $i < strlen($str); $i++) {
        if (in_array($str[$i], $temp)) {
            $result .= $str[$i];
        }
    }
    return $result;
}


function mac_day($t, $f = '', $c = '#FF0000')
{
    if (empty($t)) {
        return '';
    }
    if (is_numeric($t)) {
        $t = date('Y-m-d H:i:s', $t);
    }
    $now = date('Y-m-d', time());
    if ($f == 'color' && strpos(',' . $t, $now) > 0) {
        return '<font color="' . $c . '">' . $t . '</font>';
    }
    return $t;
}

function mac_friend_date($time)
{
    if (!$time)
        return false;
    $fdate = '';
    $d = time() - intval($time);
    $ld = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
    $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
    $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if ($d == 0) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('m月d日 H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('m月d日', $time);
                break;
            default:
                $fdate = date('Y年m月d日', $time);
                break;
        }
    }
    return $fdate;
}

function mac_get_time_span($sn)
{
    $lastTime = session($sn);

    if (empty($lastTime)) {
        $lastTime = "1228348800";
    }
    $res = time() - intval($lastTime);
    session($sn, time());
    return $res;
}

function mac_get_rndstr($length = 32, $f = '')
{
    $pattern = "234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if ($f == 'num') {
        $pattern = '1234567890';
    } elseif ($f == 'letter') {
        $pattern = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }
    $len = strlen($pattern) - 1;
    $res = '';
    for ($i = 0; $i < $length; $i++) {
        $res .= $pattern{mt_rand(0, $len)};
    }
    return $res;
}

function mac_convert_encoding($str, $nfate, $ofate)
{
    if ($ofate == "UTF-8") {
        return $str;
    }
    if ($ofate == "GB2312") {
        $ofate = "GBK";
    }

    if (function_exists("mb_convert_encoding")) {
        $str = mb_convert_encoding($str, $nfate, $ofate);
    } else {
        $ofate .= "//IGNORE";
        $str = iconv($nfate, $ofate, $str);
    }
    return $str;
}

function mac_get_refer()
{
    return trim(urldecode($_SERVER["HTTP_REFERER"] ?? ''));
}

function mac_send_mail($to, $title, $body, $conf = [])
{
    $config = config('maccms.email');
    if (!empty($conf)) {
        $config = $conf;
    }
    $mail = new \phpmailer\src\PHPMailer();
    //$mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->CharSet = "UTF-8";
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = $config['port'];
    $mail->setFrom($config['username'], $config['nick']);
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $title;
    $mail->Body = $body;
    unset($config);
    return $mail->send();
}

function mac_check_back_link($url)
{
    $res = [];
    $res['code'] = 0;
    $res['msg'] = '参数错误';

    if (empty($url)) {
        return json($res);
    }

    $site_url = $GLOBALS['config']['site']['site_url'];
    $site_wapurl = $GLOBALS['config']['site']['site_wapurl'];
    $html = mac_curl_get($url);
    $msg = '';
    $code = 1;

    $ok = '反链正常';
    $err = '反链异常';

    $msg .= '[' . $site_url . ']';
    if (strpos($html, $site_url) !== false) {
        $code = 1;
        $msg .= $ok;
    } else {
        $code = 101;
        $msg .= $err;
    }

    $msg .= '，[' . $site_wapurl . ']';
    if (strpos($html, $site_wapurl) !== false) {
        $code = 1;
        $msg .= $ok;
    } else {
        $code = 101;
        $msg .= $err;
    }
    $res['code'] = $code;
    $res['msg'] = $msg;

    return $res;
}

function object_array($array)
{
    if (is_object($array)) {
        $array = (array)$array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}

function mac_list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = 0)
{
    $tree = array();
    if (is_array($list)) {
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }

        foreach ($list as $key => $data) {
            $parentId = $data[$pid];

            if ($root == $parentId) {
                $tree[] =& $list[$key];

            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

function mac_str_correct($str, $from, $to)
{
    return str_replace($from, $to, $str);
}

function mac_buildregx($regstr, $regopt)
{
    return '/' . str_replace('/', '\/', $regstr) . '/' . $regopt;
}

function mac_em_replace($s)
{
    return preg_replace("/\[em:(\d{1,})?\]/", "<img src=\"" . MAC_PATH . "static/images/face/$1.gif\" border=0/>", $s);
}

function mac_page_param($record_total, $page_size, $page_current, $page_url, $page_half = 5)
{
    $page_param = array();
    $page_num = array();

    if ($record_total == 0) {
        return ['record_total' => 0];
    }
    if (empty($page_half)) {
        $page_half = 5;
    }

    $page_param['record_total'] = $record_total;
    $page_param['page_current'] = $page_current;

    $page_total = ceil($record_total / $page_size);
    $page_param['page_total'] = $page_total;
    $page_param['page_sp'] = MAC_PAGE_SP;

    $page_prev = $page_current - 1;
    if ($page_prev <= 0) {
        $page_prev = 1;
    }
    $page_next = $page_current + 1;
    if ($page_next > $page_total) {
        $page_next = $page_total;
    }
    $page_param['page_prev'] = $page_prev;
    $page_param['page_next'] = $page_next;

    if ($page_total <= $page_half) {
        for ($i = 1; $i <= $page_total; $i++) {
            $page_num[$i] = $i;
        }
    } else {
        $page_num_left = floor($page_half / 2);
        $page_num_right = $page_total - $page_half;

        if ($page_current <= $page_num_left) {
            for ($i = 1; $i <= $page_half; $i++) {
                $page_num[$i] = $i;
            }
        } elseif ($page_current > $page_num_right) {
            for ($i = ($page_num_right + 1); $i <= $page_total; $i++) {
                $page_num[$i] = $i;
            }
        } else {
            for ($i = ($page_current - $page_num_left); $i <= ($page_current + $page_num_left); $i++) {
                $page_num[$i] = $i;
            }
        }
    }
    $page_param['page_num'] = $page_num;
    $page_param['page_url'] = $page_url;

    return $page_param;
}

// CurlPOST数据提交-----------------------------------------
function mac_curl_post($url, $data, $heads = array(), $cookie = '')
{
    $ch = @curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36');
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLINFO_CONTENT_LENGTH_UPLOAD, strlen($data));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    if (!empty($cookie)) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    if (count($heads) > 0) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $heads);
    }
    $response = @curl_exec($ch);
    if (curl_errno($ch)) {//出错则显示错误信息
        //print curl_error($ch);
    }
    curl_close($ch); //关闭curl链接
    return $response;//显示返回信息
}

// CurlPOST数据提交-----------------------------------------
function mac_curl_get($url, $heads = array(), $cookie = '')
{
    $ch = @curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36');

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_POST, 0);

    //如果访问为https协议
    if (substr($url, 0, 5) == "https") {
//        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    }

    if (!empty($cookie)) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    if (count($heads) > 0) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $heads);
    }
    $response = @curl_exec($ch);
    if (curl_errno($ch)) {//出错则显示错误信息
        //print curl_error($ch);die;
    }
    curl_close($ch); //关闭curl链接
    return $response;//显示返回信息
}

//删除空格
function mac_trim_all($str)//删除空格
{
    $str = mac_format_text($str);
    $qian = array(" ", "　", "\t", "\n", "\r", "&nbsp;");
    $hou = array("", "", "", "", "", "");
    return str_replace($qian, $hou, $str);
}

//删除空格
function mac_trim_del($str)//删除空格
{
    $qian = array(" ", "　", "\t", "\n", "\r");
    return str_replace($qian, "", $str);
}

//交集相似度
function mac_intersect($str1, $str2)
{
    $str1 = array_filter(explode(',', $str1));
    $str2 = array_filter(explode(',', $str2));
    $len = count($str1) + count($str2);
    $percent = array_intersect($str1, $str2);
    return (count($percent) * 2 / $len) * 100;
}

//去除多余空格
function mac_filter_trim($str)
{
    return implode(',', array_filter(explode(',', $str)));
}

//名字过滤
function mac_characters_format($vod_name)
{
//    $vod_name = mac_trim_all($vod_name);
    $vod_name = str_replace('[', '', $vod_name);
    $vod_name = str_replace(']', '', $vod_name);
    $vod_name = str_replace('【', '', $vod_name);
    $vod_name = str_replace('】', '', $vod_name);
    $vod_name = str_replace('（', '', $vod_name);
    $vod_name = str_replace('）', '', $vod_name);
    $vod_name = str_replace('(', '', $vod_name);
    $vod_name = str_replace(')', '', $vod_name);
    preg_match_all('/[\x7f-\xff]+[ ]/', $vod_name, $matches);
    if (!empty($matches) && isset($matches[0])) {
        foreach ($matches[0] as $m_k => $m_v) {
            $vod_name = str_replace($m_v, str_replace(' ', '', $m_v), $vod_name);
        }
    }
    return $vod_name;
}

function mac_vod_remarks_is_v($vod_remarks)
{
    $is_remarks = false;
    $array_remarks = [
        '完结',
        '集全',
        '全集',
        '大结局',
    ];
    foreach ($array_remarks as $k => $v) {
        if (strpos($vod_remarks, $v) !== false) {
            $is_remarks = true;
        }
    }
    return $is_remarks;
}

//备注过滤
function mac_vod_remarks($vod_remarks, $vod_total)
{
    $update['vod_serial'] = '';
    if (strpos($vod_remarks, '完结') !== false) {
        $update['vod_serial'] = $vod_total;
    } elseif (strpos($vod_remarks, '集全') !== false) {
        $update['vod_serial'] = $vod_total;
    } elseif (strpos($vod_remarks, '集(全)') !== false) {
        $update['vod_serial'] = $vod_total;
    } elseif (strpos($vod_remarks, '全集') !== false) {
        $update['vod_serial'] = $vod_total;
    } elseif (strpos($vod_remarks, '大结局') !== false) {
        $update['vod_serial'] = $vod_total;
    } else {
        $vod_serial_str = findNum($vod_remarks);
        $vod_serial_str = empty($vod_serial_str) ? 0 : $vod_serial_str;
        $update['vod_serial'] = $vod_serial = max($vod_serial_str, $vod_total);
    }
    return $update['vod_serial'];
}

//ua大全
function mac_ua_all($type = 16)
{
    $ua_all = [
        0 => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36 OPR/26.0.1656.60',
        1 => 'Opera/8.0 (Windows NT 5.1; U; en)',
        2 => 'Mozilla/5.0 (Windows NT 5.1; U; en; rv:1.8.1) Gecko/20061208 Firefox/2.0.0 Opera 9.50',
        3 => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 9.50',
        4 => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0',
        5 => 'Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10',
        6 => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2 ',
        7 => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36',
        8 => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11',
        9 => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16',
        10 => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.101 Safari/537.36',
        11 => 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
        12 => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.11 TaoBrowser/2.0 Safari/536.11',
        13 => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.71 Safari/537.1 LBBROWSER',
        14 => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E)',
        15 => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.84 Safari/535.11 SE 2.X MetaSr 1.0',
        16 => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SV1; QQDownload 732; .NET4.0C; .NET4.0E; SE 2.X MetaSr 1.0)',
    ];
    return isset($ua_all[$type]) ? $ua_all[$type] : 'not-ua';
}

function mac_substring($str, $lenth, $start = 0)
{
    $len = strlen($str);
    $r = array();
    $n = 0;
    $m = 0;

    for ($i = 0; $i < $len; $i++) {
        $x = substr($str, $i, 1);
        $a = base_convert(ord($x), 10, 2);
        $a = substr('00000000 ' . $a, -8);

        if ($n < $start) {
            if (substr($a, 0, 1) == 0) {
            } else if (substr($a, 0, 3) == 110) {
                $i += 1;
            } else if (substr($a, 0, 4) == 1110) {
                $i += 2;
            }
            $n++;
        } else {
            if (substr($a, 0, 1) == 0) {
                $r[] = substr($str, $i, 1);
            } else if (substr($a, 0, 3) == 110) {
                $r[] = substr($str, $i, 2);
                $i += 1;
            } else if (substr($a, 0, 4) == 1110) {
                $r[] = substr($str, $i, 3);
                $i += 2;
            } else {
                $r[] = ' ';
            }
            if (++$m >= $lenth) {
                break;
            }
        }
    }
    return join('', $r);
}

//查看表是否存在
function isTable($table)
{
    $tableName = config('database.prefix') . $table;
    $isTable = db()->query('SHOW TABLES LIKE ' . "'" . $table . "'");
    if ($isTable) {
        //表存在
        return true;
    }
    return false;
}

// 复制表  新表名字 原本表
function copyTable($new_table, $from_table)
{
    #创建tp5_temp3表，数据全部自源于 tp5_staff 子查询
//    $sql = "CREATE TABLE `".$new_table."` AS SELECT * FROM `".$from_table."` ";
//
//    return db()->query($sql);

    $sql = "CREATE TABLE `" . $new_table . "` like `" . $from_table . "` ";
    db()->query($sql);

    $sql1 = "insert into `" . $new_table . "` select * from `" . $from_table . "` ";

    return db()->query($sql1);

}

function mac_array2xml($arr, $level = 1)
{
    $s = $level == 1 ? "<xml>" : '';
    foreach ($arr as $tagname => $value) {
        if (is_numeric($tagname)) {
            $tagname = $value['TagName'];
            unset($value['TagName']);
        }
        if (!is_array($value)) {
            $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
        } else {
            $s .= "<{$tagname}>" . mac_array2xml($value, $level + 1) . "</{$tagname}>";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    return $level == 1 ? $s . "</xml>" : $s;
}

//encode
function mac_json_encode($data)
{
    return json_encode($data, true);
}

//decode
function mac_json_decode($data)
{
    return json_decode($data, true);
}

function mac_xml2array($xml)
{
    libxml_disable_entity_loader(true);
    $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $result;
}

function mac_array_rekey($arr, $key)
{
    $list = [];
    foreach ($arr as $k => $v) {
        $list[$v[$key]] = $v;
    }
    return $list;
}

function mac_array_filter($arr, $str)
{
    if (!is_array($arr)) {
        $arr = explode(',', $arr);
    }
    $arr = array_filter($arr);
    if (empty($arr)) {
        return false;
    }
    //方式一
    $new_str = str_replace($arr, '*', $str);
    //$badword1 = array_combine($arr,array_fill(0,count($arr),'*'));
    //$new_str = strtr($str, $badword1);
    return $new_str != $str;
}

function mac_parse_sql($sql = '', $limit = 0, $prefix = [])
{
    // 被替换的前缀
    $from = '';
    // 要替换的前缀
    $to = '';

    // 替换表前缀
    if (!empty($prefix)) {
        $to = current($prefix);
        $from = current(array_flip($prefix));
    }

    if ($sql != '') {
        // 纯sql内容
        $pure_sql = [];

        // 多行注释标记
        $comment = false;

        // 按行分割，兼容多个平台
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        $sql = explode("\n", trim($sql));

        // 循环处理每一行
        foreach ($sql as $key => $line) {
            // 跳过空行
            if ($line == '') {
                continue;
            }

            // 跳过以#或者--开头的单行注释
            if (preg_match("/^(#|--)/", $line)) {
                continue;
            }

            // 跳过以/**/包裹起来的单行注释
            if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                continue;
            }

            // 多行注释开始
            if (substr($line, 0, 2) == '/*') {
                $comment = true;
                continue;
            }

            // 多行注释结束
            if (substr($line, -2) == '*/') {
                $comment = false;
                continue;
            }

            // 多行注释没有结束，继续跳过
            if ($comment) {
                continue;
            }

            // 替换表前缀
            if ($from != '') {
                $line = str_replace('`' . $from, '`' . $to, $line);
            }
            if ($line == 'BEGIN;' || $line == 'COMMIT;') {
                continue;
            }
            // sql语句
            array_push($pure_sql, $line);
        }

        // 只返回一条语句
        if ($limit == 1) {
            return implode($pure_sql, "");
        }

        // 以数组形式返回sql语句
        $pure_sql = implode($pure_sql, "\n");
        $pure_sql = explode(";\n", $pure_sql);
        return $pure_sql;
    } else {
        return $limit == 1 ? '' : [];
    }
}

function mac_interface_type()
{
    $key = 'interface_type';
    $data = think\Cache::get($key);

    if (empty($data)) {
        $config = config('maccms.interface');
        $vodtype = str_replace([chr(10), chr(13)], ['', '#'], $config['vodtype']);
        $arttype = str_replace([chr(10), chr(13)], ['', '#'], $config['arttype']);
        $actortype = str_replace([chr(10), chr(13)], ['', '#'], $config['actortype']);
        $websitetype = str_replace([chr(10), chr(13)], ['', '#'], $config['websitetype']);

        $data = [];
        $type_arr = explode('#', $vodtype);
        foreach ($type_arr as $k => $v) {
            list($from, $to) = explode('=', $v);
            $data['vodtype'][$to] = $from;
        }

        $type_arr = explode('#', $arttype);
        foreach ($type_arr as $k => $v) {
            list($from, $to) = explode('=', $v);
            $data['arttype'][$to] = $from;
        }

        $type_arr = explode('#', $actortype);
        foreach ($type_arr as $k => $v) {
            list($from, $to) = explode('=', $v);
            $data['actortype'][$to] = $from;
        }

        $type_arr = explode('#', $websitetype);
        foreach ($type_arr as $k => $v) {
            list($from, $to) = explode('=', $v);
            $data['websitetype'][$to] = $from;
        }

        think\Cache::set($key, $data);
    }

    $type_list = model('Type')->getCache('type_list');

    $type_names = [];
    foreach ($type_list as $k => $v) {
        $type_names[$v['type_name']] = $v['type_id'];
    }

    foreach ($data['vodtype'] as $k => $v) {
        $data['vodtype'][$k] = (int)$type_names[$v];
    }
    foreach ($data['arttype'] as $k => $v) {
        $data['arttype'][$k] = (int)$type_names[$v];
    }
    foreach ($data['actortype'] as $k => $v) {
        $data['actortype'][$k] = (int)$type_names[$v];
    }
    foreach ($data['websitetype'] as $k => $v) {
        $data['websitetype'][$k] = (int)$type_names[$v];
    }
    return $data;
}

function mac_rep_pse_rnd($psearr, $txt, $id = 0)
{
    if (empty($txt)) {
        $txt = "";
    }
    if (empty($id)) {
        $id = crc32($txt);
    }
    $i = count($psearr) + 1;
    $j = mb_strpos($txt, "<br>");

    if ($j == 0) {
        $j = mb_strpos($txt, "<br/>");
    }
    if ($j == 0) {
        $j = mb_strpos($txt, "<br />");
    }
    if ($j == 0) {
        $j = mb_strpos($txt, "</p>");
    }
    if ($j == 0) {
        $j = mb_strpos($txt, "。") + 1;
    }

    if ($j > 0) {
        $res = mac_substring($txt, $j - 1) . $psearr[$id % $i] . mac_substring($txt, mb_strlen($txt) - $j, $j);
    } else {
        $res = $psearr[$id % 1] . $txt;
    }
    unset($psearr);
    return $res;
}

function mac_txt_explain($txt)
{
    $txtarr = explode('#', $txt);
    $data = [];
    foreach ($txtarr as $k => $v) {
        list($from, $to) = explode('=', $v);
        $data['from'][] = $from;
        $data['to'][] = $to;
    }
    return $data;
}

function mac_rep_pse_syn($psearr, $txt)
{
    if (empty($txt)) {
        $txt = "";
    }
    if (is_array($psearr['from']) && is_array($psearr['to'])) {
        $txt = str_replace($psearr['from'], $psearr['to'], $txt);
    }
    return $txt;
}

function mac_get_tag($title, $content)
{
    $url = 'http://api.maccms.com/keyword/?callback=&txt=' . rawurlencode($title) . rawurlencode(mac_substring(strip_tags($content), 200));
    $data = mac_curl_get($url);
    $json = @json_decode($data, true);
    if ($json) {
        if ($json['code'] == 1) {
            return implode(',', $json['data']);
        }
    }
    return false;
}

function mac_get_uniqid_co($code_prefix = '')
{
    $code_prefix = strtoupper($code_prefix);
    $now_date = date('YmdHis');
    $now_time = rand(100000, 999999);
    return $code_prefix . $now_date . $now_time;
}

function mac_escape($string, $in_encoding = 'UTF-8', $out_encoding = 'UCS-2')
{
    $return = '';
    if (function_exists('mb_get_info')) {
        for ($x = 0; $x < mb_strlen($string, $in_encoding); $x++) {
            $str = mb_substr($string, $x, 1, $in_encoding);
            if (strlen($str) > 1) { // 多字节字符
                $return .= '%u' . strtoupper(bin2hex(mb_convert_encoding($str, $out_encoding, $in_encoding)));
            } else {
                $return .= '%' . strtoupper(bin2hex($str));
            }
        }
    }
    return $return;
}

function mac_unescape($str)
{
    $ret = '';
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        if ($str[$i] == '%' && $str[$i + 1] == 'u') {
            $val = hexdec(substr($str, $i + 2, 4));
            if ($val < 0x7f)
                $ret .= chr($val);
            else
                if ($val < 0x800)
                    $ret .= chr(0xc0 | ($val >> 6)) .
                        chr(0x80 | ($val & 0x3f));
                else
                    $ret .= chr(0xe0 | ($val >> 12)) .
                        chr(0x80 | (($val >> 6) & 0x3f)) .
                        chr(0x80 | ($val & 0x3f));
            $i += 5;
        } else
            if ($str[$i] == '%') {
                $ret .= urldecode(substr($str, $i, 3));
                $i += 2;
            } else
                $ret .= $str[$i];
    }
    return $ret;
}

/*特殊字段的值转换*/
function mac_get_mid_code($data)
{
    $arr = [1 => 'vod', 2 => 'art', 3 => 'topic', 4 => 'commment', 5 => 'gbook', 6 => 'user', 7 => 'label', 8 => 'actor', 9 => 'role', 10 => 'plot', 11 => 'website'];
    return $arr[$data];
}

function mac_get_mid_text($data)
{
    $arr = [1 => '视频', 2 => '文章', 3 => '专题', 4 => '评论', 5 => '留言', 6 => '用户中心', 7 => '自定义页面', 8 => '演员', 9 => '角色', 10 => '剧情', 11 => '网址'];
    return $arr[$data];
}

function mac_get_mid($controller)
{
    $controller = strtolower($controller);
    $arr = ['vod' => 1, 'art' => 2, 'topic' => 3, 'comment' => 4, 'gbook' => 5, 'user' => 6, 'label' => 7, 'actor' => 8, 'role' => 9, 'plot' => 10, 'website' => 11];
    return $arr[$controller];
}

function mac_get_aid($controller, $action = '')
{
    $controller = strtolower($controller);
    $action = strtolower($action);
    $key = $controller . '/' . $action;

    $arr = ['index' => 1, 'map' => 2, 'rss' => 3, 'gbook' => 4, 'comment' => 5, 'user' => 6, 'label' => 7, 'vod' => 10, 'art' => 20, 'topic' => 30, 'actor' => 80, 'role' => 90, 'plot' => 100, 'website' => 110];
    $res = $arr[$controller];

    $arr = [
        'vod/type' => 11, 'vod/show' => 12, 'vod/search' => 13, 'vod/detail' => 14, 'vod/play' => 15, 'vod/down' => 16, 'vod/role' => 17,
        'art/type' => 21, 'art/show' => 22, 'art/search' => 23, 'art/detail' => 24,
        'topic/search' => 33, 'topic/detail' => 34,
        'actor/type' => 81, 'actor/show' => 82, 'actor/search' => 83, 'actor/detail' => 84,
        'role/show' => 92, 'role/search' => 93, 'role/detail' => 94,
        'plot/search' => 103, 'plot/detail' => 104,
        'website/type' => 111, 'website/show' => 112, 'website/search' => 113, 'website/detail' => 114,
    ];
    if (!empty($arr[$key])) {
        $res = $arr[$key];
    }
//    v($res);
    return $res;
}

function mac_get_user_status_text($data)
{
    $arr = [0 => '禁用', 1 => '启用'];
    return $arr[$data];
}

function mac_get_user_flag_text($data)
{
    $arr = [0 => '计点', 1 => '计时', 2 => 'ip段'];
    return $arr[$data];
}

function mac_get_ulog_type_text($data)
{
    $arr = [1 => '浏览', 2 => '收藏', 3 => '想看', 4 => '点播', 5 => '下载'];
    return $arr[$data];
}

function mac_get_plog_type_text($data)
{
    $arr = [1 => '积分充值', 2 => '注册推广', 3 => '访问推广', 4 => '三级分销', 7 => '积分升级', 8 => '积分消费', 9 => '积分提现', 10 => '签到'];
    return $arr[$data];
}

function mac_get_card_sale_status_text($data)
{
    $arr = [0 => '未出售', 1 => '已出售'];
    return $arr[$data];
}

function mac_get_card_use_status_text($data)
{
    $arr = [0 => '未使用', 1 => '已使用'];
    return $arr[$data];
}

function mac_get_order_status_text($data)
{
    $arr = [0 => '未支付', 1 => '已支付'];
    return $arr[$data];
}

function mac_get_user_portrait($user_id)
{
    $res = MAC_PATH . 'static/images/touxiang.png';
    if (!empty($user_id)) {
        $res2 = 'upload/user/' . ($user_id % 10) . '/' . $user_id . '.jpg';
        if (file_exists(ROOT_PATH . $res2)) {
            $res = MAC_PATH . $res2;
        }
    }
    return $res;
}

function mac_filter_html($str)
{
    return strip_tags($str);
}
function int_zhuanhuan($str){
    $len = preg_replace('|[a-zA-Z/]+|','',$str);
    if (strlen($len) >= 50){
        return 1;
    }else{
        return $len;
    }
}
//推算主级id
function getTypePid($id, $i = 1)
{
    $data = Db::table('type')->field('type_pid,type_id')->where(['type_id' => $id])->find();
    if (empty($data)) {
        return 0;
    }
    if ($data['type_pid'] != 0) {
        $i = $i + 1;
        if ($i > 3) {
            return 0;//最后默认先给0 有问题的数据
        } else {
            return getTypePid($data['type_pid'], $i);
        }
    } else {
        return $data['type_id'];
    }
}

function mac_format_text($str)
{
    return str_replace(array('/', '，', '|', '、', ' ', ',,,'), ',', $str);
}

function mac_format_count($str)
{
    $arr = explode(',', $str);
    return count($arr);
}

function mac_txt_merge($txt, $str)
{
    if (empty($str)) {
        return $txt;
    }
    if ($GLOBALS['config']['collect']['vod']['class_filter'] != '0') {
        if (mb_strlen($str) > 2) {
            $str = str_replace(['片'], [''], $str);
        }
        if (mb_strlen($str) > 2) {
            $str = str_replace(['剧'], [''], $str);
        }
    }
    $txt = mac_format_text($txt);
    $str = mac_format_text($str);
    $arr1 = explode(',', $txt);
    $arr2 = explode(',', $str);
    $arr = array_merge($arr1, $arr2);
    return join(',', array_unique(array_filter($arr)));
}

function mac_array_check_num($arr)
{
    if (!is_array($arr)) {
        return false;
    }
    $res = true;
    foreach ($arr as $a) {
        if (!is_numeric($a)) {
            $res = false;
            break;
        }
    }
    return $res;
}

function mac_like_arr($s)
{
    $tmp = explode(',', $s);
    foreach ($tmp as $k => $v) {
        $tmp[$k] = '%' . $v . '%';
    }
    return $tmp;
}

function mac_art_list($art_title, $art_note, $art_content)
{
    $art_title_list = [];
    $art_note_list = [];
    $art_content_list = [];
    if (!empty($art_title)) {
        $art_title_list = explode('$$$', $art_title);
    }
    if (!empty($art_note)) {
        $art_note_list = explode('$$$', $art_note);
    }
    if (!empty($art_content)) {
        $art_content_list = explode('$$$', $art_content);
    }
    $res_list = [];
    foreach ($art_content_list as $k => $v) {
        $res_list[$k + 1] = [
            'page' => $k + 1,
            'title' => $art_title_list[$k],
            'note' => $art_note_list[$k],
            'content' => $v,
        ];
    }
    return $res_list;
}

function mac_plot_list($vod_plot_name, $vod_plot_detail)
{
    $vod_plot_name_list = [];
    $vod_plot_detail_list = [];

    if (!empty($vod_plot_name)) {
        $vod_plot_name_list = explode('$$$', $vod_plot_name);
    }
    if (!empty($vod_plot_detail)) {
        $vod_plot_detail_list = explode('$$$', $vod_plot_detail);
    }

    $res_list = [];
    foreach ($vod_plot_name_list as $k => $v) {

        $res_list[$k + 1] = [
            'name' => $vod_plot_name_list[$k],
            'detail' => $vod_plot_detail_list[$k],
        ];
    }
    return $res_list;

}

function mac_play_list($vod_play_from, $vod_play_url, $vod_play_server, $vod_play_note, $flag = 'play')
{
    $vod_play_from_list = [];
    $vod_play_url_list = [];
    $vod_play_server_list = [];
    $vod_play_note_list = [];

    if (!empty($vod_play_from)) {
        $vod_play_from_list = explode('$$$', $vod_play_from);
    }
    if (!empty($vod_play_url)) {
        $vod_play_url_list = explode('$$$', $vod_play_url);
    }
    if (!empty($vod_play_server)) {
        $vod_play_server_list = explode('$$$', $vod_play_server);
    }
    if (!empty($vod_play_note)) {
        $vod_play_note_list = explode('$$$', $vod_play_note);
    }

    if ($flag == 'play') {
        $player_list = config('vodplayer');
    } else {
        $player_list = config('voddowner');
    }
    $server_list = config('vodserver');

    $res_list = [];
    $sort = [];

    foreach ($vod_play_from_list as $k => $v) {
        $server = (string)$vod_play_server_list[$k];
        $urls = mac_play_list_one($vod_play_url_list[$k], $v);
        $player_info = $player_list[$v];
        $server_info = $server_list[$server];
        if ($player_info['status'] == '1') {
            $sort[] = $player_info['sort'];
            $res_list[$k + 1] = [
                'sid' => $k + 1,
                'player_info' => $player_info,
                'server_info' => $server_info,
                'from' => $v,
                'url' => $vod_play_url_list[$k],
                'server' => $server,
                'note' => $vod_play_note_list[$k],
                'url_count' => count($urls),
                'urls' => $urls,
            ];
        }
    }
    if ((ENTRANCE != 'admin' && MAC_PLAYER_SORT == '1') || $GLOBALS['ismake'] == '1') {
        array_multisort($sort, SORT_DESC, SORT_FLAG_CASE, $res_list);
        $tmp = [];
        foreach ($res_list as $k => $v) {
            $tmp[$v['sid']] = $v;
        }
        $res_list = $tmp;
    }
    return $res_list;
}

function new_stripslashes($string)
{
    if (!is_array($string)) return stripslashes($string);
    foreach ($string as $key => $val) $string[$key] = new_stripslashes($val);
    return $string;
}

function mac_sort_arr($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
{
    $key_arrays = array();
    if (is_array($arrays)) {
        foreach ($arrays as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
    return $arrays;
}

function DeleteHtml($descclear)
{
    $descclear = strip_tags($descclear);
    $descclear = str_replace("\r", "", $descclear);//过滤换行
    $descclear = str_replace("\n", "", $descclear);//过滤换行
    $descclear = str_replace("\t", "", $descclear);//过滤换行
    $descclear = str_replace("\r\n", "", $descclear);//过滤换行
    $descclear = preg_replace("/\s+/", " ", $descclear);//过滤多余回车
    $descclear = preg_replace("/<[ ]+/si", "<", $descclear); //过滤<__("<"号后面带空格)
    $descclear = preg_replace("/<\!--.*?-->/si", "", $descclear); //过滤html注释
    $descclear = preg_replace("/<(\!.*?)>/si", "", $descclear); //过滤DOCTYPE
    $descclear = preg_replace("/<(\/?html.*?)>/si", "", $descclear); //过滤html标签
    $descclear = preg_replace("/<(\/?head.*?)>/si", "", $descclear); //过滤head标签
    $descclear = preg_replace("/<(\/?meta.*?)>/si", "", $descclear); //过滤meta标签
    $descclear = preg_replace("/<(\/?body.*?)>/si", "", $descclear); //过滤body标签
    $descclear = preg_replace("/<(\/?link.*?)>/si", "", $descclear); //过滤link标签
    $descclear = preg_replace("/<(\/?form.*?)>/si", "", $descclear); //过滤form标签
    $descclear = preg_replace("/cookie/si", "COOKIE", $descclear); //过滤COOKIE标签
    $descclear = preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si", "", $descclear); //过滤applet标签
    $descclear = preg_replace("/<(\/?applet.*?)>/si", "", $descclear); //过滤applet标签
    $descclear = preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si", "", $descclear); //过滤style标签
    $descclear = preg_replace("/<(\/?style.*?)>/si", "", $descclear); //过滤style标签
    $descclear = preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si", "", $descclear); //过滤title标签
    $descclear = preg_replace("/<(\/?title.*?)>/si", "", $descclear); //过滤title标签
    $descclear = preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si", "", $descclear); //过滤object标签
    $descclear = preg_replace("/<(\/?objec.*?)>/si", "", $descclear); //过滤object标签
    $descclear = preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si", "", $descclear); //过滤noframes标签
    $descclear = preg_replace("/<(\/?noframes.*?)>/si", "", $descclear); //过滤noframes标签
    $descclear = preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si", "", $descclear); //过滤frame标签
    $descclear = preg_replace("/<(\/?i?frame.*?)>/si", "", $descclear); //过滤frame标签
    $descclear = preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si", "", $descclear); //过滤script标签
    $descclear = preg_replace("/<(\/?script.*?)>/si", "", $descclear); //过滤script标签
    $descclear = preg_replace("/javascript/si", "Javascript", $descclear); //过滤script标签
    $descclear = preg_replace("/vbscript/si", "Vbscript", $descclear); //过滤script标签
    $descclear = preg_replace("/on([a-z]+)\s*=/si", "On\\1=", $descclear); //过滤script标签
    $descclear = preg_replace("/&#/si", "&＃", $descclear); //过滤script标签，如javAsCript:alert();
//使用正则替换
    $pat = "/<(\/?)(script|i?frame|style|html|body|li|i|map|title|img|link|span|u|font|table|tr|b|marquee|td|strong|div|a|meta|\?|\%)([^>]*?)>/isU";
    $descclear = preg_replace($pat, "", $descclear);
    return trim($descclear); //返回字符串
}

//查看是否存在html字符
function mac_str_is_html($str)
{
    $str = trim($str);
    $strip_str = htmlspecialchars_decode($str);
    $strip_str = mac_trim_all($strip_str);
    $strip_str = DeleteHtml($strip_str);
    return $strip_str;
//    if($str != $strip_str){
//        return $strip_str;
//    }else{
//        return false;
//    }
}

function getM3u8($m3u8,$type=1){
    $list = [
        'ckm3u8'=>[
            'id'=>'1',
            'key'=>'ckm3u8',
            'name'=>'ok'
        ],
        'zuidam3u8'=>[
            'id'=>'2',
            'key'=>'zuidam3u8',
            'name'=>'最大'
        ],
        'zkm3u8'=>[
            'id'=>'3',
            'key'=>'zkm3u8',
            'name'=>'最快'
        ],
        'xinm3u8'=>[
            'id'=>'4',
            'key'=>'xinm3u8',
            'name'=>'最新'
        ],
        'mahua'=>[
            'id'=>'5',
            'key'=>'mahua',
            'name'=>'麻花'
        ],
        'kkm3u8'=>[
            'id'=>'6',
            'key'=>'kkm3u8',
            'name'=>'酷云'
        ],

        'mbckm3u8'=>[
            'id'=>'7',
            'key'=>'mbckm3u8',
            'name'=>'秒播'
        ],
        '135m3u8'=>[
            'id'=>'8',
            'key'=>'135m3u8',
            'name'=>'135'
        ],
        '123kum3u8'=>[
            'id'=>'9',
            'key'=>'123kum3u8',
            'name'=>'123'
        ],
        'dbm3u8'=>[
            'id'=>'10',
            'key'=>'dbm3u8',
            'name'=>'百度云'
        ],
    ];
    if ($type == 1){
        return isset($list[$m3u8])?$list[$m3u8]:[];
    }else{
        return $list;
    }
}

//查看是否存在html字符
function mac_str_is_htmls($str)
{
    $str1 = $str;
    $str = trim($str);
    $strip_str = htmlspecialchars_decode($str);
    $strip_str = mac_trim_all($strip_str);
    $strip_str = DeleteHtml($strip_str);
    if($str1 != $strip_str){
        return $strip_str;
    }else{
        return false;
    }
}
function mac_play_list_one($url_one, $from_one, $server_one = '')
{

    $url_list = array();
    $array_url = explode('#', $url_one);
    $new_array = [];

    $array_url = array_reverse($array_url);
    foreach ($array_url as $k => $v) {
        list($title, $url, $from) = explode('$', $v);
        $new_array[$k]['key'] = $title;
        $new_array[$k]['url'] = $url;
        if (!empty($from)) {
            $new_array[$k]['from'] = $from;
        }
    }
    array_multisort(array_column($new_array, 'key'), SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL, $new_array);
    foreach ($new_array as $k => $v) {
        $new_array[$k] = implode('$', $v);
    }
    $array_url = $new_array;
    foreach ($array_url as $key => $val) {
        if (empty($val)) continue;
        list($title, $url, $from) = explode('$', $val);
        if (empty($url)) {
            $url_list[$key + 1]['name'] = '第' . ($key + 1) . '集';
            $url_list[$key + 1]['url'] = $server_one . $title;
        } else {
            $url_list[$key + 1]['name'] = $title;
            $url_list[$key + 1]['url'] = $server_one . $url;
        }
        if (empty($from)) {
            $from = $from_one;
        }
        $url_list[$key + 1]['from'] = (string)$from;
        $url_list[$key + 1]['nid'] = $key + 1;
    }
    return $url_list;
}


function mac_filter_words($str)
{
    $config = config('maccms.app');
    $arr = explode(",", $config['filter_words']);
    foreach ($arr as $a) {
        $str = str_replace($a, "***", $str);
    }
    return $str;
}

function mac_long2ip($ip)
{
    $ip = long2ip($ip);
    $reg2 = '~(\d+)\.(\d+)\.(\d+)\.(\d+)~';
    return preg_replace($reg2, "$1.$2.*.*", $ip);
}

function mac_default($s, $def = '')
{
    if (empty($s)) {
        return $def;
    }
    return $s;
}

function mac_num_fill($num)
{
    if ($num < 10) {
        $num = '0' . $num;
    }
    return $num;
}

function mac_multisort($arr, $col_sort, $sort_order, $col_status = '', $status_val = '')
{

    $sort = [];
    foreach ($arr as $k => $v) {
        $sort[] = $v[$col_sort];
        if ($col_status != '' && $v[$col_status] != $status_val) {
            unset($arr[$k]);
        }
    }
    array_multisort($sort, SORT_DESC, SORT_FLAG_CASE, $arr);
    return $arr;
}

function mac_get_body($text, $start, $end)
{
    if (empty($text)) {
        return false;
    }
    if (empty($start)) {
        return false;
    }
    if (empty($end)) {
        return false;
    }

    $start = stripslashes($start);
    $end = stripslashes($end);


    if (strpos($text, $start) != "") {
        $str = substr($text, strpos($text, $start) + strlen($start));
        $str = substr($str, 0, strpos($str, $end));
    } else {
        $str = '';
    }
    return $str;
}

function mac_find_array($text, $start, $end)
{

    $start = stripslashes($start);
    $end = stripslashes($end);
    if (empty($text)) {
        return false;
    }
    if (empty($start)) {
        return false;
    }
    if (empty($end)) {
        return false;
    }

    $start = str_replace(["(", ")", "'", "?"], ["\(", "\)", "\'", "\?"], $start);
    $end = str_replace(["(", ")", "'", "?"], ["\(", "\)", "\'", "\?"], $end);

    $labelRule = $start . "(.*?)" . $end;
    $labelRule = mac_buildregx($labelRule, "is");
    preg_match_all($labelRule, $text, $tmparr);
    $tmparrlen = count($tmparr[1]);
    $rc = false;
    $str = '';
    $arr = [];
    for ($i = 0; $i < $tmparrlen; $i++) {
        if ($rc) {
            $str .= "{array}";
        }
        $str .= $tmparr[1][$i];
        $rc = true;
    }

    if (empty($str)) {
        return false;
    }
    $str = str_replace($start, "", $str);
    $str = str_replace($end, "", $str);
    //$str=str_replace("\"\"","",$str);
    //$str=str_replace("'","",$str);
    //$str=str_replace(" ","",$str);
    if (empty($str)) {
        return false;
    }
    return $str;
}

/*前台页面*/
function mac_param_url()
{
    $input = input();
    $param = [];
    $input = array_merge($input, $_REQUEST);

    //$param['id'] = intval($input['id']);
    $param['page'] = intval($input['page']) < 1 ? 1 : intval($input['page']);
    $param['ajax'] = intval($input['ajax']);
    $param['tid'] = intval($input['tid']);
    $param['mid'] = intval($input['mid']);
    $param['rid'] = intval($input['rid']);
    $param['pid'] = intval($input['pid']);
    $param['sid'] = intval($input['sid']);
    $param['nid'] = intval($input['nid']);
    $param['uid'] = intval($input['uid']);
    $param['level'] = intval($input['level']);
    $param['score'] = intval($input['score']);
    $param['limit'] = intval($input['limit']);

    $param['id'] = htmlspecialchars(urldecode(trim($input['id'])));
    $param['ids'] = htmlspecialchars(urldecode(trim($input['ids'])));
    $param['wd'] = htmlspecialchars(urldecode(trim($input['wd'])));
    $param['en'] = htmlspecialchars(urldecode(trim($input['en'])));
    $param['state'] = htmlspecialchars(urldecode(trim($input['state'])));
    $param['area'] = htmlspecialchars(urldecode(trim($input['area'])));
    $param['year'] = htmlspecialchars(urldecode(trim($input['year'])));
    $param['lang'] = htmlspecialchars(urldecode(trim($input['lang'])));
    $param['letter'] = htmlspecialchars(trim($input['letter']));
    $param['actor'] = htmlspecialchars(urldecode(trim($input['actor'])));
    $param['director'] = htmlspecialchars(urldecode(trim($input['director'])));
    $param['tag'] = htmlspecialchars(urldecode(trim($input['tag'])));
    $param['class'] = htmlspecialchars(urldecode(trim($input['class'])));
    $param['order'] = htmlspecialchars(urldecode(trim($input['order'])));
    $param['by'] = htmlspecialchars(urldecode(trim($input['by'])));
    $param['file'] = htmlspecialchars(urldecode(trim($input['file'])));
    $param['name'] = htmlspecialchars(urldecode(trim($input['name'])));
    $param['url'] = htmlspecialchars(urldecode(trim($input['url'])));
    $param['type'] = htmlspecialchars(urldecode(trim($input['type'])));
    $param['sex'] = htmlspecialchars(urldecode(trim($input['sex'])));
    $param['version'] = htmlspecialchars(urldecode(trim($input['version'])));
    $param['blood'] = htmlspecialchars(urldecode(trim($input['blood'])));
    $param['starsign'] = htmlspecialchars(urldecode(trim($input['starsign'])));
    $param['domain'] = htmlspecialchars(urldecode(trim($input['domain'])));

    return $param;
}

function mac_get_page($page)
{
    if (empty($page)) {
        $param = mac_param_url();
        $page = $param['page'];
    }
    return $page;
}

function mac_tpl_fetch($model, $tpl, $def = '')
{
    return $model . '/' . (empty($tpl) ? $def : str_replace('.html', '', $tpl));
}

function mac_get_order($order, $param)
{
    if (!empty($param['order'])) {
        $order = $param['order'];
    }
    if (!in_array($order, ['asc', 'desc'])) {
        $order = 'desc';
    }
    return $order;
}

function mac_url_img($url)
{
    if (substr($url, 0, 4) == 'mac:') {
        $protocol = $GLOBALS['config']['upload']['protocol'];
        if (empty($protocol)) {
            $protocol = 'http';
        }
        $url = str_replace('mac:', $protocol . ':', $url);
    } elseif (substr($url, 0, 4) != 'http' && substr($url, 0, 2) != '//' && substr($url, 0, 1) != '/') {
        if ($GLOBALS['config']['upload']['mode'] == 'remote') {
            $url = $GLOBALS['config']['upload']['remoteurl'] . $url;
        } else {
            $url = MAC_PATH . $url;
        }
    }
    return $url;
}

function mac_url_content_img($content)
{
    $protocol = $GLOBALS['config']['upload']['protocol'];
    if (empty($protocol)) {
        $protocol = 'http';
    }
    return str_replace('mac:', $protocol . ':', $content);
}

function mac_url($model, $param = [], $info = [])
{
    foreach ($param as $k => $v) {
        if (empty($v)) {
            unset($param[$k]);
        }
    }

    if (!isset($param['page'])) $param['page'] = 1;

    if ($param['page'] == 1) {
        $param['page'] = '';
    }

    ksort($param);

    $config = $GLOBALS['config'];
    $replace_from = ['{id}', '{en}', '{page}', '{type_id}', '{type_en}', '{type_pid}', '{type_pen}', '{md5}', '{year}', '{month}', '{day}', '{sid}', '{nid}'];
    $replace_to = [];
    $page_sp = $config['path']['page_sp'];
    $path = '';


    switch ($model) {
        case 'index/index':
            if ($config['view']['index'] == 2) {
                $path = 'index';
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
            } else {
                $url = url($model, $param);
                if ($url == '/PAGELINK.html') {
                    $url = '/index-PAGELINK.html';
                }
            }
            break;
        case 'map/index':
            if ($config['view']['map'] == 2) {
                $path = 'map';
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
            } else {
                $url = url($model, $param);
            }
            break;
        case strpos($model, 'rss/') !== false:
            if ($config['view']['rss'] == 2) {
                $path = $model;
                if ($param['page'] != '') {
                    $path .= $page_sp . $param['page'];
                }

                $path .= '.xml';
            } else {
                $url = url($model, $param, 'xml');
            }
            break;
        case strpos($model, 'label/') !== false:
            if ($config['view']['label'] == 2) {
                $path = $model;
            } else {
                $url = url($model, $param);
            }
            break;
        case 'vod/show':
        case 'art/show':
        case 'actor/show':
        case 'website/show':
            $id = $config['rewrite']['type_id'] == 1 ? 'type_en' : 'type_id';
            if (!empty($info[$id])) {
                $param['id'] = $info[$id];
            }
            $url = url($model, $param);
            break;
        case 'vod/type':
            $replace_to = [$info['type_id'], $info['type_en'], $param['page'],
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en'],
            ];
            if ($config['view']['vod_type'] == 2) {
                $path = $config['path']['vod_type'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['type_id']);
                }
                if ($param['page'] != '') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $id = $config['rewrite']['type_id'] == 1 ? 'type_en' : 'type_id';
                $url = url($model, ['id' => $info[$id], 'page' => $param['page']]);
            }
            break;
        case 'vod/detail':
            $replace_to = [$info['vod_id'], $info['vod_en'], '',
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en']
            ];
            if ($config['view']['vod_detail'] == 2) {
                $path = $config['path']['vod_detail'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['vod_id']);
                }
            } else {
                $id = $config['rewrite']['vod_id'] == 1 ? 'vod_en' : 'vod_id';
                $url = url($model, ['id' => $info[$id]]);
            }
            $replace_to = array_merge($replace_to, [date('Y', $info['vod_time']), date('m', $info['vod_time']), date('d', $info['vod_time'])]);
            break;
        case 'vod/play':
            $replace_to = [
                $info['vod_id'], $info['vod_en'], '',
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en'],

            ];
            if ($config['view']['vod_play'] >= 2) {
                $path = $config['path']['vod_play'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['vod_id']);
                }
                if ($config['view']['vod_play'] == 2) {
                    $path .= '.' . $config['path']['suffix'];
                    $path .= '?' . $info['vod_id'] . '-' . $param['sid'] . '-' . $param['nid'];
                } elseif ($config['view']['vod_play'] == 3) {
                    $path .= $config['path']['page_sp'] . $param['sid'] . $config['path']['page_sp'] . $param['nid'];
                } elseif ($config['view']['vod_play'] == 4) {
                    $path .= $config['path']['page_sp'] . '' . $param['sid'] . $config['path']['page_sp'] . '1';
                    $path .= '.' . $config['path']['suffix'];
                    $path .= '?' . $info['vod_id'] . '-' . $param['sid'] . '-' . $param['nid'];
                }
            } else {
                $id = $config['rewrite']['vod_id'] == 1 ? 'vod_en' : 'vod_id';
                $url = url($model, ['id' => $info[$id], 'sid' => $param['sid'], 'nid' => $param['nid']]);
            }
            $replace_to = array_merge($replace_to, [date('Y', $info['vod_time']), date('m', $info['vod_time']), date('d', $info['vod_time']), $param['sid'], $param['nid']]);
            break;
        case 'vod/down':
            $replace_to = [
                $info['vod_id'], $info['vod_en'], '',
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en']
            ];
            if ($config['view']['vod_down'] == 2) {
                $path = $config['path']['vod_down'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['vod_id']);
                }
                if ($config['view']['vod_down'] == 3) {
                    $path .= $config['path']['page_sp'] . $param['sid'] . $config['path']['page_sp'] . $param['nid'];
                } elseif ($config['view']['vod_down'] == 4) {
                    $path .= $config['path']['page_sp'] . '' . $param['sid'];
                }
            } else {
                $id = $config['rewrite']['vod_id'] == 1 ? 'vod_en' : 'vod_id';
                $url = url($model, ['id' => $info[$id], 'sid' => $param['sid'], 'nid' => $param['nid']]);
            }
            $replace_to = array_merge($replace_to, [date('Y', $info['vod_time']), date('m', $info['vod_time']), date('d', $info['vod_time']), $param['sid'], $param['nid']]);
            break;
        case 'vod/role':
            $replace_to = [$info['vod_id'], $info['vod_en'], '',
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en']
            ];
            if ($config['view']['vod_role'] == 2) {
                $path = $config['path']['vod_role'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['vod_id']);
                }
            } else {
                $id = $config['rewrite']['vod_id'] == 1 ? 'vod_en' : 'vod_id';
                $url = url($model, ['id' => $info[$id]]);
            }
            $replace_to = array_merge($replace_to, [date('Y', $info['vod_time']), date('m', $info['vod_time']), date('d', $info['vod_time'])]);
            break;
        case 'vod/plot':
            $replace_to = [
                $info['vod_id'], $info['vod_en'], $param['page'],
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en']
            ];
            if ($config['view']['vod_plot'] == 2) {
                $path = $config['path']['vod_plot'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['vod_id']);
                }
                if ($param['page'] != '') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $id = $config['rewrite']['vod_id'] == 1 ? 'vod_en' : 'vod_id';
                $url = url($model, ['id' => $info[$id], 'page' => $param['page']]);
            }
            $replace_to = array_merge($replace_to, [date('Y', $info['vod_time']), date('m', $info['vod_time']), date('d', $info['vod_time'])]);
            break;
        case 'art/type':
            $replace_to = [$info['type_id'], $info['type_en'], $param['page'],
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en'],
            ];
            if ($config['view']['art_type'] == 2) {
                $path = $config['path']['art_type'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['type_id']);
                }
                if ($param['page'] != '') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $id = $config['rewrite']['type_id'] == 1 ? 'type_en' : 'type_id';
                $url = url($model, ['id' => $info[$id], 'page' => $param['page']]);
            }
            break;
        case 'art/detail':
            $replace_to = [
                $info['art_id'], $info['art_en'], '',
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en']
            ];
            if ($config['view']['art_detail'] == 2) {
                $path = $config['path']['art_detail'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['art_id']);
                }
                if ($param['page'] > 1 || $param['page'] == 'PAGELINK') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $id = $config['rewrite']['art_id'] == 1 ? 'art_en' : 'art_id';
                $url = url($model, ['id' => $info[$id], 'page' => $param['page']]);
            }
            $replace_to = array_merge($replace_to, [date('Y', $info['art_time']), date('m', $info['art_time']), date('d', $info['art_time'])]);
            break;
        case 'topic/index':
            if ($config['view']['topic_index'] == 2) {
                $path = $config['path']['topic_index'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if ($param['page'] > 1 || $param['page'] == 'PAGELINK') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $url = url($model, ['page' => $param['page']]);
            }
            break;
        case 'topic/detail':
            $replace_to = [$info['topic_id'], $info['topic_en'], '', '', '', '', ''];
            if ($config['view']['topic_detail'] == 2) {
                $path = $config['path']['topic_detail'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['topic_id']);
                }
            } else {
                $id = $config['rewrite']['topic_id'] == 1 ? 'topic_en' : 'topic_id';
                $url = url($model, ['id' => $info[$id]]);
            }
            break;
        case 'actor/index':
            if ($config['view']['actor_index'] == 2) {
                $path = $config['path']['actor_index'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if ($param['page'] > 1 || $param['page'] == 'PAGELINK') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $url = url($model, ['page' => $param['page']]);
            }
            break;
        case 'actor/type':
            $replace_to = [$info['type_id'], $info['type_en'], $param['page'],
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en'],
            ];
            if ($config['view']['actor_type'] == 2) {
                $path = $config['path']['actor_type'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['type_id']);
                }
                if ($param['page'] != '') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $id = $config['rewrite']['type_id'] == 1 ? 'type_en' : 'type_id';
                $url = url($model, ['id' => $info[$id], 'page' => $param['page']]);
            }
            break;
        case 'actor/detail':
            $replace_to = [$info['actor_id'], $info['actor_en'], '', '', '', '', ''];
            if ($config['view']['actor_detail'] == 2) {
                $path = $config['path']['actor_detail'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['actor_id']);
                }
            } else {
                $id = $config['rewrite']['actor_id'] == 1 ? 'actor_en' : 'actor_id';
                $url = url($model, ['id' => $info[$id]]);
            }
            break;
        case 'role/index':
            if ($config['view']['role_index'] == 2) {
                $path = $config['path']['role_index'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if ($param['page'] > 1 || $param['page'] == 'PAGELINK') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $url = url($model, ['page' => $param['page']]);
            }
            break;
        case 'role/detail':
            $replace_to = [$info['role_id'], $info['actor_en'], '', '', '', '', ''];
            if ($config['view']['role_detail'] == 2) {
                $path = $config['path']['role_detail'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['role_id']);
                }
            } else {
                $id = $config['rewrite']['role_id'] == 1 ? 'role_en' : 'role_id';
                $url = url($model, ['id' => $info[$id]]);
            }
            break;
        case 'plot/index':
            if ($config['view']['plot_index'] == 2) {
                $path = $config['path']['plot_index'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if ($param['page'] > 1 || $param['page'] == 'PAGELINK') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $url = url($model, ['page' => $param['page']]);
            }
            break;
        case 'plot/detail':
            $replace_to = [
                $info['vod_id'], $info['vod_en'], '',
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en']
            ];
            if ($config['view']['plot_detail'] == 2) {
                $path = $config['path']['plot_detail'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['vod_id']);
                }
                if ($param['page'] > 1 || $param['page'] == 'PAGELINK') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $id = $config['rewrite']['vod_id'] == 1 ? 'vod_en' : 'vod_id';
                $url = url($model, ['id' => $info[$id], 'page' => $param['page']]);
            }
            $replace_to = array_merge($replace_to, [date('Y', $info['vod_time']), date('m', $info['vod_time']), date('d', $info['vod_time'])]);
            break;
        case 'website/index':
            if ($config['view']['website_index'] == 2) {
                $path = $config['path']['website_index'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if ($param['page'] > 1 || $param['page'] == 'PAGELINK') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $url = url($model, ['page' => $param['page']]);
            }
            break;
        case 'website/type':
            $replace_to = [$info['type_id'], $info['type_en'], $param['page'],
                $info['type_id'], $info['type']['type_en'], $info['type_1']['type_id'], $info['type_1']['type_en'],
            ];
            if ($config['view']['website_type'] == 2) {
                $path = $config['path']['website_type'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['type_id']);
                }
                if ($param['page'] != '') {
                    $path .= $page_sp . $param['page'];
                }
            } else {
                $id = $config['rewrite']['type_id'] == 1 ? 'type_en' : 'type_id';
                $url = url($model, ['id' => $info[$id], 'page' => $param['page']]);
            }
            break;
        case 'website/detail':
            $replace_to = [$info['website_id'], $info['website_en'], '', '', '', '', ''];
            if ($config['view']['website_detail'] == 2) {
                $path = $config['path']['website_detail'];
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= 'index';
                }
                if (strpos($path, '{md5}') !== false) {
                    $replace_to[] = md5($info['website_id']);
                }
            } else {
                $id = $config['rewrite']['website_id'] == 1 ? 'website_en' : 'website_id';
                $url = url($model, ['id' => $info[$id]]);
            }
            break;
        case 'gbook/index':
            $url = url($model, ['page' => $param['page']]);
            break;
        case 'comment/index':
            $url = url($model, ['page' => $param['page']]);
            break;
        default:
            $url = url($model, $param);
            break;
    }
    if (!empty($path)) {
        $path = str_replace($replace_from, $replace_to, $path);
        $path = str_replace('//', '/', $path);
        $delimiter = false;
        if (substr($path, strlen($path) - 6) == '/index') {
            $delimiter = true;
            $path = substr($path, 0, strlen($path) - 5);
        }

        if ($delimiter == false && strpos($path, '.') === false) {
            $path .= '.' . $config['path']['suffix'];
        }
        $url = $path;
        if (substr($path, 0, 1) != '/') {
            $url = MAC_PATH . $path;
        }
    } else {
        if (ENTRANCE != 'index') {
            $sto = MAC_PATH;
            if ($config['rewrite']['status'] == 0) {
                $sto = MAC_PATH . 'index.php/';
            }
            if (!empty(IN_FILE)) {
                $url = str_replace(IN_FILE . '/', $sto, $url);
                $url = str_replace(ENTRANCE . '/', '', $url);
                $url = $GLOBALS['http_type'] . $config['site']['site_url'] . $url;
            }
        } elseif ($config['rewrite']['status'] == 0 && strpos($url, 'index.php') === false) {
            if (MAC_PATH != '/') {
                $url = str_replace(MAC_PATH, '/', $url);
            }
            //$url = MAC_PATH. 'index.php' . $url;
        }

        if ($config['rewrite']['suffix_hide'] == 1) {
            $url = str_replace('.html', '/', $url);
            if (strpos($model, '/show') === false && strpos($model, '/search') === false) {
                $url = str_replace(['-/', '_/', '-.', '_.'], '/', $url);
            }
        } else {
            if (strpos($model, 'search') === false && strpos($model, 'show') === false) {
                $url = str_replace(['-.', '/.'], '.', $url);
            }
        }
    }
    return $url;
}

function mac_array_del_column($data, $del)
{
    foreach ($data as $k => $v) {
        foreach ($del as $key => $val) {
            unset($data[$k][$val]);
        }
    }
    return $data;
}

function mac_url_page($url, $num)
{
    $url = str_replace(MAC_PAGE_SP . 'PAGELINK', ($num > 1 ? MAC_PAGE_SP . $num : ''), $url);
    $url = str_replace('PAGELINK', $num, $url);
    return $url;
}

function mac_url_create($str, $type = 'actor', $flag = 'vod', $ac = 'search', $sp = '&nbsp;')
{
    if (!$str) {
        return '未知';
    }
    $res = [];
    $str = str_replace(array('/', '|', ',', '，', ' '), ',', $str);
    $arr = explode(',', $str);
    foreach ($arr as $k => $v) {
        $res[$k] = '<a href="' . mac_url($flag . '/' . $ac, [$type => $v]) . '" target="_blank">' . $v . '</a>' . $sp;
    }
    return implode('', $res);
}

function mac_url_search($param = [], $flag = 'vod')
{
    return mac_url($flag . '/search', $param);
}

function mac_url_type($info, $param = [], $flag = 'type')
{
    $tab = 'vod';
    if ($info['type_mid'] == 1) {

    } else if ($info['type_mid'] == 2) {
        $tab = 'art';
    } else if ($info['type_mid'] == 8) {
        $tab = 'actor';
    } else if ($info['type_mid'] == 11) {
        $tab = 'website';
    }
    if (empty($param['id'])) {
        $param['id'] = $info['type_id'];
    }

    return mac_url($tab . '/' . $flag, $param, $info);
}

function mac_url_topic_index($param = [])
{
    return mac_url('topic/index', ['page' => $param['page']]);
}

function mac_url_topic_detail($info)
{
    return mac_url('topic/detail', [], $info);
}

function mac_url_role_index($param = [])
{
    return mac_url('role/index', ['page' => $param['page']]);
}

function mac_url_role_detail($info)
{
    return mac_url('role/detail', [], $info);
}

function mac_url_actor_index($param = [])
{
    return mac_url('actor/index', ['page' => $param['page']]);
}

function mac_url_actor_detail($info)
{
    return mac_url('actor/detail', [], $info);
}

function mac_url_actor_search($param)
{
    return mac_url('actor/search', $param);
}

function mac_url_plot_index($param = [])
{
    return mac_url('plot/index', ['page' => $param['page']]);
}

function mac_url_plot_detail($info, $param = [])
{
    return mac_url('plot/detail', ['page' => $param['page']], $info);
}

function mac_url_vod_plot($info, $param = [])
{
    return mac_url('vod/plot', $param, $info);
}

function mac_url_website_index($param = [])
{
    return mac_url('website/index', ['page' => $param['page']]);
}

function mac_url_website_detail($info)
{
    return mac_url('website/detail', [], $info);
}

function mac_url_website_search($param)
{
    return mac_url('website/search', $param);
}

function mac_url_art_index($param = [])
{
    return mac_url('art/index', ['page' => $param['page']]);
}

function mac_url_art_detail($info, $param = [])
{
    return mac_url('art/detail', ['page' => $param['page']], $info);
}

function mac_url_art_search($param)
{
    return mac_url('art/search', $param);
}

function mac_url_vod_index($param = [])
{
    return mac_url('vod/index', ['page' => $param['page']]);
}

function mac_url_vod_detail($info)
{
    return mac_url('vod/detail', [], $info);
}

function mac_url_vod_search($param)
{
    return mac_url('vod/search', $param);
}

function mac_url_vod_play($info, $param = [])
{
    if ($param == 'first') {
        $sid = intval(key($info['vod_play_list']));
        $nid = intval(key($info['vod_play_list'][$sid]['urls']));
        if ($sid == 0 || $nid == 0) {
            return '';
        }
        $param = [];
        $param['sid'] = $sid;
        $param['nid'] = $nid;
    }
    if (intval($param['sid']) < 1) {
        $param['sid'] = 1;
    }
    if (intval($param['nid']) < 1) {
        $param['nid'] = 1;
    }

    return mac_url('vod/play', ['sid' => $param['sid'], 'nid' => $param['nid']], $info);
}

function mac_url_vod_down($info, $param = [])
{
    if ($param == 'first') {
        $sid = intval(key($info['vod_down_list']));
        $nid = intval(key($info['vod_down_list'][$sid]['urls']));
        if ($sid == 0 || $nid == 0) {
            return '';
        }
        $param = [];
        $param['sid'] = $sid;
        $param['nid'] = $nid;
    }

    if (intval($param['sid']) < 1) {
        $param['sid'] = 1;
    }
    if (intval($param['nid']) < 1) {
        $param['nid'] = 1;
    }

    return mac_url('vod/down', ['sid' => $param['sid'], 'nid' => $param['nid']], $info);
}


function mac_label_website_detail($param)
{
    $where = [];
    if (is_numeric($param['id'])) {
        $where['website_id'] = ['eq', $param['id']];
    } else {
        $where['website_en'] = ['eq', $param['id']];
    }
    $where['website_status'] = ['eq', 1];
    $res = model('Website')->infoData($where, '*', 1);
    $GLOBALS['type_id'] = $res['info']['type_id'];
    $GLOBALS['type_pid'] = $res['info']['type']['type_pid'];
    return $res;
}

function mac_label_actor_detail($param)
{
    $where = [];
    if (is_numeric($param['id'])) {
        $where['actor_id'] = ['eq', $param['id']];
    } else {
        $where['actor_en'] = ['eq', $param['id']];
    }
    $where['actor_status'] = ['eq', 1];
    $res = model('Actor')->infoData($where, '*', 1);

    $GLOBALS['type_id'] = $res['info']['type_id'];
    $GLOBALS['type_pid'] = $res['info']['type']['type_pid'];
    return $res;
}

function mac_label_role_detail($param)
{
    $where = [];
    if (is_numeric($param['id'])) {
        $where['role_id'] = ['eq', $param['id']];
    } else {
        $where['role_en'] = ['eq', $param['id']];
    }
    $where['role_status'] = ['eq', 1];
    $res = model('Role')->infoData($where, '*', 1);
    return $res;
}

function mac_label_topic_detail($param)
{
    $where = [];
    if (is_numeric($param['id'])) {
        $where['topic_id'] = ['eq', $param['id']];
    } else {
        $where['topic_en'] = ['eq', $param['id']];
    }
    $where['topic_status'] = ['eq', 1];
    $res = model('Topic')->infoData($where, '*', 1);
    return $res;
}

function mac_label_art_detail($param)
{
    $where = [];
    if (is_numeric($param['id'])) {
        $where['art_id'] = ['eq', $param['id']];
    } else {
        $where['art_en'] = ['eq', $param['id']];
    }
    $where['art_status'] = ['eq', 1];
    $res = model('Art')->infoData($where, '*', 1);
    if ($res['code'] == 1) {
        if ($param['page'] > $res['info']['art_page_total']) {
            $param['page'] = $res['info']['art_page_total'];
        }
    }
    $GLOBALS['type_id'] = $res['info']['type_id'];
    $GLOBALS['type_pid'] = $res['info']['type']['type_pid'];

    return $res;
}

function mac_label_vod_detail($param)
{
    $where = [];
    if (is_numeric($param['id'])) {
        $where['vod_id'] = ['eq', $param['id']];
    } else {
        $where['vod_en'] = ['eq', $param['id']];
    }
    $where['vod_status'] = ['eq', 1];
    $res = model('Vod')->infoData($where, '*', 1);
    $GLOBALS['type_id'] = $res['info']['type_id'];
    $GLOBALS['type_pid'] = $res['info']['type']['type_pid'];
    return $res;
}

function mac_label_vod_role($param)
{
    $where = [];
    $where['role_rid'] = $param['rid'];
    $where['role_status'] = ['eq', 1];
    $order = 'role_sort desc,role_id desc';
    $res = model('Role')->listData($where, $order, 1, 999, 0, '*', 0, 0);
    return $res;
}

//获取域名分类
function mac_get_type_list()
{
    //获取域名
    $domain_conf = config('domain');
    //存在
    if (is_array($domain_conf) && !empty($domain_conf[$_SERVER['HTTP_HOST']])) {
        return true;
    }
    return false;
}

function mac_get_type_list_table()
{
    return $_SERVER['HTTP_HOST'] . '_type';

}

function mac_get_type_list_model()
{
    $new_table = mac_get_type_list_table();
    $new_table = str_replace('.', '_', $new_table);
    return $new_table;
}

function mac_label_type($param)
{
    //获取域名分类
    if (mac_get_type_list() != false) {
        $new_table = $_SERVER['HTTP_HOST'] . '_type';
        $new_table = str_replace('.', '_', $new_table);
        $model = new \app\common\model\TypeSeo($new_table);
        $type_info = $model->getCacheInfo($param['id']);
        $GLOBALS['type_id'] = $type_info['type_id'];
        $GLOBALS['type_pid'] = $type_info['type_pid'];
        $parent = $model->getCacheInfo($type_info['type_pid']);
    } else {
        $type_info = model('Type')->getCacheInfo($param['id']);
        $GLOBALS['type_id'] = $type_info['type_id'];
        $GLOBALS['type_pid'] = $type_info['type_pid'];
        $parent = model('Type')->getCacheInfo($type_info['type_pid']);
    }
    $type_info['parent'] = $parent;
    return $type_info;
}

function mac_data_count($tid = 0, $range = 'all', $flag = 'vod')
{
    if (!in_array($flag, ['vod', 'art', 'actor', 'role', 'topic', 'website'])) {
        $flag = 'vod';
    }
    if (!in_array($range, ['all', 'today', 'min'])) {
        $range = 'all';
    }

    $data = model('Extend')->dataCount();
    $key = 'type_' . $range . '_' . $tid;
    if ($tid > 0 && in_array($flag, ['vod', 'art'])) {

    } else {
        $key = $flag . '_' . $range;
    }
    return intval($data[$key]);
}

function mac_get_popedom_filter($group_type, $type_list = [])
{
    if (empty($type_list)) {
        $type_list = model('Type')->getCache('type_list');
    }
    $type_keys = array_keys($type_list);
    $group_type = trim($group_type, ',');
    $group_keys = explode(',', $group_type);
    $cha_keys = array_diff($type_keys, $group_keys);
    return implode(',', $cha_keys);
}

// 静态资源链接
function asset($url, $param = [])
{
    if (!isset($param['_v']) && is_static_resource($url)) {
        global $asset_md5;
        if ($asset_md5) {
            $relative_path = substr($url, strlen('/'));
            if (isset($asset_md5[$relative_path])) {
                $param['_v'] = $asset_md5[$relative_path];
            }
        }
        // dev 环境不走缓存
        if (\think\Env::get('app_debug')) {
            $param['_v'] = time();
        }
    }

    $url = mac_url_img(substr($url, 1));
    if ($param) {
        if (strpos($url, '?')) {
            $url .= '&';
        } else {
            $url .= '?';
        }
        $url .= build_url_query($param);
    }
    return $url;
}

function is_static_resource($url)
{
    static $exts = ['js', 'css', 'jpg', 'png', 'gif'];
    $ps = explode('?', $url);
    $url = $ps[0];
    $ps = explode('#', $url);
    $url = $ps[0];
    $ps = explode('.', $url);
    $ext = $ps[count($ps) - 1];
    return in_array($ext, $exts);
}

function charsetToGBK($str){
    return $str;
}

function build_url_query($param, $static = false)
{
    $arr = [];
    foreach ($param as $k => $v) {
        if ($static && !is_array($v)) {
            // 路径中不能包含%2F
            $v = urlencode(str_replace('/', '%2F', $v));
            $arr[] = $k . '/' . $v;
        } else {
            if (is_array($v)) {
                foreach ($v as $n => $s) {
                    $n = urlencode($n);
                    $arr[] = $k . "[$n]=" . urlencode($s);
                }
            } else {
                $arr[] = $k . '=' . urlencode($v);
            }
        }
    }
    if ($static) {
        $query = join('/', $arr);
    } else {
        $query = join('&', $arr);
    }
    return $query;
}
/**
 * 当type_id_1=0 根据type_id获取对应的type_id_1
 * @param  [type] $type_id [description]
 * @return [type]          [description]
 */
function get_type_pid_type_id( $type_id ){
    $type_pid = 0;
    if (($type_id >= 6 && $type_id <= 12) || $type_id == 1 || $type_id == 30){
        $type_pid = 1;
    } else if (($type_id >= 13 &&  $type_id <= 16) || $type_id == 24 || $type_id == 2){
        $type_pid = 2;
    } else if ($type_id >= 19 && $type_id <= 22){
        $type_pid = 4;
    } else if (($type_id >= 25 && $type_id <= 28) || $type_id == 3){
        $type_pid = 3;
    } else if ($type_id == 4){
        $type_pid = 4;
    }
    return $type_pid;
}
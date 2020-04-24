<?php

namespace app\crontab\command;

use similar_text\similarText;
use think\Cache;
use app\common\model\Type;
use app\common\model\CollectOk;
use app\common\model\Collect;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Log;

use GuzzleHttp\Client;
use Exception;
use QL\Ext\PhantomJs;
use QL\QueryList;

class Common extends Command
{

    protected $times;//超时time
    protected $get_port;//port

    //代理使用
    protected $proxy_username = 'zhangshanap1';
    protected $proxy_passwd = '76836051';
    protected $proxy_server = '183.129.244.16';
    protected $proxy_port = '88';
    protected $pattern = 'json';//API访问返回信息格式：json和text可选
    protected $num = 1;//获取代理端口数量
    protected $key_name = 'user_name=';
    protected $key_timestamp = 'timestamp=';
    protected $key_md5 = 'md5=';
    protected $key_pattern = 'pattern=';
    protected $key_num = 'number=';
    protected $key_port = 'port=';


    public function ParSing($parameter)
    {
        $parameter_array = array();
        $arry = explode('#', $parameter);
        foreach ($arry as $key => $value) {
            $zzz = explode('=', $value);
            $parameter_array[$zzz[0]] = $zzz[1];

        }
        return $parameter_array;

    }

    //返回当前时间戳（单位为 ms）
    public function get_timestamp()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

//进行md5加密
    public function get_md5_str($str)
    {
        return md5($str);
    }

//返回请求分配代理端口URL链接
    public function get_open_url()
    {
        $this->times = time();
        $time_stamp = $this->get_timestamp();
        $md5_str = $this->get_md5_str($this->proxy_username . $this->proxy_passwd . strval($time_stamp));
        return 'http://' . $this->proxy_server . ':'
            . $this->proxy_port . '/open?' . $this->key_name . $this->proxy_username .
            '&' . $this->key_timestamp . strval($time_stamp) .
            '&' . $this->key_md5 . $md5_str .
            '&' . $this->key_pattern . $this->pattern .
            '&' . $this->key_num . strval($this->num);
    }

//返回释放代理端口URL链接
    public function get_close_url($auth_port)
    {
        $time_stamp = $this->get_timestamp();
        $md5_str = $this->get_md5_str($this->proxy_username . $this->proxy_passwd . strval($time_stamp));
        return 'http://' . $this->proxy_server . ':'
            . $this->proxy_port . '/close?' . $this->key_name . $this->proxy_username .
            '&' . $this->key_timestamp . strval($time_stamp) .
            '&' . $this->key_md5 . $md5_str .
            '&' . $this->key_pattern . $this->pattern .
            '&' . $this->key_port . strval($auth_port);
    }

//返回重置本用户已使用ip URL链接
    public function get_reset_url()
    {
        $time_stamp = $this->get_timestamp();
        $md5_str = $this->get_md5_str($this->proxy_username . $this->proxy_passwd . strval($time_stamp));
        return 'http://' . $this->proxy_server . ':'
            . $this->proxy_port . '/reset_ip?' . $this->key_name . $this->proxy_username .
            '&' . $this->key_timestamp . strval($time_stamp) .
            '&' . $this->key_md5 . $md5_str .
            '&' . $this->key_pattern . $this->pattern;
    }

//使用代理进行测试 url为使用代理访问的链接，auth_port为代理端口
    public function testing($url, $auth_port)
    {
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy_server); //代理服务器地址
        curl_setopt($ch, CURLOPT_PROXYPORT, $auth_port); //代理服务器端口
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
        //如果访问为https协议
        if (substr($url, 0, 5) == "https") {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }

        $file_contents = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $httpCode;
    }

    //更新cookie
    protected function getCookie($url,$is = false)
    {
        if($is != true){
            return 'll="108288";bid=h4nqLajQEBo';
        }else{
            $client = new Client();
            $response = $client->get($url);
            // 获取响应头部信息
            $headers = $response->getHeaders();
            $cookie = "";
            foreach ($headers['Set-Cookie'] as $k) {
                if (strpos(explode(';', $k)[0], 'll') !== false) {
                    $cookie .= explode(';', $k)[0] . ';';
                }
                if (strpos(explode(';', $k)[0], 'bid') !== false) {
                    $cookie .= explode(';', $k)[0] . '';
                }
            }
        }
        return $cookie;
    }

    protected function newCookie($cookies)
    {
        $cookie = 'll="108288";bid=h4nqLajQEBo; douban-fav-remind=1; __gads=ID=f547fc5d1024460e:T=1584933974:S=ALNI_MYnz5KEHQFfcZy0gMy6CM04qFHEGg;  _vwo_uuid_v2=DE8FD61CD60225FE96D81709B68421C2D|866f6dabae9a822d17e89ca947c01f78; __yadk_uid=HPbvxvJ9JN8yUqI6foqDYbhNLOHg2OMc; __utmc=30149280; push_noty_num=0; push_doumail_num=0; __utmv=30149280.21552; douban-profile-remind=1; __utmz=30149280.1587373187.4.3.utmcsr=baidu|utmccn=(organic)|utmcmd=organic; dbcl2="215524010:bdDl9E8vVTg"; ck=m31b; ap_v=0,6.0; ct=y; _pk_ref.100001.2939=%5B%22%22%2C%22%22%2C1587439340%2C%22https%3A%2F%2Fmovie.douban.com%2F%22%5D; _pk_ses.100001.2939=*; __utma=30149280.1772134204.1587359482.1587432721.1587439341.7; __utmt=1; _pk_id.100001.2939=1deb2b5e8988f44c.1587174800.9.1587439359.1587434637.; __utmb=30149280.9.9.1587439359009';
        $cookieArray = explode(';', $cookie);

        $cookieArray[16] = '_pk_ref.100001.2939' . urlencode('["","",time(),"https://movie.douban.com/"]');
        $cookieArray[21] = '30149280.9.9.' . time() . rand(0, 9) . rand(1, 6) . rand(0, 6);
        $cookieArray[3] = str_replace('T=1584933974', 'T=' . time(), $cookieArray[3]);
        $cookieArray[11] = str_replace('1587373187', time(), $cookieArray[11]);
        $cookieArray[18] = str_replace('1587439341', time(), $cookieArray[18]);
        $cookieArray[20] = str_replace('1587439359', time() + 11, $cookieArray[20]);
        $cookieArray[20] = str_replace('1587174800', time() + 600, $cookieArray[20]);
        $cookieArray[0] = $cookies;
        unset($cookieArray[1]);
        return implode($cookieArray, ';');
    }

    public function update_url_proxy($error_count,$url){
        $error_count++;
        if ($error_count > 10) {
            $tmp = $this->testing($url, $this->get_port);
            if ($tmp != 200 && $this->times + (50 * 3)) {
                $this->get_port = $this->getDouBan(); //重新构成代理端口
                echo 'test_proxy|| httpCode:' . $tmp . "\n <br>";
                file_put_contents('log.txt', 'test_proxy|| httpCode:' . $tmp . PHP_EOL, FILE_APPEND);
                try {
                    $close_url = $this->get_close_url($this->get_port);
                    $r = file_get_contents($close_url);
                    $result = iconv("gb2312", "utf-8//IGNORE", $r);
                    echo 'close_url||' . $result;
                    file_put_contents('log.txt', 'close_url||' . $result . PHP_EOL, FILE_APPEND);
                } catch (Exception $e) {
                    file_put_contents('log.txt', 'close_url||' . $e . PHP_EOL, FILE_APPEND);
                }
            }
        }
    }
    //获取代理端口
    public function getDouBan()
    {
        //实例简单演示如何正确获取代理端口，使用代理服务测试访问https://ip.cn，验证后释放代理端口
        $file = 'log.txt';
        $port = '';//代理端口变量
        try {
            $open_url = $this->get_open_url();
//            p($open_url);
            $r = file_get_contents($open_url);
            $result = iconv("gb2312", "utf-8//IGNORE", $r);
            $code = json_decode($result, true);
            echo $result . "\n <br>";
            file_put_contents($file, date('Y-m-d H:i:s', time()) . PHP_EOL . 'open_url||' . $result . PHP_EOL, FILE_APPEND);
            $json_arr = json_decode($result, true);
            $code = $json_arr['code'];
            if ($code == 108) {
                $reset_url = $this->get_reset_url();
                $r = file_get_contents($reset_url);
            } else if ($code == 100) {
                $port = strval($json_arr['port'][0]);
            }

        } catch (\Exception $e) {
            file_put_contents($file, 'open_url||' . $e . PHP_EOL, FILE_APPEND);
        }
        return $port;
    }
    protected function isJsonBool($data = '', $assoc = false)
    {
        $data = json_decode($data, $assoc);
        if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
            return $data;
        }
        return false;
    }

    public  function strToUtf8($str)
    {
        $encode = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        if ($encode == 'UTF-8') {
            return $str;
        } else {
            return mb_convert_encoding($str, 'UTF-8', $encode);
        }
    }

}
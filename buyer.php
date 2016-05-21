<?php
    error_reporting(0);
    if( !headers_sent() && // 如果页面头部信息还没有输出
    extension_loaded("zlib") && // 而且php已经加载了zlib扩展
    strstr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip")) //而且浏览器接受GZIP
    {
      ini_set('zlib.output_compression', 'On');
      ini_set('zlib.output_compression_level', '4');
    }
    header("content-Type: text/html; charset=gbk");
    function async_get_url($url_array, $wait_usec = 0)
    {
        if (!is_array($url_array))
            return false;

        $wait_usec = intval($wait_usec);

        $data    = "";
        $handle  = array();
        $running = 0;

        $mh = curl_multi_init(); // multi curl handler

        $i = 0;
        foreach($url_array as $url) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return don't print
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 7);

            curl_multi_add_handle($mh, $ch); // 把 curl resource 放进 multi curl handler 里

            $handle[$i++] = $ch;
        }

        /* 执行 */
        do {
            curl_multi_exec($mh, $running);

            if ($wait_usec > 0) /* 每个 connect 要间隔多久 */
                usleep($wait_usec); // 250000 = 0.25 sec
        } while ($running > 0);

        /* 读取资料 */
        foreach($handle as $i => $ch) {
            $content  = curl_multi_getcontent($ch);
            $data = $data . ((curl_errno($ch) == 0) ? $content : false);
            unset($content);
        }

        /* 移除 handle*/
        foreach($handle as $ch) {
            curl_multi_remove_handle($mh, $ch);
        }

        curl_multi_close($mh);

        return $data;
    }
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    function checkstr($str, $needle){
        if($needle != ""){
            if(strpos($str, $needle)){
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }
    }
    $page = "";
    $site = "";
    $keyword = "";
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $page = test_input($_GET["page"]);
        $site = test_input($_GET["site"]);
        $keyword = test_input($_GET["keyword"]);
    }
    $zhengze1  = '/(?:t f14">)(.*?)(?:<)/';//匹配商品名数码之家的
    $zhengze2 = '/(?:a href=")(.*?)(?:" name=)/';//匹配url
    $zhengze3 = '/(?=id=[0-9]{3,}&page=2&property=0&ClassID=3>)(.*?)</';
    $zhengze4 = '/forum_read\.asp\?id=[0-9]{3,}&page=2&property=0&ClassID=3/';
    $zhengze5 = '/(\/p\/[0-9]{3,})" title="(.*?)"/';//匹配标题和url，图拉丁吧的
    $arr = array('{','[');
    $result = array();
    $url_arr = array();
    $counter = (int)$page;
    if ($site == "mydigit") {
        while($counter){
            array_push($url_arr, 'http://bbs.mydigit.cn/thread.php?fid=137&search=all&page=' . (string)$counter);
            $counter-=1;
        }
        $source = async_get_url($url_arr);
        if(preg_match_all($zhengze1, $source, $matches1) and preg_match_all($zhengze2, $source, $matches2)) {
            for ($i=0;$i<sizeof($matches2[1]);$i++) {
                if(checkstr(iconv('gbk','utf-8',$matches1[1][$i]), $keyword)){
                    array_push($result, array("title"=>iconv('gbk','utf-8',$matches1[1][$i]),"url"=>'http://bbs.mydigit.cn/' . $matches2[1][$i]));
                }
            }
            //echo json_encode($result);
        }

        echo json_encode($result);
    }
    elseif($site == "tulading"){
        while($counter){
            array_push($url_arr, 'http://tieba.baidu.com/f?kw=%E5%9B%BE%E6%8B%89%E4%B8%81&ie=utf-8&pn=' . (string)(($counter-1)*50));
            $counter-=1;
        }
        $source = async_get_url($url_arr);
        if(preg_match_all($zhengze5, $source, $matches1)){
            for ($i=0;$i<sizeof($matches1[1]);$i++) {
                if(checkstr($matches1[2][$i], $keyword)){
                    array_push($result, array("title"=>$matches1[2][$i],"url"=>'http://tieba.baidu.com' . iconv('gbk','utf-8',$matches1[1][$i])));
                }
            }
        }
        echo json_encode($result);
    }
?>

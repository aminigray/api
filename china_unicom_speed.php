<?php
    $url = array();
    for($idx=1;$idx<255;$idx++){
        array_push($url,"http://120.52.72.$idx/www.hi-pda.com/forum/templates/colors/images/logo.gif");
    }
    $result = async_get_url($url);
    sort($result);
    echo json_encode($result);
    function async_get_url($url_array, $wait_usec = 0)
    {
        if (!is_array($url_array))
            return false;
        $wait_usec = intval($wait_usec);
        $data    = array();
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
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 302 redirect
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
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if(preg_match('/[2-3]0[0-8]/',$http_code)){
                preg_match('/120\.52\.72\.[0-9]{1,3}/',curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),$matches);
                array_push($data,array("total_time"=>curl_getinfo($ch, CURLINFO_TOTAL_TIME), "download_speed"=>curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD), "host_name"=>$matches[0]));
                unset($matches);
                unset($http_code);
            }
        }
        /* 移除 handle*/
        foreach($handle as $ch) {
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);
        return $data;
    }
?>

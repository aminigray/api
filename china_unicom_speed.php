<?php
    error_reporting(0);
    for($idx=1;$idx<255;$idx++){
        $url = "http://120.52.72." . (string)$idx . "/www.hi-pda.com/forum/templates/colors/images/logo.gif";
        $stime=microtime(true);
        $result_code = file_get_contents($url);
        $etime=microtime(true);
        if($result_code != false){
            $distance = $etime - $stime;
            echo "120.52.72.$idx ". "time: $distance\n";
        }
    }
?>

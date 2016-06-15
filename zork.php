<?php
/*
    方倍工作室 http://www.cnblogs.com/txw1958/
    CopyRight 2013 www.fangbei.org  All Rights Reserved
*/

define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}
function cleanHtml($str){ 
    $str=trim($str);
    $str=strip_tags($str,"");
    $str=ereg_replace("\t","",$str);
    $str=ereg_replace("\r\n","",$str);
    $str=ereg_replace("\r","",$str);
    $str=ereg_replace("\n","",$str);
    $str=ereg_replace(" "," ",$str);
    return trim($str);
}    
function nodelist2string($nodelist) {//把xpath获得的nodelist全部输出为string，仅在只有一个元素的时候有效
    foreach($nodelist as $node) {
        $a_node = $node->nodeValue;
        if($a_node) {
            return $a_node;
        }
        else {
            return 0;
        }
    }
}
function dom_parser($html, $mypath) {//构建xpath并查找
    $doc = new DomDocument;
    $doc->loadHTML($html);
    $xpath = new DOMXpath($doc);
    $href = $xpath->query($mypath);
    return nodelist2string($href);
}
function curl_get_contents($url,$timeout=5,$method='get',$post_fields=array(),$reRequest=0,$referer="") { //封装 curl
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE );
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false );
   curl_setopt($ch, CURLOPT_REFERER, $referer);
   if (strpos($method,'post')>-1) {
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS,$post_fields);
   }
   if (strpos($method,'WithHeader')>-1) {
       curl_setopt($ch, CURLOPT_HEADER, true);
       curl_setopt($ch, CURLOPT_NOBODY, false);
   }
   $output = curl_exec($ch);
   if (curl_errno($ch)==0) {
       if (strpos($method,'WithHeader')>-1) {
           $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
           $header = substr($output, 0, $headerSize);
           $body = substr($output, $headerSize);
           return array($header,$body,$output);
       } else {
           return $output;
       }
   } else {
       if ($reRequest) {
           $reRequest--;
           return curl_get_contents($url,$timeout,$method,$post_fields,$reRequest);
       } else {
           return false;
       }
   }
}
class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = $postObj->Content;
            $time = time();
            $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>";
            if($event == "subscribe") {
                $msgType = "text";#回复数据类型为文本
                $contentStr = "简单指令:read open east(e) west(w) up down inventory";//回复欢迎信息
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;                
            }                        
            if($keyword != '' || $keyword != "restart")
            {
                $msgType = "text";
                $url ="http://www.web-adventures.org/cgi-bin/webfrotz?s=ZorkDungeon&x=Q" . $fromUsername . '&a=' . urlencode($keyword);
                echo $content = curl_get_contents($url);
                $zhengze_place = '/status"> ([A-Za-z0-9]{0,99})/';
                $zhengze_restart = '/n=([0-9]{1,9})/';
                if($keyword == "restart"){
                    preg_match($zhengze_restart, $content, $matches);
                    $content = curl_get_contents("http://www.web-adventures.org/cgi-bin/webfrotz?s=ZorkDungeon&x=Q" . $fromUsername  . '&' .$matches[0]);
                    
                }
                preg_match($zhengze_place, $content, $matches);
                rsort($matches);
                $place = trim(str_replace('status">', "" ,$matches[0]));
                $content = cleanHtml($content);
                $zhengze_score = '/SCORE: [0-9]{1,3}     MOVES: [0-9]{1,3}/';
                preg_match($zhengze_score,$content,$matches);
                $status = $matches[0];
                $content = strrchr($content,$place);
                $contentStr = $status . "\n" . $place . "\n" . str_replace($place, "", $content);
                $contentStr = str_replace('&gt&nbsp;_uacct = "UA-4654789-1";urchinTracker();','',$contentStr);
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }
        }elseif($keyword == "restart"){
            $url ="http://www.web-adventures.org/cgi-bin/webfrotz?s=ZorkDungeon&x=Q" . $fromUsername . '&a=' . urlencode($keyword);
        }
    }
}
?>

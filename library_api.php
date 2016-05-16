<?php
    header("content-Type: text/html; charset=utf-8");
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
    function nodelist2string($nodelist) {//把xpath获得的nodelist全部输出为string，仅在只汗一个元素的时候有效
        foreach($nodelist as $node) {
            $a_node = $node->nodeValue;
        }
        return $a_node;
    }
    function dom_parser($html, $mypath) {//构建xpath并查找
        $doc = new DomDocument;
        $doc->loadHTML($html);
        $xpath = new DOMXpath($doc);
        $href = $xpath->query($mypath);
        return $href;
    }
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;     
    }   
    $search = "";
    $toplist ="";
    $num = "";
    $newbook = "";
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $search = test_input($_GET["search"]);
        $toplist = test_input($_GET["toplist"]);
        $num = test_input($_GET["num"]);
        $newbook = test_input($_GET["newbook"]);
    }
    if ($toplist == "yes") {
        $source = curl_get_contents("http://aleph.dlmu.edu.cn:8991/opac_lcl_chi/loan_top_ten/loan.ALL.ALL.y");
        $zhengze = '/\">.*?</';
        $arr = array('">','<');
        $json_arr = array();
        if(preg_match_all($zhengze, $source , $matches)) {
            foreach($matches as $match) {
                array_push($json_arr, array('title'=>str_replace($arr, '', $match)));
            }
            echo json_encode($json_arr);
        }
    }
    elseif ($search != "") {
        $url = "http://aleph.dlmu.edu.cn:8991/F/6RCM1U1Y9KA5GU3J9LGH4VKL71YTP28TQMMLLM9K82XH9GLLV1-02032?func=find-b&find_code=WRD&request=". $search ."&filter_code_1=WLN&filter_request_1=&filter_code_2=WYR&filter_request_2=&filter_code_3=WYR&filter_request_3=&filter_code_4=WFM&filter_request_4=&filter_code_5=WSL&filter_request_5=";
        $source = curl_get_contents($url);
        $book_name = dom_parser($source, "//div[@class='itemtitle']");
        $book_code = dom_parser($source, "//td[@class='col2']/table/tbody/tr[1]/td[@class='content'][2]");
        $book_state = dom_parser($source, "//td[@class='col2']/table/tbody/tr[4]/td[@class='libs']/a");
        $book_publish = dom_parser($source, "//td[@class='col2']/table/tbody/tr[2]/td[@class='content'][1]");
        $book_author = dom_parser($source, "//td[@class='col2']/table/tbody/tr[1]/td[@class='content'][1]");
        $book_state_href = dom_parser($source , "//td[@class='col2']/table/tbody/tr[4]/td[@class='libs']/a/@href");
        echo $source;
    }
    elseif($newbook == "yes") {
        $url = "http://aleph.dlmu.edu.cn:8991/cgi-bin/newbook.cgi?base=ALL&cls=ALL&date=180";
        $source = curl_get_contents($url);
        $zhengze = '/t:".*?"/';
        $arr = array('t:','"');
        $json_arr = array();
        if(preg_match_all($zhengze, $source , $matches)) {
            foreach($matches as $match) {
                array_push($json_arr, array('title'=>str_replace($arr, '', $match)));
            }
            echo json_encode($json_arr);
        }        
    }
    

?>

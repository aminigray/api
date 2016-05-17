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
        return nodelist2string($href);   
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
    $douban = "";
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $search = test_input($_GET["search"]);
        $toplist = test_input($_GET["toplist"]);
        $num = test_input($_GET["num"]);
        $newbook = test_input($_GET["newbook"]);
        $douban = test_input($_GET["douban"]);
    }
    if ($toplist == "yes") {
        $source = curl_get_contents("http://aleph.dlmu.edu.cn:8991/opac_lcl_chi/loan_top_ten/loan.ALL.ALL.y");
        $zhengze = '/\">.*?</';
        $arr = array('">','<');
        $json_arr = array();
        if(preg_match_all($zhengze, $source , $matches)) {
            foreach($matches as $match) {
                $json_arr = $json_arr + array("title"=>str_replace($arr, '', $match));
            }
            echo json_encode($json_arr);
        }
    }
    elseif ($search != "") {
        $url = "http://202.118.84.130:1701/primo_library/libweb/action/search.do?ct=facet&fctN=facet_tlevel&fctV=available&rfnGrp=show_only&dscnt=0&frbg=&scp.scps=scope%3A(DLMH)%2Cprimo_central_multiple_fe&tab=default_tab&dstmp=1456801597218&srt=rank&ct=search&mode=Basic&&dum=true&indx=1&vl(freeText0)=".$search;
        $url = $url."&fn=search&vid=dlmh";//海事大学图书馆书名查找入口
        $html_source = curl_get_contents($url);
    //    echo dom_parser($html_source, "//li[@id='exlidResult0-LocationsTab']/a/@href");//获取图书所在位置
        $url = "http://202.118.84.130:1701/primo_library/libweb/action/" . dom_parser($html_source, "//li[@id='exlidResult0-LocationsTab']/a/@href");//查找图书所在位置(在架状态页面)
        $url_2 = "http://202.118.84.130:1701/primo_library/libweb/action/" . dom_parser($html_source, "//a[@id='exlidResult0-detailsTabLink']/@href");//查找图书所在位置(详细信息页面)
        $html_source_2 = curl_get_contents($url_2);
        $book_isbn = dom_parser($html_source_2, "//ul/li[@id='识别符-1']/span[@class='EXLDetailsDisplayVal']");
        $html_source = curl_get_contents($url);
        $book_name = dom_parser($html_source, "//h1[@class='EXLResultTitle']");//获取书籍名称
        $book_author = dom_parser($html_source_2,"//ul/li[@id='著者-1']/a[@class='EXLLinkedField']");//获取书籍作者
        $book_publisher = dom_parser($html_source_2,"//ul/li[@id='出版发行-1']/span[@class='EXLDetailsDisplayVal']");//获取书籍出版社
        $book_location = trim(dom_parser($html_source, "//span[@class='EXLLocationsTitleContainer']"));//获取书籍所在位置
        $book_details = dom_parser($html_source, "//h3[@class='EXLResultFourthLine']");//获取书籍细节     
        $book_states = dom_parser($html_source, "//td[@class='EXLLocationTableColumn3']");//获取在架状态
        $book_ztflh = dom_parser($html_source, "//cite");//获取书籍中图分类号
        $pa = '{[a-zA-Z]{1,2}.*[0-9]}';
        if (preg_match($pa, $book_ztflh, $a_book_ztflh)) {
            $book_ztflh = $a_book_ztflh[0];
        }
        if (ctype_space($contentStr)){
            echo"没有你想要找的书籍";
        }
        echo json_encode(array("book_isbn"=>$book_isbn, "book_name"=>$book_name, "book_author"=>$book_author, "book_publisher"=>$book_publisher, "book_location"=>$book_location, "book_states "=>$book_states, "book_details"=>$book_details, "book_ztflh"=>$book_ztflh));
    }
    elseif($newbook == "yes") {
        $url = "http://aleph.dlmu.edu.cn:8991/cgi-bin/newbook.cgi?base=ALL&cls=ALL&date=180";
        $source = curl_get_contents($url);
        $zhengze = '/t:".*?"/';
        $arr = array('t:','"');
        $json_arr = array();
        if(preg_match_all($zhengze, $source , $matches)) {
            foreach($matches as $match) {
                $json_arr = $json_arr + array("title"=>str_replace($arr, '', $match));
            }
            echo json_encode($json_arr);
        }        
    }
    elseif($douban != "") {
        if(preg_match('/[0-9]{13}/',$douban) or preg_match('/[0-9]{10}/',$douban)) {
            $url = 'https://api.douban.com/v2/book/isbn/' . $douban . '?fields=rating,image,price';
            $source = curl_get_contents($url);
            $json = json_decode($source);
            $book_rating = $json->rating->average;
            $book_image = $json->image;
            $book_price = $json->price;
            echo json_encode(array("book_rating"=>$book_rating, "book_image"=>$book_image, "book_price"=>$book_price));
        }
        else
            echo "错误的isbn";
    }
    

?>

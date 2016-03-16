<?php
    error_reporting(E_ALL | E_STRICT);//错误报告为严格
    header("Content-Type: text/html;charset=utf-8");//防止出现乱码
    $isbn = "";
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $isbn = test_input($_GET["isbn"]);
    } 
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
                $my_html = "
                    <html>
                        <body>
                            <font size=12>%s</font><br />
                            <img src=%s><br /> 
                            <strong>%s</strong><br />                           
                            <p>%s</p><br />
                        </body>
                    </html>                
                ";
                $arr = array("ISBN","isbn"," ","-");
                $isbn = str_replace($arr, "", $isbn);
                $douban_api = "https://api.douban.com/v2/book/isbn/" . $isbn;
                $html_source = json_decode(file_get_contents($douban_api),false);
                $book_price = $html_source->price;
                $book_image = $html_source->images->small;
                $book_score = $html_source->rating->average;
                $book_author_intro = $html_source->author_intro;
                $book_summary = $html_source->summary;
                $result = sprintf($my_html, $book_score, $book_image, $book_price, $book_summary);
                if (ctype_space($book_summary)){
                    echo "找不到书籍";
                }
                else{
                    echo $result;
                }
?>

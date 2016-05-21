#各种api

##library_api


toplist:为非空时返回图书馆热门书籍排行榜

newbook:为非空时返回图书馆新购书籍

search:查找书籍，返回ISBN，作者，书名，所在地，在架状态，中图分类号

douban:接受参数为13或10位ISBN编号，返回书籍封面和平均评分

e.g:
http://127.0.0.1/library_api.php?newbook=yes&toplist=yes&search=qw
http://127.0.0.1/library_api.php?toplist=yes
http://127.0.0.1/library_api.php?search=qw

```
[
    [
        "信号与系统(作者：于凤芹,) - 2015",
        "物理学(第六版)习题分析与解答(作者：马文蔚) - 2015",
        "R软件教程与统计分析 :入门到精通(作者：麦考斯) - 2015",
        "现代机械工程图学习题集(作者：宋健) - 2015",
        "有机化学(作者：侯士聪) - 2015",
        "公司财务管理案例分析(作者：马忠) - 2015",
        "误差理论与数据处理(作者：费业泰) - 2015",
        "Photoshop网页设计从入门到精通(作者：李彦广) - 2015",
        "超大规模集成电路设计(作者：曲英杰) - 2015",
        "行摄 :Photoshop CC后期修片高手之道(作者：金玉洁) - 2015",
        "软件随想录(作者：斯波尔斯凯) - 2015",
        "邓颖超传(作者：金凤) - 1993",
        "电网络理论(作者：巴拉巴尼安,) - 1982",
        "全球海上遇险和安全系统(作者：庄司和民) - 1991",
        "实用电子电路设计制作例解(作者：颜杰先) - 1993",
    ]
  ]
```

#buyer_api.php
接收参数:

site(必须):接受参数tulading，mydigit。对应数码之家或者图拉丁吧

page(必须):传入>=1的整数，为抓取的页面数量

keyword(选):为获取标题后的筛选关键词

e.g:
http://127.0.0.1/buyer.php?site=mydigit&page=3&keyword=test

```
    [{
        "title": "清风无线插座转换器13.9元 无线蓝牙音箱插卡金属低音炮29.9",
        "url": "http://bbs.mydigit.cn/read.php?tid=1665309"
    },
    {
        "title": "304不锈钢地漏仅7.9元包邮！康漫护腰带腰托围仅5.9元包邮！",
        "url": "http://bbs.mydigit.cn/read.php?tid=1665740"
    },
    {
        "title": "一安软头电子体温计9.9秒杀！小能人车载手机磁力支架9.9包邮！",
        "url": "http://bbs.mydigit.cn/read.php?tid=1665759"
    },
    {
        "title": "快活林 5斤活性炭仅5.9元包邮！心相印 10卷筒纸仅14.9元！",
        "url": "http://bbs.mydigit.cn/read.php?tid=1665552"
    }]
 ```

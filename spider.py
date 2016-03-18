#coding=utf-8
import urllib
import urllib2
from lxml import etree

def spider(num):
    url = 'http://202.118.84.130:1701/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=dmuzw000'+str(num)
    response = urllib.urlopen(url)
    page = response.read()
    return page

def analyse(page):
    doc = etree.HTML(page.decode('utf-8','ignore'))
    book_name = doc.xpath(u"//div[@class='EXLLinkedFieldTitle']/text()")
    book_publish = doc.xpath(u"//ul/li[@id='出版发行-1']/span[@class='EXLDetailsDisplayVal']/text()")
    book_author = doc.xpath(u"//ul/li[@id='著者-1']/a[@class='EXLLinkedField']/text()")
    return book_name[0]

def main():
    print analyse(spider(100000)).encode('utf-8')


main()

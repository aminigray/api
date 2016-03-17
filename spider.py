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
    book_name = doc.xpath(u"//div[@class='EXLLinkedFieldTitle']")
    book_publish = doc.xpath(u"//li[@id='出版发行-1']/span[@class='EXLDetailsDisplayVal']")
    book_isbn = doc.xpath(u"//li[@id='识别符-1']/span[@class='EXLDetailsDisplayVal']")
    book_author = doc.xpath(u"//li[@id='著者-1']/a[@class='EXLLinkedField']")
    book_ztflh = doc.xpath(u"//li[@id='著者-1']/a[@class='EXLLinkedField']")
    print doc
    print book_ztflh

def main():
    analyse(spider(100000))
    
    
main()    

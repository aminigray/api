#coding=utf-8
import urllib
import urllib2
from lxml import etree
import Queue
import MySQLdb

q = Queue.Queue()

def SQLwrite(book_isbn, book_author, book_publisher, book_location, book_state, book_ztfh, book_name):
    db = MySQLdb.connect("localhost","root"," "," ", " " )
    cursor = db.cursor()
    sql = """
    INSERT INTO books(book_isbn, 
                      book_author, book_publisher,
                      book_location, book_state,
                      book_ztfh, book_name)
    VALUES ("%s", "%s", "%s", "%s", "%s", "%s", "%s")
    """ % (book_isbn, book_author, book_publisher, book_location, book_state, book_ztfh, book_name)
    try:
        cursor.execute(sql)
        db.commit()
    except:
        db.rollback()
        
    db.close()

def spider(num):
    url = 'http://202.118.84.130:1701/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=dmuzw000'+str(num)
    response = urllib.urlopen(url)
    page = response.read()
    return page

def analyse_index(page):
    doc = etree.HTML(page.decode('utf-8','ignore'))
    book_name = doc.xpath(u"//div[@class='EXLLinkedFieldTitle']/text()")
    book_publisher = doc.xpath(u"//ul/li[@id='出版发行-1']/span[@class='EXLDetailsDisplayVal']/text()")
    book_author = doc.xpath(u"//ul/li[@id='著者-1']/a[@class='EXLLinkedField']/text()")
    book_href = doc.xpath(u"//li[@id='exlidResult0-LocationsTab']/a/@href")
    location_href = 'http://202.118.84.130:1701/primo_library/libweb/action/' + book_href[]
    q.put(location_href)
    return (book_name[0].encode('utf-8'), book_publisher[0].encode('utf-8'), book_author[0].encode('utf-8'))
    
def analyse_location(page):
    doc = etree.HTML(page.decode('utf-8','ignore'))
    book_ztfh = doc.xpath(u"//cite/text()")
    book_location = doc.xpath(u"//span[@class='EXLLocationsTitleContainer']/text()")
    book_state = doc.xpath(u"//td[@class='EXLLocationTableColumn3']/text()")
    return (book_ztfh[0].encode('utf-8'), book_location[0].encode('utf-8'), book_state[0].encode('utf-8'))
def main():
    print analyse(spider(100000)).encode('utf-8')


main()

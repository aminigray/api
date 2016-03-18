#coding=utf-8
import urllib
import urllib2
from lxml import etree
import Queue
import MySQLdb

q1 = Queue.Queue()
q2 = Queue.Queue(maxsize = 7)
temp_list = []


def SQLwrite(book_isbn, book_author, book_publisher, book_name, book_location, book_state, book_ztfh):
    db = MySQLdb.connect("localhost", " ", " ", " " )
    cursor = db.cursor()
    sql = """
    INSERT INTO books(book_isbn, 
                      book_author, book_publisher,
                      book_name, book_location, book_state,
                      book_ztfh )
    VALUES ("%s", "%s", "%s", "%s", "%s", "%s", "%s")
    """ % (book_isbn, book_author, book_publisher, book_location, book_state, book_ztfh, book_name)
    try:
        cursor.execute(sql)
        db.commit()
    except:
        db.rollback()
        
    db.close()

def xpathNull(doc, yourPath):
    temp = doc.xpath(yourPath)
    if len(temp) == 0:
        temp.append("NULL")
        return temp
    else:
        return temp
def analyse_index(num):
    url = 'http://202.118.84.130:1701/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=dmuzw000'+str(num)
    response = urllib.urlopen(url)
    page = response.read()
    doc = etree.HTML(page.decode('utf-8','ignore'))
    book_name = xpathNull(doc,u"//div[@class='EXLLinkedFieldTitle']/text()")
    book_publisher = xpathNull(doc,u"//ul/li[@id='出版发行-1']/span[@class='EXLDetailsDisplayVal']/text()")
    book_author = xpathNull(doc,u"//ul/li[@id='著者-1']/a[@class='EXLLinkedField']/text()")
    book_href = xpathNull(doc,u"//li[@id='exlidResult0-LocationsTab']/a/@href")
    book_isbn = xpathNull(doc,u"//li[@id='识别符-1']/span[@class='EXLDetailsDisplayVal']/text()")
    location_href = 'http://202.118.84.130:1701/primo_library/libweb/action/' + book_href[0]
    if len(book_isbn) == 0:
        book_isbn.append('')
    q1.put(location_href)
    q2.put(book_isbn[0].replace(' ',''))
    q2.put(book_author[0].replace(' ',''))
    q2.put(book_publisher[0].replace(' ',''))
    q2.put(book_name[0])
    
def analyse_location():
    url = q1.get()
    response = urllib.urlopen(url)
    page = response.read()
    doc = etree.HTML(page.decode('utf-8','ignore'))
    book_ztfh = xpathNull(doc,u"//cite/text()")
    book_location = xpathNull(doc,u"//span[@class='EXLLocationsTitleContainer']/text()")
    book_state = xpathNull(doc,u"//td[@class='EXLLocationTableColumn3']/text()")
    q2.put(book_location[0].strip())
    q2.put(book_state[0].replace(' ',''))
    q2.put(book_ztfh[0].replace(' ',''))

def run():
    analyse_index(300000)
    if q1.empty() == False:
        analyse_location()
    if q2.full() == True:
        for i in range(7):
            print q2.get()

run()

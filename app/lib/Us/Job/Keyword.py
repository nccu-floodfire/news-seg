#encoding=utf-8

class Keyword:
    def getOptions(self):
        import time
        import getopt
        import datetime
        import sys

        limit = 10000
        startTime = None
        endTime = None
        word = None
        term = None
        action = 'get'

        opts, args = getopt.getopt(sys.argv[1:], '', ['action=', 'limit=', 'keyword=', 'term=', 'start-time=', 'end-time=', 'source=', 'help'])
        for theOpt in opts:
            if theOpt[0] == '--action':
                action = theOpt[1]
            elif theOpt[0] == '--limit':
                limit = int(theOpt[1])
            elif theOpt[0] == '--keyword':
                word = theOpt[1]
                word = word.decode('utf-8')
            elif theOpt[0] == '--term':
                term = theOpt[1]
            elif theOpt[0] == '--start-time':
                startTime = time.mktime(datetime.datetime.strptime(theOpt[1], '%Y-%m-%d %H:%M:%S').timetuple())
            elif theOpt[0] == '--end-time':
                endTime = time.mktime(datetime.datetime.strptime(theOpt[1], '%Y-%m-%d %H:%M:%S').timetuple())
            elif theOpt[0] == '--source':
                source = theOpt[1]
            elif theOpt[0] == '--help':
                self.showHelp()
                sys.exit(0)

        return action, word, term, startTime, endTime, limit

    def showHelp(self):
        import sys

        sys.stderr.write("--action='get'\n")
        sys.stderr.write("--limit=10000\n")
        sys.stderr.write("--keyword='歐巴馬'\n")
        sys.stderr.write("--term='選舉'\n")
        sys.stderr.write("--start-time='2014-06-01 00:00:00'\n")
        sys.stderr.write("--end-time='2014-07-31 23:59:59'\n")
        sys.stderr.write("--source=1\n")

    def getNews(self, word, startTime, endTime, limit):
        import sys
        import datetime

        self.mysqlCon()

        dataArr = []
        lastId = -1
        isOver = False
        tmpData = []
        total = 0
        handleDataCount = 0

        sys.stderr.write('Looping for 0/%d\n' % limit)
        while not isOver:
            try:
                sql = 'SELECT id, url, time, title, body FROM news LEFT JOIN news_info ON id=news_id WHERE last_fetch_at!=0'
                if lastId != -1:
                    sql = '%s and id < %d' % (sql, lastId)
                if startTime != None:
                    sql = '%s and time >= %s' % (sql, startTime)
                if endTime != None:
                    sql = '%s and time <= %s' % (sql, endTime)
                sql = '%s %s' % (sql, 'ORDER BY id DESC LIMIT 1000')

                self.cursor.execute(sql)
                tmpData =  self.cursor.fetchall()

                total += len(tmpData)

                if len(tmpData) == 0:
                    break;

                sys.stderr.write('Looping for %d/%d\n' % (total, limit))
            except MySQLdb.OperationalError, e:
                sys.stderr.write('Error: %s\n' % e)

                retryCount = 0
                while True:
                    try:
                        mysqlCon()
                        break
                    except MySQLdb.OperationalError, e:
                        sys.stderr.write('Error: %s\n' % e)
                        retryCount += 1
                        if retryCount == 3:
                            sys.exit(1)
                continue

            for record in tmpData:
                newsId = record[0]
                url = record[1]
                time = record[2]
                title = record[3]
                body = record[4]
                
                handleDataCount += 1;
                if (word == None) or (word != None and (word in title or word in body)):
                    newsDateTime = datetime.datetime.fromtimestamp(time).strftime('%Y-%m-%d')
                    dataDict = {'Published': newsDateTime, 'SubjectHtml': title, 'TextHtml': body}
                    dataArr.append(dataDict)
                    lastId = newsId
                if handleDataCount == limit:
                    isOver = True
                    break

        self.cursor.close()
        self.con.close()
        return dataArr

    def mysqlCon(self):
        import MySQLdb

        self.con = MySQLdb.connect(host='news-ckip.source.today', user='sma', passwd='sma', db='newsdiff', charset='utf8')
        self.cursor = self.con.cursor()

    def cutWord(self, articleArr):
        import jieba
        import itertools

        cutResultArr = []
        cutResult = {}
        for article in articleArr:
            seg_title_list = jieba.cut(article['SubjectHtml'], cut_all=False)
            seg_body_list = jieba.cut(article['TextHtml'], cut_all=False)
            for term in itertools.chain(seg_title_list, seg_body_list):
                if term in cutResult:
                    cutResult[term] += 1
                else:
                    cutResult.update({term: 1})
            cutResultArr.append({'Published': article['Published'], 'WordCutResult': cutResult})
        return cutResultArr

    def saveKeywordArticle(self, cutResultArr, word):
        import redis

        r = redis.Redis(host='localhost', port=6379, charset='utf-8')
        for cutResult in cutResultArr:
            for theWord in cutResult['WordCutResult']:
                r.zadd('CKIP:TERMS:%s:%s' % (word, cutResult['Published']), theWord, cutResult['WordCutResult'][theWord])

    def delWord(self, word, term, startTime, endTime):
        import redis
        import datetime
        import time

        r = redis.Redis(host='localhost', port=6379, charset='utf-8')
        theTime = startTime
        while theTime <= endTime:
            strTime = datetime.datetime.fromtimestamp(int(theTime)).strftime('%Y-%m-%d')
            r.zrem('CKIP:TERMS:%s:%s' % (word, strTime), term)
            structTime = datetime.datetime.strptime(strTime, '%Y-%m-%d')
            structTime = structTime + datetime.timedelta(days=1)
            theTime = time.mktime(structTime.timetuple())

if __name__ == '__main__':
    kw = Keyword()
    action, word, term, startTime, endTime, limit = kw.getOptions()
    if action == 'get':
        dataArr = kw.getNews(word, startTime, endTime, limit)
        cutResultArr = kw.cutWord(dataArr)
        kw.saveKeywordArticle(cutResultArr, word)
    elif action == 'delete':
        kw.delWord(word, term, startTime, endTime)

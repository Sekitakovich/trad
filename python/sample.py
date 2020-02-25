import psycopg2
import psycopg2.extras
from typing import Dict, List


class Session(object):

    def __init__(self):

        self.standby: bool = False
        try:
            self.pgstring: str = 'host=localhost port=5432 dbname=trad user=postgres password=postgres'
            self.handle = psycopg2.connect(self.pgstring)
            self.cursor = self.handle.cursor(cursor_factory=psycopg2.extras.DictCursor)
        except psycopg2.Error as e:
            print(e)
        else:
            self.standby = True

            self.shopList: List[int] = [654, 53, 524, 54, 655, 94, 183, 55, 56, 589, 10, 346, 428, 640, 83, 237, 236, 682, 77, 156, 131, 76]

    def findvoid(self) -> dict:

        query: str = "select min(id),count(*) from daily where vf=false and id>0"
        result = self.exec(query=query)
        value = {
            'count': result[0]['count'],
            'id': result[0]['min'],
        }
        return value

    def renumber(self):

        top: str = '2018-03-01'
        end: str = '2019-09-30'
        # ooo = ','.join(map(str, self.shopList))

        next: int = 1

        for shop in self.shopList:

            query: str = "select id from daily where vf=true and shop=%d and yyyymmdd between '%s' and '%s' order by yyyymmdd" % (shop, top, end)
            # print(query)
            result = self.exec(query=query)
            for row in result:
                k = row.keys()
                for name in k:
                    last = row[name]
                    query = "update daily set id=%d where id=%d" % (next, last)
                    print(query)
                    # self.exec(query=query)
                    next += 1

    def exec(self, *, query: str) -> List[any]:
        result: List[any] = []
        self.cursor.execute(query)
        for row in self.cursor:
            result.append(row)

        return result

    def __del__(self):
        pass


if __name__ == '__main__':

    session = Session()
    if session.standby:

        session.renumber()

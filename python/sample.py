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

            self.shopList: List[int] = [19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 40, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60]

    def member(self) -> List[any]:

        ooo = ','.join(map(str, self.shopList))
        query: str = "select id,name from shop where vf=true and id in (%s) order by id desc" % ooo
        # print(query)
        result = self.exec(query=query)
        return result

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

        ooo = session.member()
        print(ooo)
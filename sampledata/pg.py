import psycopg2


if __name__ == '__main__':

    with psycopg2.connect('host=localhost port=5432 dbname=next user=postgres password=postgres') as handle:
        cursor = handle.cursor()
        cursor.execute("select id,dtp,name from shop where vf=true  order by id asc")
        result = cursor.fetchall()
        for shop in result:
            print(shop)

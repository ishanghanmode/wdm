import json
import psycopg2
from psycopg2.extras import RealDictCursor
conn = psycopg2.connect(dbname="web", user="postgres", password="1234")
cur = conn.cursor(cursor_factory=RealDictCursor)
cur.execute("""SELECT actors.idactors, actors.fname, actors.lname FROM actors""")
data = json.dumps(cur.fetchall(), indent=2)
d = json.loads(data)
out_file = open("movie.json","w")
num = 0
for row in d:
    cur.execute("""SELECT acted_in.idmovies FROM acted_in where acted_in.idactors = %d """ % row['idactors'])
    acted = cur.fetchall()
    list_actors = {}
    for act in acted:
        temp = []
        cur.execute("""SELECT movies.title, movies.year FROM movies where movies.type = 3 AND movies.idmovies = %d """ % act['idmovies'])
        temp = cur.fetchall()
        for k in temp:
            row.setdefault('movies', []).append(k)
    jsobj = json.dumps(row)
    out_file.write(jsobj + '\n')
    print(num)
    num = num + 1
out_file.close()


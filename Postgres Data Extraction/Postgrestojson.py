import json
import psycopg2
from psycopg2.extras import RealDictCursor
conn = psycopg2.connect("dbname='WDM' user='postgres' port='5433' host='localhost' password='pacell-8990'")
cur = conn.cursor(cursor_factory=RealDictCursor)
cur.execute("""SELECT movies.idmovies, movies.title, movies.year, movies.type FROM movies LIMIT 10000 OFFSET 20000""")
data = json.dumps(cur.fetchall(), indent=2)
d = json.loads(data)
out_file = open("SERIES.json","w")
templist = []
for row in d:

     cur.execute("""SELECT series.name FROM series where series.idmovies = %d """ %row['idmovies'])
     row['series_name'] = cur.fetchall()
     cur.execute("""SELECT movies_genres.idgenres FROM movies_genres where movies_genres.idmovies = %d """ %row['idmovies'])
     genresid = cur.fetchall()
     for gid in genresid:
         genrelist= []
         cur.execute("""SELECT genres.genre FROM genres where genres.idgenres = %d""" %gid['idgenres'])
         genrelist = cur.fetchall()
         for g in genrelist:
            row.setdefault('genres', []).append(g['genre'])

     cur.execute("""SELECT movies_keywords.idkeywords FROM movies_keywords where movies_keywords.idmovies = %d """ % row['idmovies'])
     keyid = cur.fetchall()
     for keys in keyid:
         keylist = []
         cur.execute("""SELECT keywords.keyword FROM keywords where keywords.idkeywords = %d""" % keys['idkeywords'])
         keylist = cur.fetchall()
         for k in keylist:
            row.setdefault('keyword',[]).append(k['keyword'])
     cur.execute("""SELECT acted_in.billing_position, acted_in.idactors, acted_in.character FROM acted_in where acted_in.idmovies = %d""" % row['idmovies'])
     acted = cur.fetchall()
     list_actors = {}
     for act in acted:
         cur.execute("""SELECT actors.fname, actors.lname, actors.gender FROM actors where actors.idactors = %d """ %act['idactors'])
         # for k in cur.fetchall():
         temp = cur.fetchall()
         temp[0]['idactors'] = act['idactors']
         temp[0]['billing_position'] = act['billing_position']
         temp[0]['character'] = act['character']
         row.setdefault('actors', []).append(temp[0])

     jsobj = json.dumps(row)
     out_file.write(jsobj+'\n')
out_file.close()



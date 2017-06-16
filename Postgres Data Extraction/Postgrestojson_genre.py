import json
import psycopg2
from psycopg2.extras import RealDictCursor
conn = psycopg2.connect("dbname='WDM' user='postgres' port='5433' host='localhost' password='pacell-8990'")
cur = conn.cursor(cursor_factory=RealDictCursor)
Genre = 1
Movie = 1
cur.execute("""SELECT genres.genre, genres.idgenres FROM genres """)

data = json.dumps(cur.fetchall(), indent=2)
d = json.loads(data)
out_file = open("Genre_with_actors.json","w")
templist = []
for row in d:

     cur.execute("""SELECT movies_genres.idmovies FROM movies_genres where movies_genres.idgenres = %d """ %row['idgenres'])
     moviesid = cur.fetchall()
     for gid in moviesid:
         movieslist= []
         cur.execute("""SELECT movies.title, movies.year, movies.idmovies FROM movies where movies.idmovies = %d""" %gid['idmovies'])
         movieslist = cur.fetchall()
         row.setdefault('movies', []).append(movieslist[0])
         cur.execute("""SELECT acted_in.idactors FROM acted_in  where acted_in.idmovies = %d""" % movieslist[0]['idmovies'])
         acted = cur.fetchall()
         for act in acted:
            cur.execute("""SELECT actors.fname, actors.lname FROM actors where actors.idactors = %d """ %act['idactors'])
            temp = cur.fetchall()
            if temp[0]['fname'] is None:
                name = temp[0]['lname']
            if temp[0]['lname'] is None:
                name = temp[0]['fname']
            else:
                name = temp[0]['fname']+ " "+ temp[0]['lname']           
            movieslist[0].setdefault('actors', []).append(name)
         print(Genre)
         print(Movie)
         Movie=Movie +1

     jsobj = json.dumps(row)
     out_file.write(jsobj+'\n')
     Genre = 1+1
out_file.close()





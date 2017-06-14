import pymongo
import psycopg2
import json
from psycopg2.extras import RealDictCursor

def get_data(table):
	print(table)
	cur.execute("""SELECT * from """+table)
	data = json.dumps(cur.fetchall(), indent=2)
	d = json.loads(data)
	with open(table+'_data.json', 'w') as outfile:
		json.dump(d, outfile)
	print(table+" Done")
	'''
	cur.execute("""SELECT * from table""")
	data = json.dumps(cur.fetchall(), indent=2)
	d = json.loads(data)
	with open('data.json', 'w') as outfile:
		json.dump(d, outfile)
	'''
try:
	conn = psycopg2.connect("dbname='WDM' user='postgres' port='5433' host='localhost' password='password'")
except:
	print("I am unable to connect to the database")

cur = conn.cursor(cursor_factory=RealDictCursor)

tables = ['aka_names','aka_titles','keywords','movies_genres','movies_keywords','actors','acted_in']
#tables = ['series','genres','movies']
for table in tables:
	get_data(table)

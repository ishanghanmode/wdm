<!DOCTYPE html>
<html>
	<head>
		<title>PostgreSQL Movies Database</title>
	</head>
	<body>
		<?php
   			$error = false;
			//Get http method
			$method = $_SERVER['REQUEST_METHOD'];
			//Get url
			$url = explode("/", $_SERVER['REQUEST_URI']);
			if(count($url) == 4)
			{
				$object = $url[2];
				$query = $url[3];
			}
			else if(count($url) == 5)
			{
				$object = $url[2];
				$query = $url[3];
				$year = $url[4];	
			}
			else
			{
				//If there is no query or movies/actors selected in the url
				$error = true;
			}

			//Perform right function with each http method and url
			if(!$error)
			{
				switch ($method) 
				{
					case 'GET':
				    	if(count($url) == 5)
				    	{
				    		retrieveData($object, $query, $year);
				    	}
				    	else
				    	{
				    		retrieveData($object, $query, false);
				    	}
				    	break;
				  	case 'PUT':
				    	 break;
				  	case 'POST':
				    	 break;
				  	case 'DELETE':
				    	 break;
				}
			}

			//Function that retrieves data from postgresql database and returns json file
			function retrieveData($object, $query, $year)
			{
				//DB settings
				$host = "127.0.0.1";
			   	$port = "5432";
			   	$dbname = "IMDB";
			   	$username = "postgres";
			   	$password = "admin";

				//Make connection to db
				$db = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;", $username, $password); 
			   	if(!$db) {
			      	echo "Error : Unable to open database\n";
			   	} else {
			      	//echo "Opened database successfully\n";
			   	}
			   	
			   	//Search for movies
			   	if($object == "movies")
			   	{
			   		//Get a movie by id
			   		if(is_numeric($query))
			   		{
			   			//Get all movie information, except for actors
			   			$stmt = $db->query('SELECT movies.idmovies, title, year, array_agg(DISTINCT name) as "series name", array_agg(DISTINCT genre) as "genre labels", array_agg(DISTINCT keyword) as "keywords" FROM movies LEFT JOIN series on movies.idmovies = series.idmovies LEFT JOIN movies_genres on movies.idmovies = movies_genres.idmovies LEFT JOIN genres on movies_genres.idgenres = genres.idgenres LEFT JOIN movies_keywords on movies.idmovies = movies_keywords.idmovies LEFT JOIN keywords on movies_keywords.idkeywords = keywords.idkeywords WHERE type = 3 AND movies.idmovies = '.$query.' GROUP BY movies.idmovies');
			   			//Get all actors information of the movie
			   			$stmt2 = $db->query('SELECT fname, lname, gender, character, billing_position FROM actors LEFT JOIN acted_in on acted_in.idactors = actors.idactors WHERE idmovies = '.$query.' ORDER BY billing_position');
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			$actors = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			   			//Add actors information to the movie information arrray
			   			$results[0]['actors'] = $actors;
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get a movie by title or multiple movies by searchquery for title
			   		else if(!$year)
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$query = "'%".$query."%'";
			   			//Get all movies information
			   			$stmt = $db->query('SELECT movies.idmovies, title, year, array_agg(DISTINCT name) as "series name", array_agg(DISTINCT genre) as "genre labels", array_agg(DISTINCT keyword) as "keywords" FROM movies LEFT JOIN series on movies.idmovies = series.idmovies LEFT JOIN movies_genres on movies.idmovies = movies_genres.idmovies LEFT JOIN genres on movies_genres.idgenres = genres.idgenres LEFT JOIN movies_keywords on movies.idmovies = movies_keywords.idmovies LEFT JOIN keywords on movies_keywords.idkeywords = keywords.idkeywords WHERE type = 3 AND title ILIKE'.$query.' GROUP BY movies.idmovies');
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			//For each movie find the actors information and add that to the array
			   			for($i=0; $i<count($results); $i++)
			   			{
			   				$idmovie = $results[$i]['idmovies'];
			   				$stmt2 = $db->query('SELECT fname, lname, gender, character, billing_position FROM actors LEFT JOIN acted_in on acted_in.idactors = actors.idactors WHERE idmovies = '.$idmovie.' ORDER BY billing_position');
			   				$actors = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			   				$results[$i]['actors'] = $actors; 
			   			}
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get a movie by title or multiple movies by searchquery for title and a given year
			   		else
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$query = "'%".$query."%'";
			   			//Get all movies information
			   			$stmt = $db->query('SELECT movies.idmovies, title, year, array_agg(DISTINCT name) as "series name", array_agg(DISTINCT genre) as "genre labels", array_agg(DISTINCT keyword) as "keywords" FROM movies LEFT JOIN series on movies.idmovies = series.idmovies LEFT JOIN movies_genres on movies.idmovies = movies_genres.idmovies LEFT JOIN genres on movies_genres.idgenres = genres.idgenres LEFT JOIN movies_keywords on movies.idmovies = movies_keywords.idmovies LEFT JOIN keywords on movies_keywords.idkeywords = keywords.idkeywords WHERE type = 3 AND year = '.$year.'AND title ILIKE'.$query.' GROUP BY movies.idmovies');
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			//For each movie find the actors information and add that to the array
			   			for($i=0; $i<count($results); $i++)
			   			{
			   				$idmovie = $results[$i]['idmovies'];
			   				$stmt2 = $db->query('SELECT fname, lname, gender, character, billing_position FROM actors LEFT JOIN acted_in on acted_in.idactors = actors.idactors WHERE idmovies = '.$idmovie.' ORDER BY billing_position');
			   				$actors = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			   				$results[$i]['actors'] = $actors; 
			   			}
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   	}
			   	//Search for actors
			   	else if($object == "actors")
			   	{
			   		//Get a actor by id, returns first name, last name, gender, movies title, movies year
			   		if(is_numeric($query))
			   		{
			   			//Get the actor information
			   			$stmt = $db->query('SELECT fname, lname, gender FROM actors WHERE actors.idactors = '.$query);
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						//Get the movies information that the actor played in
						$stmt2 = $db->query('SELECT DISTINCT title, year FROM acted_in, movies WHERE idactors = '.$query.'AND type = 3 AND movies.idmovies = acted_in.idmovies ORDER BY year');
			   			$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			   			//Add the movies information to the results array
			   			$results[0]['movies'] = $results2; 

			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get a actor by title or multiple actors by searchquery for title, returns first name, last name, gender, movies title, movies year
			   		else
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$splitquery = explode(" ", $query);
			   			//If two names are given, use the first one as fname and the last one as lname
			   			if(count($splitquery) == 2)
			   			{
							$stmt = $db->query("SELECT fname, lname, gender, idactors FROM actors WHERE (lname ILIKE '%".$splitquery[1]."%' AND fname ILIKE '%".$splitquery[0]."%')");
			   			}
			   			//If less or more then 2 names are given, use them for both fname and lname
			   			else
			   			{
			   				$stmt = $db->query("SELECT fname, lname, gender, idactors FROM actors WHERE (lname ILIKE '%".$query."%' OR fname ILIKE '%".$query."%')");	
			   			}
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			//For each actor find the movies information and add that to the array
			   			for($i=0; $i<count($results); $i++)
			   			{
			   				$idactor = $results[$i]['idactors'];
			   				$stmt2 = $db->query('SELECT DISTINCT title, year FROM acted_in, movies WHERE idactors = '.$idactor.'AND type = 3 AND movies.idmovies = acted_in.idmovies ORDER BY year');
			   				$movies = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			   				$results[$i]['movies'] = $movies;
			   				//Remove idactors from result
			   				unset($results[$i]['idactors']); 
			   			}
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   	}
			   	//Get short statistics for actors
			   	else if($object == "actorstatistics")
			   	{
			   		//Get number of movies played for a actor by id, returns first name, last name, number of movies played
			   		if(is_numeric($query))
			   		{
			   			$stmt = $db->query('SELECT fname, lname, COUNT(DISTINCT acted_in.idmovies) as "number of movies" FROM actors, acted_in, movies WHERE actors.idactors = acted_in.idactors AND actors.idactors = '.$query.' AND acted_in.idmovies = movies.idmovies AND type = 3 GROUP BY fname, lname');
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get number of movies played for actors, returns first name, last name, number of movies played
			   		else
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$columnname = '"number of movies"';
			   			$splitquery = explode(" ", $query);
			   			//If two names are given, use the first one as fname and the last one as lname
			   			if(count($splitquery) == 2)
			   			{
			   				$stmt = $db->query("SELECT fname, lname, COUNT(DISTINCT acted_in.idmovies) as ".$columnname." FROM actors, acted_in, movies WHERE actors.idactors = acted_in.idactors AND (lname ILIKE '%".$splitquery[1]."%' AND fname ILIKE '%".$splitquery[0]."%') AND acted_in.idmovies = movies.idmovies AND type = 3 GROUP BY fname, lname");
			   			}
			   			//If less or more then 2 names are given, use them for both fname and lname
			   			else
			   			{
			   				$stmt = $db->query("SELECT fname, lname, COUNT(DISTINCT acted_in.idmovies) as ".$columnname." FROM actors, acted_in, movies WHERE actors.idactors = acted_in.idactors AND (lname ILIKE '%".$query."%' OR fname ILIKE '%".$query."%') AND acted_in.idmovies = movies.idmovies AND type = 3 GROUP BY fname, lname");
			   			}
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   	}
			   	//Get movies by genre and year
			   	else if($object == "genre")
			   	{
			   		//Makes genre start with a capital
			   		$query = ucfirst($query);
			   		//Get all movies with actors given a genre and a begin and end year
			   		if (strpos($year, "-")) 
			   		{
			   			$yeararray = explode("-", $year);
			   			$beginyear = $yeararray[0]; 
			   			$endyear = $yeararray[1];
			   			$stmt = $db->query("SELECT movies.idmovies, title, year FROM movies, genres, movies_genres WHERE genres.idgenres = movies_genres.idgenres AND movies_genres.idmovies = movies.idmovies AND type = 3 AND genre = '".$query."' AND year >= ".$beginyear." AND year <= ".$endyear." ORDER BY year, title");
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get all movies with actors given a genre and a year
			   		else
			   		{
			   			$stmt = $db->query("SELECT movies.idmovies, title, year FROM movies, genres, movies_genres WHERE genres.idgenres = movies_genres.idgenres AND movies_genres.idmovies = movies.idmovies AND type = 3 AND genre = '".$query."' AND year = ".$year." ORDER BY year, title");
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   	}
			   	//Get genre statistics
			   	else if($object == "genrestatistics")
			   	{
			   		$columnname = '"number of movies"';
			   		//Get all movies with actors given a genre and a begin and end year
			   		if (strpos($query, "-")) 
			   		{
			   			$yeararray = explode("-", $query);
			   			$beginyear = $yeararray[0]; 
			   			$endyear = $yeararray[1];
			   			$stmt = $db->query("SELECT genre, COUNT(movies_genres.idmovies) as ".$columnname." FROM genres, movies_genres, movies WHERE genres.idgenres = movies_genres.idgenres AND movies_genres.idmovies = movies.idmovies AND type = 3 AND year >= ".$beginyear." AND year <= ".$endyear." GROUP BY genre");
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get all movies with actors given a genre and a year
			   		else
			   		{
			   			$stmt = $db->query("SELECT genre, COUNT(movies_genres.idmovies) as ".$columnname." FROM genres, movies_genres, movies WHERE genres.idgenres = movies_genres.idgenres AND movies_genres.idmovies = movies.idmovies AND type = 3 AND year = ".$query." GROUP BY genre");
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   	}
			}
			
		?>
	</body>
</html>
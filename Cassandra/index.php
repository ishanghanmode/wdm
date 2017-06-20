<!DOCTYPE html>
<html>
	<head>
		<title>Cassandra Movies Database</title>
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
				// Connect to Cassandra db
				$cluster  = Cassandra::cluster()
				                ->build();
				$keyspace  = 'imdb';
				$session  = $cluster->connect($keyspace);

				if(!$session) {
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
			   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT fname, lname, gender from sc2_table where idactors = ".$query." allow filtering"));
			   			$results = [];
			   			$i = 0;
			   			foreach($result as $row)
			   			{
			   				$results[0]['fname'] = $row['fname'];
			   				$results[0]['lname'] = $row['lname'];
			   				$results[0]['gender'] = $row['gender'];
			   				break;
			   			}
			   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT title, year from sc2_table where idactors = ".$query." allow filtering"));
			   			foreach($result as $row)
			   			{
			   				$results[0]['movies'][$i]['title'] = $row['title'];
			   				$results[0]['movies'][$i]['year'] = $row['year'];
			   				$i++;
			   			}

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
			   		$columnname = '"number of movies"';
			   		//Get number of movies played for a actor by id, returns first name, last name, number of movies played
			   		if(is_numeric($query))
			   		{
			   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT fname, lname, ".$columnname." from sc3_table where idactors = ".$query." allow filtering"));
			   			$results = [];
			   			$i = 0;
			   			foreach($result as $row)
			   			{
			   				$results[$i]['fname'] = $row['fname'];
			   				$results[$i]['lname'] = $row['lname'];
			   				$results[$i]['number of movies'] = $row['number of movies'];
			   				$i++;
			   			}

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
			   				//$result = $session->execute(new Cassandra\SimpleStatement("SELECT fname, lname, ".$columnname." from sc3_table where fname = '".$query."' allow filtering"));
			   				$result2 = $session->execute(new Cassandra\SimpleStatement("SELECT fname, lname, ".$columnname." from sc3_table where lname = '".$query."' allow filtering"));
				   			$results = [];
				   			$i = 0;
				   			/*foreach($result as $row)
				   			{
				   				$results[$i]['fname'] = $row['fname'];
				   				$results[$i]['lname'] = $row['lname'];
				   				$results[$i]['number of movies'] = $row['number of movies'];
				   				//echo $row['number of movies'];
				   				$i++;
				   			}*/
				   			foreach($result2 as $row)
				   			{
				   				$results[$i]['fname'] = $row['fname'];
				   				$results[$i]['lname'] = $row['lname'];
				   				$results[$i]['number of movies'] = $row['number of movies'];
				   				//echo $row['number of movies'];
				   				$i++;
				   			}
			   			}
			   			
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
			   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT idmovies, title, year FROM sc4_table WHERE year >= ".$beginyear." AND year <= ".$endyear." AND genre = '".$query."' allow filtering"));
			   			$results = [];
			   			$i = 0;
			   			foreach($result as $row)
			   			{
			   				$results[$i]['idmovies'] = $row['idmovies'];
			   				$results[$i]['title'] = $row['title'];
			   				$results[$i]['year'] = $row['year'];
			   				$i++;
			   			}
			   						   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get all movies with actors given a genre and a year
			   		else
			   		{
			   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT idmovies, title, year FROM sc4_table WHERE year = ".$year." AND genre = '".$query."' allow filtering"));
			   			$results = [];
			   			$i = 0;
			   			foreach($result as $row)
			   			{
			   				$results[$i]['idmovies'] = $row['idmovies'];
			   				$results[$i]['title'] = $row['title'];
			   				$results[$i]['year'] = $row['year'];
			   				$i++;
			   			}
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   	}
			   	//Get genre statistics
			   	else if($object == "genrestatistics")
			   	{
			   		$columnname = '"number of movies"';
			   		//Get all movies with actors given a genre and a year
			   		$result = $session->execute(new Cassandra\SimpleStatement("SELECT genre, ".$columnname." from sc5_table where year = ".$query." allow filtering"));
		   			$results = [];
		   			$i = 0;
		   			foreach($result as $row)
		   			{
		   				$results[$i]['genre'] = $row['genre'];
		   				$results[$i]['number of movies'] = $row['number of movies'];
		   				$i++;
		   			}
		   						   			
		   			//Print json format of the data in a nice way on the webpage
		   			header('Content-Type: application/json');
		   			echo json_encode($results, JSON_PRETTY_PRINT);
			   	}
			}
		?>
	</body>
</html>
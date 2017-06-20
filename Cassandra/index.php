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
			   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT idmovies, title, year, keywords from sc1_a_table where idmovies = ".$query." allow filtering"));
			   			$result2 = $session->execute(new Cassandra\SimpleStatement("SELECT idmovies, title, year, genres from sc1_b_table where idmovies = ".$query." allow filtering"));
			   			$results = [];
			   			//$i = 0;
			   			foreach($result as $row)
			   			{
			   				$results[0]['idmovies'] = $row['idmovies'];
			   				$results[0]['title'] = $row['title'];
			   				$results[0]['year'] = $row['year'];
			   				$results[0]['keywords'] = $row['keywords'];
			   				break;
			   			}
			   			foreach($result2 as $row)
			   			{
			   				$results[0]['genres'] = $row['genres'];
			   				break;
			   			}
			   						   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get a movie by title or multiple movies by searchquery for title
			   		else if(!$year)
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT idmovies, title, year, keywords FROM sc1_a_table WHERE title = '".$query."' allow filtering"));
			   			$result2 = $session->execute(new Cassandra\SimpleStatement("SELECT idmovies, title, year, genres FroM sc1_b_table WHERE title = '".$query."' allow filtering"));
			   			$results = [];
			   			//$i = 0;
			   			foreach($result as $row)
			   			{
			   				$results[0]['idmovies'] = $row['idmovies'];
			   				$results[0]['title'] = $row['title'];
			   				$results[0]['year'] = $row['year'];
			   				$results[0]['keywords'] = $row['keywords'];
			   				break;
			   			}
			   			foreach($result2 as $row)
			   			{
			   				$results[0]['genres'] = $row['genres'];
			   				break;
			   			}
			   						   			
			   			//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			echo json_encode($results, JSON_PRETTY_PRINT);
			   		}
			   		//Get a movie by title or multiple movies by searchquery for title and a given year
			   		else
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT idmovies, title, year, keywords FROM sc1_a_table WHERe title = '".$query."' AND year = ".$year." allow filtering"));
			   			$result2 = $session->execute(new Cassandra\SimpleStatement("SELECT idmovies, title, year, genres FROM sc1_b_table WHERE title = '".$query."' AND year = ".$year." allow filtering"));
			   			$results = [];
			   			//$i = 0;
			   			foreach($result as $row)
			   			{
			   				$results[0]['idmovies'] = $row['idmovies'];
			   				$results[0]['title'] = $row['title'];
			   				$results[0]['year'] = $row['year'];
			   				$results[0]['keywords'] = $row['keywords'];
			   				break;
			   			}
			   			foreach($result2 as $row)
			   			{
			   				$results[0]['genres'] = $row['genres'];
			   				break;
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
							$result = $session->execute(new Cassandra\SimpleStatement("SELECT fname, lname, gender from sc2_table where lname = '".$splitquery[1]."' AND fname = '".$splitquery[0]."' allow filtering"));
				   			$results = [];
				   			$i = 0;
				   			foreach($result as $row)
				   			{
				   				$results[0]['fname'] = $row['fname'];
				   				$results[0]['lname'] = $row['lname'];
				   				$results[0]['gender'] = $row['gender'];
				   				break;
				   			}
				   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT title, year from sc2_table where lname = '".$splitquery[1]."' AND fname = '".$splitquery[0]."' allow filtering"));
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
			   			//If less or more then 2 names are given, use them for both fname and lname
			   			else
			   			{
			   				$result = $session->execute(new Cassandra\SimpleStatement("SELECT fname, lname, gender from sc2_table where lname = '".$query."' allow filtering"));
				   			$results = [];
				   			$i = 0;
				   			foreach($result as $row)
				   			{
				   				$results[0]['fname'] = $row['fname'];
				   				$results[0]['lname'] = $row['lname'];
				   				$results[0]['gender'] = $row['gender'];
				   				break;
				   			}
				   			$result = $session->execute(new Cassandra\SimpleStatement("SELECT title, year from sc2_table where lanme = '".$query."' allow filtering"));
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
			   			/*if(count($splitquery) == 2)
			   			{
			   				$stmt = $db->query("SELECT fname, lname, COUNT(DISTINCT acted_in.idmovies) as ".$columnname." FROM actors, acted_in, movies WHERE actors.idactors = acted_in.idactors AND (lname ILIKE '%".$splitquery[1]."%' AND fname ILIKE '%".$splitquery[0]."%') AND acted_in.idmovies = movies.idmovies AND type = 3 GROUP BY fname, lname");
			   			}
			   			//If less or more then 2 names are given, use them for both fname and lname
			   			else
			   			{*/
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
			   			//}
			   			
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
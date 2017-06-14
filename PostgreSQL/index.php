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
				    	retrieveData($object, $query);
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
			function retrieveData($object, $query)
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
			   			$stmt = $db->query('SELECT * FROM '.$object.' WHERE idmovies = '.$query);
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			echo json_encode($results);
			   		}
			   		//Get a movie by title or multiple movies by searchquery for title
			   		else
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$stmt = $db->query("SELECT * FROM ".$object." WHERE type = 3 AND title ILIKE '%".$query."%'");
			   			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			   			echo json_encode($results);
			   		}
			   	}
			}
			
		?>
	</body>
</html>
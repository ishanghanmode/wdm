<!DOCTYPE html>
<html>
	<head>
		<title>MongoDB Movies Database</title>
	</head>
	<body>
		<?php
   			// This path should point to Composer's autoloader
			require 'vendor/autoload.php';
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
	   			

				//Connect to MongoDB
			   	$client = new MongoDB\Client("mongodb://localhost:27017");
				
			   	if($client)
			   	{
			   		//echo "Connection to database successfully";
			   	}
			   	
			   	$db = $client->web;
			   	
			   	//Search for movies
			   	if($object == "movies")
			   	{
			   		$collection = $db->movie;
			   		//Get a movie by id
			   		if(is_numeric($query))
			   		{
			   			//$result = $collection->find(array('idmovies' => 1));
					   	$result = $collection->find(array('idmovies'=>intval($query),'type'=>3));
					   	//echo $result;

						
						header('Content-Type: application/json');
						foreach ($result as $id => $value) {  
		 					echo json_encode($value, JSON_PRETTY_PRINT);  
						}
			   		}
			   		//Get a movie by title or multiple movies by searchquery for title
			   		else if(!$year)
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$where = array("title" => new MongoDB\BSON\Regex($query),'year'=>array('$exists'=>true,'$gte'=>1935,'$lte'=>1985));  
						$result = $collection->find($where);
						//Get all movies information
			   			
			   			header('Content-Type: application/json');
			   			foreach ($result as $id => $value) {  
		 					echo json_encode($value, JSON_PRETTY_PRINT);  
						}
			   			
			   		}
			   		//Get a movie by title or multiple movies by searchquery for title and a given year
			   		else
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$where = array("title" => new MongoDB\BSON\Regex($query),'year'=>array('$exists'=>true,'$gte'=>intval($year),'$lte'=>intval($year)));  
						$result = $collection->find($where);
						//Get all movies information
			   			
			   			header('Content-Type: application/json');
			   			foreach ($result as $id => $value) {  
		 					echo json_encode($value, JSON_PRETTY_PRINT);  
						}
			   		}
			   	}
			   	//Search for actors
			   	else if($object == "actors")
			   	{
			   		$collection = $db->actor;
			   		//Get a actor by id, returns first name, last name, gender, movies title, movies year
			   		if(is_numeric($query))
			   		{
			   			$result = $collection->find(array("idactors"=>intval($query)));
			   			header('Content-Type: application/json');
			   			foreach ($result as $id => $value) {  
		 					echo json_encode($value, JSON_PRETTY_PRINT);  
						}
			   		}
			   		//Get a actor by title or multiple actors by searchquery for title, returns first name, last name, gender, movies title, movies year
			   		else
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$splitquery = explode(" ", $query);
			   			//If two names are given, use the first one as fname and the last one as lname
			   			if(count($splitquery) == 2)
			   			{
			   				$param = array('$and'=>array(array("lname" => new MongoDB\BSON\Regex($splitquery[1])),array("fname" => new MongoDB\BSON\Regex($splitquery[0]))));  
							$result = $collection->find($param);
							header('Content-Type: application/json');
				   			foreach ($result as $id => $value) {  
			 					echo json_encode($value, JSON_PRETTY_PRINT);  
							}	
			   			}
			   			//If less or more then 2 names are given, use them for both fname and lname
			   			else
			   			{
			   				$param = array('$or'=>array(array("lname" => new MongoDB\BSON\Regex($query)),array("fname" => new MongoDB\BSON\Regex($query))));  
							$result = $collection->find($param);
							header('Content-Type: application/json');
				   			foreach ($result as $id => $value) {  
			 					echo json_encode($value, JSON_PRETTY_PRINT);  
							}	
			   			}
			   		}
			   	}
			   	//Get short statistics for actors
			   	else if($object == "actorstatistics")
			   	{
			   		$collection = $db->actor;
			   		//Get number of movies played for a actor by id, returns first name, last name, number of movies played
			   		if(is_numeric($query))
			   		{
			   			$cond = array(  
						    array(  
						        '$match' => array("idactors"=>intval($query)),  
						    ),  
						    array(  
						        '$project' => array( 'count' =>array('$size'=>array('$movies'))
						          
						        )
						    ) 
						); 
						$result = $collection->aggregate($cond);
			   			header('Content-Type: application/json');
			   			foreach ($result as $id => $value) {  
		 					echo json_encode($value, JSON_PRETTY_PRINT);  
						}	
			   		}
			   		//Get number of movies played for actors, returns first name, last name, number of movies played
			   		else
			   		{
			   			$query = str_replace("%20", " ", $query);
			   			$splitquery = explode(" ", $query);
			   			//If two names are given, use the first one as fname and the last one as lname
			   			if(count($splitquery) == 2)
			   			{
			   				$cond = array(  
							    array(  
							        '$match' =>  array('$and'=>array(array("lname" => new MongoDB\BSON\Regex($splitquery[1])),array("fname" => new MongoDB\BSON\Regex($splitquery[0])))),  
							    ),  
							    array(  
							         '$project' => array( 'count' =>array('$size'=>array('ifNull'=>array('$movies',[])))
							          
							        )
							    ) 
							); 
							$result = $collection->aggregate($cond);
				   			header('Content-Type: application/json');
				   			foreach ($result as $id => $value) {  
			 					echo json_encode($value, JSON_PRETTY_PRINT);  
							}
			   			}
			   			//If less or more then 2 names are given, use them for both fname and lname
			   			else
			   			{
			   				$cond = array(  
							    array(  
							        '$match' =>  array('$or'=>array(array("lname" => new MongoDB\BSON\Regex($query)),array("fname" => new MongoDB\BSON\Regex($query)))),  
							    ),  
							    array(  
							          '$project' => array( 'count' =>array('$size'=>array('ifNull'=>array('$movies',false))))
							          
							        )
							     
							); 
							$result = $collection->aggregate($cond);
				   			header('Content-Type: application/json');
				   			foreach ($result as $id => $value) {  
			 					echo json_encode($value, JSON_PRETTY_PRINT);  
							}
			   			}
			   		}
			   	}
			   	//Get movies by genre and year
			   	else if($object == "genre")
			   	{
			   		$collection = $db->movie;
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
			   			$cond = array(  
						    array(  
						        '$match' => array("genre" => new MongoDB\BSON\Regex($query),'year'=>array('$exists'=>true,'$gte'=>intval($year),'$lte'=>intval($year)))  
						    ),  
						    array(  
						        '$project' => array("_id"=>0,"keyword" =>0,"actors" =>0,"series_name" =>0,"type" =>0,"genres" =>0)
						          
						        ),
						array('$sort'=>array("year"=>1,"title"=>1))
						    
						);                        
						$result = $collection->aggregate($cond);
						//Print json format of the data in a nice way on the webpage
			   			header('Content-Type: application/json');
			   			foreach ($result as $id => $value) {  
		 					echo json_encode($value, JSON_PRETTY_PRINT);  
						}
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
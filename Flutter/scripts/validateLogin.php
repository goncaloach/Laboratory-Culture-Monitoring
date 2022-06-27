<?php 
	ini_set("log_errors", 1); 
	//usar error_log("exemplo"); para fazer print na consola
	$db = "mylab"; //database name
	$dbhost = "localhost"; //database host

	$return["message"] = "";
	$return["success"] = false;

	$username = $_POST["username"];
	$password = $_POST["password"];

	try {
		$conn = mysqli_connect($dbhost, $username, $password, $db); 
		
		$query_select = "SELECT current_role()"; 
		//building SQL query
		$result_query_select = mysqli_query($conn, $query_select);

		$conn->next_result();
		$role = mysqli_fetch_assoc($result_query_select)['current_role()'];
		if($role == "Investigador") { // change role if necessary
			$return["success"] = true;
		} else {
			$return["message"] = "This user does not have permission to use the app.";
		}
		
		$result_query_select->close();
		mysqli_close($conn);

		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		// tell browser that its a json data 
		echo json_encode($return);
		//converting array to JSON string
		error_log(json_encode($return)); 
	} catch (Exception $e) {
		$return["message"] = "The login failed. Check if the user exists in the database.";
		
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		// tell browser that its a json data
		echo json_encode($return);
		//converting array to JSON string
	}

	
?>
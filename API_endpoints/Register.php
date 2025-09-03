<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Authorization');
	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    	http_response_code(200);
    	exit();
	}

	$inData = getRequestInfo();
	
	$id = 0;

	// TODO: Fill in your SQL credentials
	$conn = new mysqli("YOUR_HOST", "YOUR_USERNAME", "YOUR_PASSWORD", "YOUR_DATABASE"); 	
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		// Check if user already exists
		$stmt = $conn->prepare("SELECT ID FROM Users WHERE Login=?");
		$stmt->bind_param("s", $inData["login"]);
		$stmt->execute();
		$result = $stmt->get_result();

		if( $result->fetch_assoc() )
		{
			returnWithError("User already exists");
		}
		else
		{
			// Insert new user
			$stmt = $conn->prepare("INSERT INTO Users (firstName, lastName, Login, Password) VALUES (?, ?, ?, ?)");
			$stmt->bind_param("ssss", $inData["firstName"], $inData["lastName"], $inData["login"], $inData["password"]);
			
			if( $stmt->execute() )
			{
				$id = $conn->insert_id;
				returnWithInfo( $inData["firstName"], $inData["lastName"], $id );
			}
			else
			{
				returnWithError("Registration failed");
			}
		}

		$stmt->close();
		$conn->close();
	}
	
	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $firstName, $lastName, $id )
	{
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>
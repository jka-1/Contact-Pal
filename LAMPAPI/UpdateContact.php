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
	
	$contactId = $inData["contactId"];
	$firstName = $inData["firstName"];
	$lastName = $inData["lastName"];
	$phone = $inData["phone"];
	$email = $inData["email"];
	$userId = $inData["userId"];

	$conn = new mysqli("localhost", "TheBeast", "Team2", "CONTACTPALDB");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$stmt = $conn->prepare("UPDATE Contacts SET FirstName=?, LastName=?, Phone=?, Email=? WHERE ID=? AND UserId=?");
		$stmt->bind_param("ssssss", $firstName, $lastName, $phone, $email, $contactId, $userId);
		$stmt->execute();
		
		if ($stmt->affected_rows > 0) {
			returnWithError("");
		} else {
			returnWithError("Contact not found or you don't have permission to update it");
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
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
?>
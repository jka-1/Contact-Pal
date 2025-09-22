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
	$userId = $inData["userId"];

	$conn = new mysqli("localhost", "TheBeast", "Team2", "CONTACTPALDB");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		// First check if the contact exists and belongs to the user
		$checkStmt = $conn->prepare("SELECT ID FROM Contacts WHERE ID = ? AND UserId = ?");
		$checkStmt->bind_param("ss", $contactId, $userId);
		$checkStmt->execute();
		$result = $checkStmt->get_result();
		
		if ($result->num_rows == 0) {
			returnWithError("Contact not found or access denied");
		} else {
			// Delete the contact
			$stmt = $conn->prepare("DELETE FROM Contacts WHERE ID = ? AND UserId = ?");
			$stmt->bind_param("ss", $contactId, $userId);
			$stmt->execute();
			
			if ($stmt->affected_rows > 0) {
				returnWithSuccess("Contact deleted successfully");
			} else {
				returnWithError("Failed to delete contact");
			}
			
			$stmt->close();
		}
		
		$checkStmt->close();
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
	
	function returnWithSuccess( $message )
	{
		$retValue = '{"message":"' . $message . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>
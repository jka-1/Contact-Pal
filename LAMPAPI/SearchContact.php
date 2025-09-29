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
	
	$searchResults = "";
	$searchCount = 0;

	$conn = new mysqli("localhost", "TheBeast", "Team2", "CONTACTPALDB");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$search   = isset($inData['search']) ? $inData['search'] : '';
$userId   = isset($inData['userId']) ? (int)$inData['userId'] : 0;

if ($search === '') {
    // Empty search → return all contacts for this user
    $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone, Email, UserID AS userId FROM Contacts WHERE UserID = ?");
    $stmt->bind_param("i", $userId);
} else {
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone, Email, UserID AS userId 
                            FROM Contacts 
                            WHERE (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? OR Email LIKE ?) 
                            AND UserID = ?");
    $stmt->bind_param("ssssi", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $userId);
}


		$stmt->execute();
		
		$result = $stmt->get_result();
		
		while($row = $result->fetch_assoc())
		{
			if( $searchCount > 0 )
			{
				$searchResults .= ",";
			}
			$searchCount++;
			$searchResults .= '{"id":' . $row["ID"] .
                  ',"firstName":"' . $row["FirstName"] .
                  '","lastName":"' . $row["LastName"] .
                  '","phone":"' . $row["Phone"] .
                  '","email":"' . $row["Email"] .
                  '","userId":' . $row["userId"] . '}';
		}
		
		if( $searchCount == 0 )
		{
			returnWithError( "No Records Found" );
		}
		else
		{
			returnWithInfo( $searchResults );
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
	
	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>

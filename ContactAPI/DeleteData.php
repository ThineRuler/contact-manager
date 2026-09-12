<?php

	//Steffano Poggioli, COP4331C, 9/9/2026 Version 1.1
	//API for deleting from database, bug fix attempt 1

	$inData = getRequestInfo();
	
	$FirstName = $inData["FirstName"];
	$LastName = $inData["LastName"];
    $Phone = $inData["Phone"];
    $Email = $inData["Email"];
    $CreationDate = $inData["CreationDate"];
	
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		//Initializing detection of present data
		$stmt = $conn->prepare("DELETE FROM Contacts WHERE ((FirstName = $FirstName) AND (LastName = $LastName) AND (Phone = $Phone) AND (Email = $Email) AND (CreationDate = $CreationDate));
		$stmt->execute();

		$stmt->close();
		$conn->close();
		returnWithError("");
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
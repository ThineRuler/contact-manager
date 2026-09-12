<?php

	//Steffano Poggioli, COP4331C, 9/9/2026 Version 1.0
	//Initial attempt for API to edit existing data
	//DISCLAIMER: May cause data destruction, another sort of ID field may be needed for proper selection

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
		//SQL statement based on provided structure
		$stmt = $conn->prepare("UPDATE Contacts SET FirstName = $FirstName, LastName = $LastName, Phone = $Phone, Email = $Email, CreationDate = $CreationDate WHERE CreationDate != $CreationDate");
		$stmt->bind_param("ssssss", $FirstName, $LastName, $Phone, $Email, $CreationDate);
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
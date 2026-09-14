<?php

	//Steffano Poggioli, COP4331C, 9/14/2026 Version 1.1
	//API for editing existing contacts, update to incorporate ID variable for selection logic

	$inData = getRequestInfo();
	
	$ID = $inData["ID"];

	$FirstName = $inData["FirstName"];
	$LastName = $inData["LastName"];
    $Phone = $inData["Phone"];
    $Email = $inData["Email"];
    

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		//SQL statement based on provided structure
		$stmt = $conn->prepare("UPDATE Contacts SET FirstName = $FirstName, LastName = $LastName, Phone = $Phone, Email = $Email WHERE ID = $ID);
		$stmt->bind_param("ssss", $FirstName, $LastName, $Phone, $Email);
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
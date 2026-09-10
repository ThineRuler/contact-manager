<?php

	//Steffano Poggioli, COP4331C, 9/9/2026 Version 1.0
	//Initial Add API type derived from .php file provided in LAMP project template 
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
		$stmt = $conn->prepare("insert into Contacts (FirstName,LastName,Phone,Email,CreationDate) VALUES(?,?,?,?,?)");
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
<?php

	//Steffano Poggioli, COP4331C, 9/16/2026 Version 1.1
	//Initial Add API type derived from .php file provided in LAMP project template, update to add field for User ID
	$inData = getRequestInfo();

	$UserID = (int)($inData["UserID"] ?? 0);
	
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
		$stmt = $conn->prepare("insert into Contacts (FirstName,LastName,Phone,Email,CreationDate,UserID) VALUES(?,?,?,?,?,?)");
		$stmt->bind_param("sssssss", $FirstName, $LastName, $Phone, $Email, $CreationDate, $UserID);
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
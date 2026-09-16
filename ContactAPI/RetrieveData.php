<?php

	//Steffano Poggioli, COP4331C, 9/16/2026 Version 1.0
	//API for retrieving and sending contact data for a particular user upon login

	$inData = getRequestInfo();
	
    //UserID statement credited to Acsah's work
    $UserID = (int)($inData["UserID"] ?? 0);

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		//SQL statement based on provided structure
		$stmt = $conn->prepare("SELECT ID, FirstName, Lastname, Phone, Email, CreationDate FROM Contacts WHERE UserID = $UserID");
		$stmt->execute();

        sendResultInfoAsJson($stmt);

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
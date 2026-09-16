<?php

	//Steffano Poggioli, COP4331C, 9/16/2026 Version 1.2
	//API for deleting from database, logic improvement given ID variable field, fix to syntax

	$inData = getRequestInfo();

	$UserID = (int)($inData["UserID"] ?? 0);
	
	$FirstName = $inData["FirstName"];
	$LastName = $inData["LastName"];
    $Phone = $inData["Phone"];
    $Email = $inData["Email"];
    $CreationDate = $inData["CreationDate"];

	$rowID;
	
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		//Initializing detection of present data
		$stmt = $conn->prepare("SELECT ID FROM Contacts WHERE ((FirstName = $FirstName) AND (LastName = $LastName) AND (Phone = $Phone) AND (Email = $Email) AND (CreationDate = $CreationDate) AND (UserId = $UserID)");
		$stmt->execute();

		if (0 < $stmt->num_rows) {
  			while($firstRow = $stmt->fetch_assoc()) {

				$rowID = $firstRow["ID"];

				$deleteSTMT = $conn->prepare("DELETE FROM Contacts WHERE ID = $rowID");
				$deleteSTMT->execute();
				$deleteSTMT->close();
				break;

  			}
		} 

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
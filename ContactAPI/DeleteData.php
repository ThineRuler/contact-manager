<?php

	//Steffano Poggioli, COP4331C, 9/17/2026 Version 1.4
	//API for deleting from database, update to fix parameter binding using help from Acsah's code

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Content-Type");
	header("Access-Control-Allow-Methods: POST, OPTIONS");

	if (($_SERVER["REQUEST_METHOD"] ?? "") === "OPTIONS")
	{
		http_response_code(204);
		exit;
	}

	$inData = getRequestInfo();

	$UserID = (int)($inData["UserID"] ?? 0);
	$ID = (int)($inData["ID"] ?? 0);

	$FirstName = trim((string)($inData["FirstName"] ?? ""));
	$LastName = trim((string)($inData["LastName"] ?? ""));

	if ($UserID <= 0 || $FirstName === "" || $LastName === "")
	{
		returnWithError("UserID, FirstName, and LastName are required.");
		exit;
	}

	if ($CreationDate === "")
	{
		$CreationDate = date("Y-m-d H:i:s");
	}
	
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		//Initializing detection of present data
		$stmt = $conn->prepare("DELETE FROM Contacts WHERE ID = ? AND UserId = ?");
		$stmt->bind_param("ii", $ID, $UserID);

		if($stmt->execute()){

			$stmt->close();
			$conn->close();
			returnWithError("");
		}else{

			$err = $stmt->error;
			$stmt->close();
			$conn->close();
			returnWithError($err);
		}
	}

	function getRequestInfo()
	{
		$data = json_decode(file_get_contents("php://input"), true);
		return is_array($data) ? $data : [];
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		sendResultInfoAsJson(json_encode(["error" => $err]));
	}
	
?>
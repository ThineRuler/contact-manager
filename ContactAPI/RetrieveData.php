<?php

	//Steffano Poggioli, COP4331C, 9/17/2026 Version 1.2
	//API for retrieving and sending contact data for a particular user upon login, updated via help from Acsah's code

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
	$FirstName = trim((string)($inData["FirstName"] ?? ""));
	$LastName = trim((string)($inData["LastName"] ?? ""));

	if ($UserID <= 0 || $FirstName === "" || $LastName === "")
	{
		returnWithError("UserID, FirstName, and LastName are required.");
		exit;
	}

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		//SQL statement based on provided structure
		$stmt = $conn->prepare("SELECT ID, FirstName, Lastname, Phone, Email, CreationDate FROM Contacts WHERE UserID = ?");
		$stmt->bind_param("i", $UserID);

		if ($stmt->execute()){

			$AllData = array();

			if(0 < stmt->num_rows){
				while($CurrentRow = $stmt->fetch_assoc()){

					$AllData[] = $CurrentRow;

				}

				echo json_encode($AllData);
			}
			
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

	function sendResultInfoAsJson($obj)
	{
		header("Content-type: application/json");
		echo $obj;
	}

	function returnWithError($err)
	{
		sendResultInfoAsJson(json_encode(["error" => $err]));
	}

?>
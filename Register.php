<?php

	//Creates a new user account. Password is hashed with password_hash() before storage —

	$inData = getRequestInfo();

	$FirstName = trim($inData["FirstName"] ?? "");
	$LastName  = trim($inData["LastName"] ?? "");
	$Login     = trim($inData["Login"] ?? "");
	$Password  = $inData["Password"] ?? "";

	if ($FirstName === "" || $LastName === "" || $Login === "" || $Password === "")
	{
		returnWithError("FirstName, LastName, Login, and Password are all required.");
		exit;
	}

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error)
	{
		returnWithError($conn->connect_error);
		exit;
	}

	// Reject duplicate logins up front so we return a clean error instead of a DB constraint failure.
	$check = $conn->prepare("SELECT ID FROM Users WHERE Login = ?");
	$check->bind_param("s", $Login);
	$check->execute();
	$check->store_result();

	if ($check->num_rows > 0)
	{
		$check->close();
		$conn->close();
		returnWithError("That Login is already registered.");
		exit;
	}
	$check->close();

	$hashedPassword = password_hash($Password, PASSWORD_DEFAULT);

	$stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("ssss", $FirstName, $LastName, $Login, $hashedPassword);

	if ($stmt->execute())
	{
		$newUserId = $conn->insert_id;
		$stmt->close();
		$conn->close();

		$result = json_encode([
			"error"     => "",
			"id"        => $newUserId,
			"firstName" => $FirstName,
			"lastName"  => $LastName
		]);
		sendResultInfoAsJson($result);
	}
	else
	{
		$err = $stmt->error;
		$stmt->close();
		$conn->close();
		returnWithError($err);
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
		$retValue = json_encode(["error" => $err]);
		sendResultInfoAsJson( $retValue );
	}

?>

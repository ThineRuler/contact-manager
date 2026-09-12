<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Content-Type");
	header("Access-Control-Allow-Methods: POST, OPTIONS");

	if (($_SERVER["REQUEST_METHOD"] ?? "") === "OPTIONS")
	{
		http_response_code(204);
		exit;
	}

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
		sendResultInfoAsJson(json_encode([
			"error"     => "",
			"id"        => (int)$newUserId,
			"firstName" => $FirstName,
			"lastName"  => $LastName
		]));
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

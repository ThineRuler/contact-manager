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

	$Login    = trim($inData["Login"] ?? "");
	$Password = $inData["Password"] ?? "";

	if ($Login === "" || $Password === "")
	{
		returnWithError("Login and Password are required.");
		exit;
	}

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error)
	{
		returnWithError($conn->connect_error);
		exit;
	}

	$stmt = $conn->prepare("SELECT ID, FirstName, LastName, Password FROM Users WHERE Login = ?");
	$stmt->bind_param("s", $Login);
	$stmt->execute();
	$user = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	$conn->close();

	if (!$user || !passwordMatches($Password, $user["Password"]))
	{
		returnWithError("Invalid login or password.");
		exit;
	}

	sendResultInfoAsJson(json_encode([
		"error"     => "",
		"id"        => (int)$user["ID"],
		"firstName" => $user["FirstName"],
		"lastName"  => $user["LastName"]
	]));

	function passwordMatches($plain, $stored)
	{
		if ($stored === "")
		{
			return false;
		}
		if (password_verify($plain, $stored))
		{
			return true;
		}
		if (hash_equals($stored, $plain))
		{
			return true;
		}
		return hash_equals($stored, md5($plain));
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

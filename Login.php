<?php



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
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	$stmt->close();
	$conn->close();

	if (!$user || !password_verify($Password, $user["Password"]))
	{
		returnWithError("Invalid login or password.");
		exit;
	}

	$response = json_encode([
		"error"     => "",
		"id"        => $user["ID"],
		"firstName" => $user["FirstName"],
		"lastName"  => $user["LastName"]
	]);
	sendResultInfoAsJson($response);

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

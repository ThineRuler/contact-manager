<?php
	// Search contacts for one user by first and/or last name.
	// Matching is case-insensitive and partial (e.g. "Jo" matches John, Jones, Jobs).
	// Results are filtered in SQL with LIMIT/OFFSET so the server does not load every row.

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
	$Search = trim((string)($inData["Search"] ?? $inData["search"] ?? ""));
	$Page   = max(1, (int)($inData["Page"] ?? 1));
	$Limit  = (int)($inData["ResultsPerPage"] ?? 25);
	$Limit  = max(1, min(50, $Limit));
	$Offset = ($Page - 1) * $Limit;

	if ($UserID <= 0)
	{
		returnWithError("UserID is required.");
		exit;
	}

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error)
	{
		returnWithError($conn->connect_error);
		exit;
	}

	$conn->set_charset("utf8mb4");
	$like = likeTerm($Search);

	$countStmt = $conn->prepare(
		"SELECT COUNT(*) AS Total
		 FROM Contacts
		 WHERE UserID = ?
		   AND (
				FirstName LIKE ? ESCAPE '\\\\'
				OR LastName LIKE ? ESCAPE '\\\\'
				OR CONCAT(FirstName, ' ', LastName) LIKE ? ESCAPE '\\\\'
		   )"
	);
	$countStmt->bind_param("isss", $UserID, $like, $like, $like);
	$countStmt->execute();
	$total = (int)$countStmt->get_result()->fetch_assoc()["Total"];
	$countStmt->close();

	$stmt = $conn->prepare(
		"SELECT ID, FirstName, LastName, Phone, Email, UserID, CreationDate
		 FROM Contacts
		 WHERE UserID = ?
		   AND (
				FirstName LIKE ? ESCAPE '\\\\'
				OR LastName LIKE ? ESCAPE '\\\\'
				OR CONCAT(FirstName, ' ', LastName) LIKE ? ESCAPE '\\\\'
		   )
		 ORDER BY LastName, FirstName, ID
		 LIMIT ? OFFSET ?"
	);
	$stmt->bind_param("isssii", $UserID, $like, $like, $like, $Limit, $Offset);
	$stmt->execute();
	$result = $stmt->get_result();

	$results = [];
	while ($row = $result->fetch_assoc())
	{
		$results[] = [
			"id"          => (int)$row["ID"],
			"firstName"   => $row["FirstName"],
			"lastName"    => $row["LastName"],
			"phone"       => $row["Phone"],
			"email"       => $row["Email"],
			"userId"      => (int)$row["UserID"],
			"dateCreated" => $row["CreationDate"]
		];
	}

	$stmt->close();
	$conn->close();

	sendResultInfoAsJson(json_encode([
		"error"          => "",
		"search"         => $Search,
		"page"           => $Page,
		"resultsPerPage" => $Limit,
		"total"          => $total,
		"results"        => $results
	]));

	function likeTerm($search)
	{
		$escaped = str_replace(["\\", "%", "_"], ["\\\\", "\\%", "\\_"], $search);
		return "%" . $escaped . "%";
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

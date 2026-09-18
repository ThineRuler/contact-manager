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
    $UserID = (int)($inData["UserID"] ?? 0);
    $ids = [];

    if (isset($inData["ids"]) && is_array($inData["ids"]))
    {
        $ids = array_map("intval", $inData["ids"]);
    }
    elseif (isset($inData["ID"]))
    {
        $ids = [(int)$inData["ID"]];
    }

    $ids = array_values(array_filter($ids, fn($id) => $id > 0));

    if ($UserID <= 0 || empty($ids))
    {
        returnWithError("UserID and at least one contact ID are required.");
        exit;
    }

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
    if ($conn->connect_error)
    {
        returnWithError($conn->connect_error);
        exit;
    }

    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $types = str_repeat("i", count($ids));
    $query = "DELETE FROM Contacts WHERE UserID = ? AND ID IN ($placeholders)";

    $stmt = $conn->prepare($query);
    $params = array_merge([$UserID], $ids);
    $bindTypes = "i" . $types;
    $stmt->bind_param($bindTypes, ...$params);

    if (!$stmt->execute())
    {
        $err = $stmt->error;
        $stmt->close();
        $conn->close();
        returnWithError($err);
        exit;
    }

    $stmt->close();
    $conn->close();
    sendResultInfoAsJson(json_encode(["error" => ""]));

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
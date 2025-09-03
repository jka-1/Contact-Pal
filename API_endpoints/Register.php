<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$inData = getRequestInfo();
$first = trim($inData['firstName'] ?? '');
$last  = trim($inData['lastName']  ?? '');
$login = trim($inData['login']     ?? '');
$pass  = (string)($inData['password'] ?? '');

if ($first === '' || $last === '' || $login === '' || $pass === '') {
  returnWithError("Missing required field(s): firstName, lastName, login, password"); exit;
}

$conn = new mysqli("localhost", "TheBeast", "Team2", "CONTACTPALDB");
if ($conn->connect_error) { returnWithError("DB connection failed: " . $conn->connect_error); exit; }
$conn->set_charset("utf8mb4");

// Ensure UNIQUE constraint exists on Login (run once in MySQL shell):
// ALTER TABLE Users ADD UNIQUE KEY (Login);

$check = $conn->prepare("SELECT ID FROM Users WHERE Login=? LIMIT 1");
$check->bind_param("s", $login);
$check->execute();
$checkRes = $check->get_result();
if ($checkRes && $checkRes->fetch_assoc()) { 
  $check->close(); $conn->close(); 
  returnWithError("User already exists"); exit; 
}
$check->close();

$ins = $conn->prepare("INSERT INTO Users (firstName, lastName, Login, Password) VALUES (?,?,?,?)");
$ins->bind_param("ssss", $first, $last, $login, $pass);
if (!$ins->execute()) {
  $err = ($conn->errno === 1062) ? "User already exists" : ("Insert failed: " . $conn->error);
  $ins->close(); $conn->close(); returnWithError($err); exit;
}

$newId = (int)$ins->insert_id;
$ins->close(); $conn->close();
returnWithInfo($first, $last, $newId);

function getRequestInfo() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function sendResultInfoAsJson($obj) { echo $obj; }
function returnWithError($err) {
  sendResultInfoAsJson(json_encode(["id"=>0,"firstName"=>"","lastName"=>"","error"=>$err]));
}
function returnWithInfo($firstName, $lastName, $id) {
  sendResultInfoAsJson(json_encode(["id"=>$id,"firstName"=>$firstName,"lastName"=>$lastName,"error"=>""]));
}
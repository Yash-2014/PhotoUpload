<?php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

$config = include __DIR__ . "/imageKit.php";

$privateKey  = trim($config["privateKey"]);
$urlEndpoint = trim($config["urlEndpoint"]);

if (!isset($_FILES["image"])) {
    echo json_encode(["error" => "No image uploaded"]);
    exit;
}

// Read file
$filePath = $_FILES["image"]["tmp_name"];
$fileName = basename($_FILES["image"]["name"]);

$fileData = base64_encode(file_get_contents($filePath));
$fileMime = mime_content_type($filePath);

$payload = "data:$fileMime;base64,$fileData";

// cURL request
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://upload.imagekit.io/api/v1/files/upload",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        "file" => $payload,
        "fileName" => $fileName,
    ],
    CURLOPT_USERPWD => $privateKey . ":",
]);

$response = curl_exec($curl);
$error = curl_error($curl);
curl_close($curl);

// If cURL fails → InfinityFree blocked API
if ($error) {
    echo json_encode(["error" => "Server Error: $error"]);
    exit;
}

// If InfinityFree injects HTML error → remove it
$jsonStart = strpos($response, "{");
if ($jsonStart !== false) {
    $response = substr($response, $jsonStart);
}

echo $response;
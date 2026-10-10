<?php
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

require_once "config.php";

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || $message === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid name, email and message."
    ]);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO messages (name, email, message) VALUES (:name, :email, :message)"
);

$stmt->execute([
    ":name" => $name,
    ":email" => $email,
    ":message" => $message
]);

echo json_encode([
    "success" => true,
    "message" => "Message sent successfully!"
]);
?>

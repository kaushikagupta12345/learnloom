<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "YOUR PASSWORD"; // XAMPP password
$dbname = "DB_NAME";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Receive form data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $branch = $_POST['branch'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    // Insert into database
    $sql = "INSERT INTO users (username, email, branch, password) VALUES ('$username', '$email', '$branch', '$password')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "User registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
    }
}

$conn->close();
?>

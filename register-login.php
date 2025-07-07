<?php
session_start();
$servername = "localhost";
$username = "USERNAME"; 
$password = "YOUR PASSWORD"; 
$dbname = "YOUR DB_NAME",

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$role = isset($_GET['role']) ? $_GET['role'] : '';

// Handle Registration
if (isset($_POST['register'])) {
    $username = $_POST['register-username'];
    $email = $_POST['register-email'];
    $branch = $_POST['register-branch'];
    $semester = $_POST['register-semester'];
    $password = password_hash($_POST['register-password'], PASSWORD_BCRYPT);

    $sql_check = "SELECT * FROM users WHERE username='$username' AND role='$role'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Username already taken. Please choose another one.');</script>";
    } else {

        $sql = "INSERT INTO users (username, email, branch,semester, password, role) VALUES ('$username', '$email', '$branch', '$semester', '$password', '$role')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registration successful! Please log in.'); window.location = 'register-login.php?role=$role&login=true';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $username = $_POST['login-username'];
    $password = $_POST['login-password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND role='$role'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'provider') {
                echo "<script>alert('Login successful! Redirecting to Provider Dashboard.'); window.location = 'provider-dashboard.php';</script>";
            } else {
                echo "<script>alert('Login successful! Redirecting to Seeker Dashboard.'); window.location = 'seeker-dashboard.php';</script>";
            }
        } else {
            echo "<script>alert('Invalid password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('No user found with that username or incorrect role.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register and Login</title>
    <style>
       body {
            font-family: Arial, sans-serif;
            background: rgb(5,27,70);
            background: linear-gradient(90deg, rgba(5,27,70,1) 0%, rgba(9,68,121,0.7663817663817664) 0%, rgba(2,0,36,0.5085470085470085) 0%, rgba(7,50,99,1) 28%, rgba(3,96,139,1) 60%, rgba(3,114,157,1) 81%, rgba(4,71,114,1) 100%, rgba(0,212,255,0.9031339031339032) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            width: 400px;
            padding: 20px;
            background-color: #fff;
            border-radius: 9px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 10px;
        }
        .form-container h2 {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
            font-family: papyrus;
            font-size: 40px;
        }
        .form-container input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 2px solid #ccc;
        }
        .form-container button {
            width: 95%;
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF40;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #45a039;
        }
        .toggle-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007BFF;
            cursor: pointer;
        }
        .toggle-link:hover {
            text-decoration: underline;
        }
        .cut-button-container {
            position: absolute;
            top: 100px;
            right: 400px;
            width: 30px;
            height: 30px;
            background-color: #f44336;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        .cut-button-container:hover {
            background-color: #e53935;
        }
        .cut-button {
            font-size: 18px;
            color: white;
            border: none;
            background: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="cut-button-container" onclick="window.location.href = '//127.0.0.1:5500/index.html';">
        <button class="cut-button">×</button>
    </div>

    <!-- Register Form -->
    <div class="form-container" id="register-form" <?php echo isset($_GET['login']) ? 'style="display:none;"' : ''; ?>>
        <h2>Register as <?php echo ucfirst($role); ?></h2>
        <form method="POST" action="register-login.php?role=<?php echo $role; ?>">
            <input type="text" name="register-username" placeholder="Username" required>
            <input type="email" name="register-email" placeholder="Email" required>
            <input type="text" name="register-branch" placeholder="Branch" required>
            <input type="number" name="register-semester" placeholder="Semester" required>
            <input type="password" name="register-password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <span class="toggle-link" onclick="toggleForm()">Already have an account? Login</span>
    </div>

    <!-- Login Form -->
    <div class="form-container" id="login-form" <?php echo isset($_GET['login']) ? 'style="display:block;"' : 'style="display:none;"'; ?>>
        <h2>Login as <?php echo ucfirst($role); ?></h2>
        <form method="POST" action="register-login.php?role=<?php echo $role; ?>">
            <input type="text" name="login-username" placeholder="Username" required>
            <input type="password" name="login-password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <span class="toggle-link" onclick="toggleForm()">Don't have an account? Register</span>
    </div>

    <script>
        function toggleForm() {
            const registerForm = document.getElementById("register-form");
            const loginForm = document.getElementById("login-form");
            if (registerForm.style.display === "none") {
                registerForm.style.display = "block";
                loginForm.style.display = "none";
            } else {
                registerForm.style.display = "none";
                loginForm.style.display = "block";
            }
        }
    </script>

</body>
</html>

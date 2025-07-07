<?php
session_start();
// Ensure the user is logged in as a seeker
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'seeker') {
    header("Location: register-login.php");
    exit;
}

// Initialize the downloaded files array if it doesn't exist in the session
if (!isset($_SESSION['downloads'])) {
    $_SESSION['downloads'] = [];
}

$servername = "localhost";
$username = "root";
$password = "kaushika";
$dbname = "learnloom";
// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$sql = "SELECT username, email, branch, semester FROM users WHERE username = '" . $_SESSION['username'] . "'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row['username'];
    $email = $row['email'];
    $branch = $row['branch'];
    $semester = $row['semester'];
} else {
    echo "<script>alert('User data not found.');</script>";
}

// Fetch all resources from the resources table
$material_sql = "SELECT * FROM resources WHERE target_branch = '$branch' OR target_branch = 'all'";
$material_result = $conn->query($material_sql);
$conn->close();

// Handle file download request
if (isset($_GET['download'])) {
    $file_name = $_GET['download'];
    $file_path = __DIR__ . '/uploads/' . basename($file_name);

    if (file_exists($file_path)) {
        $_SESSION['downloads'][] = $file_name;
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . basename($file_name) . "\"");
        header("Content-Length: " . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        echo "<script>alert('File not found: $file_path');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learn Loom Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #136dec;
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            margin: 0;
            margin-left: 40px;
        }
        .navbar button {
            background-color: #e72525;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 60px;
        }
        .dashboard-container {
            display: flex;
            flex-grow: 1;
        }
        .sidebar {
            width: 250px;
            background-color: #222;
            color: white;
            padding: 1rem;
            position: -webkit-sticky; 
            position: sticky;
            top: 0; 
            height: 100vh; 
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1); 
            z-index: 10; 
            overflow-y: auto; 
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu li {
            margin: 1rem 0;
        }
        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: block;
            font-size: 1.2rem;
            font-weight: bolder;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            padding: 1.2rem;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .sidebar-menu li a:hover {
            background-color: #5461ae;
        }
        #profile-section {
            padding: 2rem;
            background:url(https://i.pinimg.com/736x/76/ad/8a/76ad8ad7169e8227217c2d838dac5ba9.jpg);
            box-shadow: 2px 4px 6px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            width: 35%;
            height: 45%;
            margin-left: 25%;
            text-align: center;
        }
        #profile-section h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
            font-family: papyrus;
        }
        #profile-section p {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        #profile-section, #uploaded-section, #downloads-section {
            padding: 2rem;
            border-radius: 10px;
            margin-top: 20px;
            display: none; /* Hide all sections by default */
        }
        #profile-section h3,
        #uploaded-section h3,
        #downloads-section h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        #welcome-section {
            align-items: center;
            padding: 4rem;
            text-align: center;
        }
        #welcome-section h2 {
            font-family: papyrus;
            font-size: 3.5rem;
        }
        .dashboard-main {
            flex-grow: 1;
            padding: 1rem;
        }
        #uploaded-section {
            list-style: none;
            padding: 1.2rem;
            margin: 10px;
            border-radius: 8px;
            justify-content: space-around;
        }
        #uploaded-section h3{
            color: #222;
            text-align: center;
        }
        .resource-item {
            background-color: white;
            padding: 1.3rem;
            margin: 1rem 1rem 1rem 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .resource-item h4 {
            margin: 0 0 0.5rem;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            justify-content: space-around;

        }
        .tab {
            background-color:rgb(32, 116, 37);
            color: white;
            padding: 1.4rem;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            width: 27%;
            font-size: 20px;
            transition: background-color 0.3s;
        }
        .tab:hover {
            background-color:rgb(115, 17, 87);
            color: white;
        }
        .resource-list {
            display: none;
            color:rgb(1, 14, 22);
            text-decoration: none;
            background: url(https://img.freepik.com/free-vector/black-silk-fabric-top-view-scene-luxury-wave_107791-19892.jpg);
            padding: 1.5rem;
            margin: 1.5rem 1.5rem 1.5rem 1.5rem;
            border-radius: 8px;
            margin-left: 15%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 70%;
        }
        .resource-list.active {
            display: block;
        }
        .resource-list .not-uploaded {
            text-align: center;
            font-size: 1.2rem;
            color: #ff4d4d; /* A noticeable red color for emphasis */
            background-color: #ffe6e6; /* A light red background for visibility */
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem auto;
            width: 80%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }

        .resource-list .not-uploaded:hover {
            background-color: #ffcccc; /* Slightly darker on hover for interactivity */
        }

    </style>
</head>
<body>
<header>
    <div class="navbar">
        <h1>LearnLoom</h1>
        <button id="logout-btn" onclick="window.location.href = '//127.0.0.1:5500/index.html'">Logout</button>
    </div>
</header>
<div class="dashboard-container">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="#" class="sidebar-link" data-target="profile-section">Profile</a></li>
            <li><a href="#" class="sidebar-link" data-target="uploaded-section">Uploaded</a></li>
            <li><a href="#" class="sidebar-link" data-target="downloads-section">Downloads</a></li>
        </ul>
    </aside>
    <!-- Main Content -->
    <main class="dashboard-main">
            <section id="welcome-section">
                <h2>Welcome to Learn Loom</h2>
                <p>This is the dashboard where you can upload and manage your learning resources.</p>
            </section>
            <!-- Profile Section -->
            <section id="profile-section">
                <h3>Profile</h3><hr>
                <p><strong>Username:</strong> <?php echo $username; ?></p>
                <p><strong>Email:</strong> <?php echo $email; ?></p>
                <p><strong>Branch:</strong> <?php echo $branch; ?></p>
                <p><strong>Semester:</strong> <?php echo $semester; ?></p> 
            </section>

        <section id="uploaded-section">
            <h3>Uploaded Resources</h3>
            <div class="tabs">
                <div class="tab" data-type="file">Files</div>
                <div class="tab" data-type="book">Book Recommendations</div>
                <div class="tab" data-type="link">Links</div>
            </div>

            <div id="files" class="resource-list">
                <?php
                $material_result->data_seek(0);
                $has_files = false;
                while ($material = $material_result->fetch_assoc()) {
                    if ($material['upload_type'] == 'file') {
                        $has_files = true;
                        echo '<div class="resource-item">';
                        echo '<h4>' . htmlspecialchars($material['resource_name']) . '</h4>';
                        echo '<p><strong>Download:</strong> <a href="?download=' . urlencode($material['file_path']) . '">Download File</a></p>';
                        echo '<p><strong>Uploaded By :</strong> ' . htmlspecialchars($material['username']) . '</p>';
                        echo '<p><strong>Branch & Semester : </strong> ' . htmlspecialchars($material['branch'].' & '.$material['semester']) . '</p>';
                        echo '<p><strong>Uploaded At :</strong> ' . htmlspecialchars($material['uploaded_at']) . '</p>';
                        echo '</div>';
                    }
                }
                if (!$has_files) {
                    echo '<div class="not-uploaded">File not uploaded yet.</div>';
                }
                ?>
            </div>
                
            <div id="books" class="resource-list">
                <?php
                $material_result->data_seek(0);
                $has_books = false;
                while ($material = $material_result->fetch_assoc()) {
                    if ($material['upload_type'] == 'book') {
                        $has_books = true;
                        echo '<div class="resource-item">';
                        echo '<h4>' . htmlspecialchars($material['resource_name']) . '</h4>';
                        echo '<h4>' . htmlspecialchars($material['book_name']) . '</h4>';
                        echo '<p><strong>Uploaded By :</strong> ' . htmlspecialchars($material['username']) . '</p>';
                        echo '<p><strong>Branch & Semester : </strong> ' . htmlspecialchars($material['branch'].' & '.$material['semester']) . '</p>';
                        echo '<p><strong>Uploaded At :</strong> ' . htmlspecialchars($material['uploaded_at']) . '</p>';
                        echo '</div>';
                    }
                }
                if (!$has_books) {
                    echo '<div class="not-uploaded">No book recommendations uploaded yet.</div>';
                }
                ?>
            </div>
                
            <div id="links" class="resource-list">
                <?php
                $material_result->data_seek(0);
                $has_links = false;
                while ($material = $material_result->fetch_assoc()) {
                    if ($material['upload_type'] == 'link') {
                        $has_links = true;
                        echo '<div class="resource-item">';
                        echo '<h4>' . htmlspecialchars($material['resource_name']) . '</h4>';
                        echo '<h4>Link: <a href="' . htmlspecialchars($material['link_url']) . '" target="_blank">Visit</a></h4>';
                        echo '<p><strong>Uploaded By :</strong> ' . htmlspecialchars($material['username']) . '</p>';
                        echo '<p><strong>Branch & Semester : </strong> ' . htmlspecialchars($material['branch'].' & '.$material['semester']) . '</p>';
                        echo '<p><strong>Uploaded At :</strong> ' . htmlspecialchars($material['uploaded_at']) . '</p>';
                        echo '</div>';
                    }
                }
                if (!$has_links) {
                    echo '<div class="not-uploaded">No links uploaded yet.</div>';
                }
                ?>
            </div>
        </section>

        <!-- Downloads Section -->
        <section id="downloads-section">
                <h3>Downloaded Resource</h3>
                <?php
                if (count($_SESSION['downloads']) > 0) {
                    foreach ($_SESSION['downloads'] as $downloaded_file) {
                        echo '<p>' . htmlspecialchars($downloaded_file) . '</p>';
                    }
                } else {
                    echo '<p>No files downloaded yet.</p>';
                }
                ?>
        </section>
    </main>
</div>

<script>
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            // Hide all sections
            document.querySelectorAll('.dashboard-main section').forEach(section => {
                section.style.display = "none";
            });
            // Show the clicked section
            const targetSection = document.getElementById(link.dataset.target);
            targetSection.style.display = "block";
        });
    });

    const tabs = document.querySelectorAll('.tab');
    const resourceLists = document.querySelectorAll('.resource-list');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            resourceLists.forEach(list => list.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(tab.dataset.type + 's').classList.add('active');
        });
    });
</script>
</body>
</html>

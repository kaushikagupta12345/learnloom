<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'provider') {
    header("Location: register-login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "kaushika";
$dbname = "learnloom";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT username, email, branch, semester FROM users WHERE username = '" . $_SESSION['username'] . "'";
$result = $conn->query($sql);
if ($result === false) {
    echo "Error: " . $conn->error;
} else {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $email = $row['email'];
        $branch = $row['branch'];
        $semester = $row['semester'];
    } else {
        echo "<script>alert('User data not found.');</script>";
    }
}

// Success flag
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['upload-type']) && !empty($_POST['upload-type'])) {
        $upload_type = $_POST['upload-type'];
        $resource_name = isset($_POST['resource-name']) ? trim($_POST['resource-name']) : '';
        $target_branch = isset($_POST['target-branch']) ? $_POST['target-branch'] : 'all';

        if (empty($resource_name)) {
            echo "<script>alert('Resource name is required.');</script>";
        } else {
            if ($upload_type == 'file' && isset($_FILES['file-upload'])) {
                $file_name = $_FILES['file-upload']['name'];
                $file_tmp = $_FILES['file-upload']['tmp_name'];
                $file_path = __DIR__ . '/uploads/' . $file_name; // Absolute path

                if (move_uploaded_file($file_tmp, $file_path)) {
                    $sql = "INSERT INTO resources (resource_name, semester, upload_type, file_path, username, email, branch, target_branch) 
                            VALUES ('$resource_name', '$semester', '$upload_type', '$file_path', '$username', '$email', '$branch', '$target_branch')";

                    if ($conn->query($sql) === TRUE) {
                        $successMessage = "File uploaded successfully!";
                    } else {
                        echo "Error: " . $conn->error;
                    }
                } else {
                    echo "<script>alert('Failed to upload file.');</script>";
                }
            } elseif ($upload_type == 'book' && isset($_POST['book-name']) && !empty($_POST['book-name'])) {
                $book_name = trim($_POST['book-name']);
                $sql = "INSERT INTO resources (resource_name, semester, upload_type, book_name, username, email, branch, target_branch) 
                        VALUES ('$resource_name', '$semester', '$upload_type', '$book_name', '$username', '$email', '$branch', '$target_branch')";

                if ($conn->query($sql) === TRUE) {
                    $successMessage = "Book recommendation submitted successfully!";
                } else {
                    echo "Error: " . $conn->error;
                }
            } elseif ($upload_type == 'link' && isset($_POST['link-url']) && !empty($_POST['link-url'])) {
                $link_url = trim($_POST['link-url']);
                $sql = "INSERT INTO resources (resource_name, semester, upload_type, link_url, username, email, branch, target_branch) 
                        VALUES ('$resource_name', '$semester', '$upload_type', '$link_url', '$username', '$email', '$branch', '$target_branch')";

                if ($conn->query($sql) === TRUE) {
                    $successMessage = "Link uploaded successfully!";
                } else {
                    echo "Error: " . $conn->error;
                }
            } else {
                echo "<script>alert('Please fill all required fields for the selected upload type.');</script>";
            }
        }
    } else {
        echo "<script>alert('Please select an upload type.');</script>";
    }
}

if (isset($_GET['download'])) {
    $file_name = $_GET['download'];
    
    $file_path = __DIR__ . '/uploads/' . basename($file_name); 

    // Debugging file path
    echo "<script>console.log('Attempting to download file from path: " . $file_path . "');</script>";

    if (file_exists($file_path)) {
        $_SESSION['downloads'][] = $file_name;

        // Force download the file
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . basename($file_name) . "\"");
        header("Content-Length: " . filesize($file_path));
        readfile($file_path);

        exit;
    } else {
        echo "<script>alert('File not found: $file_path');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_resource'])) {
    $resource_id = $_POST['resource_id'];
    $sql_delete = "DELETE FROM resources WHERE id = '$resource_id' AND username = '$username'";

    if ($conn->query($sql_delete) === TRUE) {
        echo "<script>alert('Resource deleted successfully.');</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}


// Fetch uploaded resources by the provider
$sql_files = "SELECT * FROM resources WHERE username = '$username' AND upload_type = 'file'";
$sql_books = "SELECT * FROM resources WHERE username = '$username' AND upload_type = 'book'";
$sql_links = "SELECT * FROM resources WHERE username = '$username' AND upload_type = 'link'";

$uploaded_files = $conn->query($sql_files);
$uploaded_books = $conn->query($sql_books);
$uploaded_links = $conn->query($sql_links);
$conn->close();
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
            padding: 0.9rem;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .sidebar-menu li a:hover {
            background-color: #5461ae;
        }

        .dashboard-main {
            flex-grow: 1;
            padding: 1rem;
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

        #profile-section, #upload-section, #uploaded-section {
            display: none;
        }

        #profile-section {
            padding: 2rem;
            background:url(https://i.pinimg.com/736x/76/ad/8a/76ad8ad7169e8227217c2d838dac5ba9.jpg);
            box-shadow: 2px 4px 6px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            width: 35%;
            height: 45%;
            margin-top: 7%;
            margin-left: 25%;
            text-align: center;
           
        }

        #profile-section h3 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-family: papyrus;
        }

        #profile-section p {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            font-family: sans-serif;
        }

        .logout-btn-container {
            display: flex;
            justify-content: flex-end;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: 400px;
        }

        input, textarea, select, button {
            padding: 0.5rem;
            font-size: 1rem;
        }

        input[type="file"] {
            border: none;
        }

        button {
            background-color: #3f51b5;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #303f9f;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .dashboard-main {
                padding: 1rem;
            }
        }


        /* Upload Section Styles */
#upload-section {
    background:url(https://static.vecteezy.com/system/resources/thumbnails/008/617/161/small/abstract-gradient-pastel-blue-and-purple-background-neon-pastel-color-template-for-website-or-presentation-free-free-vector.jpg);
    background-repeat: no-repeat;
    background-size: cover; 
    background-position: center;
    padding: 1rem;
    position:absolute;
    width: 35%;
    border-radius: 10px;
    box-shadow: 2px 4px 6px 8px rgba(0.1, 01, 0, 0.1);
    margin-top: 20px;
    top: 22%;
    left:42%;
}

#upload-section h2 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 1rem;
    font-family: papyrus;

}

.upload-type-selection {
    margin-bottom: 1rem;
}

.upload-type-selection label {
    font-size: 1.2rem;
    font-weight: bold;
}

#upload-type {
    width: 100%;
    padding: 0.8rem;
    margin: 0.5rem 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

#file-upload-container, #book-upload-container, #link-upload-container {
    margin-top: 1rem;
}

#file-upload-container label,
#book-upload-container label,
#link-upload-container label {
    font-size: 1rem;
    font-weight: bold;
}

#file-upload-container input,
#book-upload-container input,
#link-upload-container input {
    width: 100%;
    padding: 0.8rem;
    margin: 0.5rem 0;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#resorce-name , #target-branch {
    margin: 15px;
    font-size: large;
}

button {
    background-color: #3f51b5;
    color: white;
    border: none;
    padding: 0.8rem;
    font-size: 1rem;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #303f9f;
}

button:focus {
    outline: none;
}

/* Styles for File, Book, and Link blocks */
#file-upload-container,
#book-upload-container,
#link-upload-container {
    display: none; /* Initially hidden */
}

/* Mobile Responsiveness for Upload Section */
@media (max-width: 768px) {
    #upload-section {
        padding: 1rem;
    }

    #upload-type {
        font-size: 1rem;
    }

    button {
        padding: 0.7rem;
        font-size: 1rem;
    }
}

.uploaded-blocks {
    display: flex;
    justify-content: space-around;
    margin-bottom: 2rem;
    
}

.uploaded-block {
    background-color:rgb(32, 116, 37);
    color: white;
    padding: 0.5rem;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    width: 30%;
    transition: background-color 0.3s;
}
        #uploaded-section h2{
            color: #222;
            text-align: center;
            font-size: 30px;
        }
.uploaded-block:hover {
    background-color:rgb(115, 17, 87);
}

.uploaded-content {
    place-items: center;
    margin-top: 0.5rem;
    
}

.uploaded-content ul {
    list-style: none;
    background: url(https://img.freepik.com/free-vector/black-silk-fabric-top-view-scene-luxury-wave_107791-19892.jpg);
    padding: 1.5rem;
    margin: 1rem 1rem 1rem 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    width: 70%;
    
}

.uploaded-content li {
    background-color: white;
    padding: 1.5rem;
    margin: 1rem 1rem 1rem 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    
}
.uploaded-content li div {
    line-height: 1.5;
    margin-left: 20px;
}

.uploaded-content li a {
    color: #3498db;
    text-decoration: none;
}

.uploaded-content li a:hover {
    text-decoration: underline;
}
#uploaded-files button[name="delete_resource"], #uploaded-links button[name="delete_resource"], #uploaded-books button[name="delete_resource"] {
    background-color: #f44336; 
    color: white; 
    border: none;
    padding: 10px 30px; 
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px; 
    transition: background-color 0.3s ease; 
    margin-top: 10px;
    float: right;
    margin-right: 30px;

}

#uploaded-files button[name="delete_resource"]:hover, #uploaded-links button[name="delete_resource"]:hover, #uploaded-books button[name="delete_resource"]:hover {
    background-color: #d32f2f; 
}


.popup {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.popup-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    width: 300px;
}

.close-btn {
    color: #aaa;
    float: right;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
}

.close-btn:hover {
    color: #000;
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
                <li><a href="#" class="sidebar-link" data-target="profile-section" onclick="showSection('profile-section')">Profile</a></li>
                <li><a href="#" class="sidebar-link" data-target="upload-section" onclick="showSection('upload-section')">Upload</a></li>
                <li><a href="#" class="sidebar-link" data-target="uploaded-section" onclick="showSection('uploaded-section')">Uploaded</a></li>
            </ul>
        </aside>

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

            <!-- Upload Section -->
            <section id="upload-section">
                <form action="provider-dashboard.php" method="post" enctype="multipart/form-data">
                    <div class="upload-type-selection">
                        <label for="upload-type">Choose Upload Type:</label>
                        <select id="upload-type" name="upload-type" onchange="toggleFileUpload()">
                            <option value="">Select Type</option>
                            <option value="file">File</option>
                            <option value="book">Book</option>
                            <option value="link">Link</option>
                        </select>
                    </div>

                    <div id="file-upload-container" class="file-upload-container" style="display: none;">
                        <label for="file-upload">Choose file to upload:</label>
                        <input type="file" name="file-upload" id="file-upload">
                    </div>

                    <div id="book-upload-container" style="display: none;">
                        <label for="book-name">Enter Book Name:</label>
                        <input type="text" name="book-name" id="book-name">
                    </div>

                    <div id="link-upload-container" style="display: none;">
                        <label for="link-url">Enter Link URL:</label>
                        <input type="text" name="link-url" id="link-url">
                    </div>

                    <div>
                        <label for="resource-name"><b>Resource Name:</b></label>
                        <input type="text" name="resource-name" id="resource-name" required>
                    </div>

                    <div>
                        <label for="target-branch"><b>Show to:</b> </label>
                        <select name="target-branch" id="target-branch">
                            <option value="all">All Branches</option>
                            <option value="cse">CSE</option>
                            <option value="ece">ECE</option>
                            <option value="me">ME</option>
                            <option value="ee">EE</option>
                        </select>
                    </div>

                    <button type="submit">Upload Resource</button>
                </form>
            </section>

            <!-- Uploaded Section -->
            <section id="uploaded-section">
    <h2>Uploaded Items</h2>

    <div class="uploaded-blocks">
        <div class="uploaded-block" id="file-block" onclick="showUploadedContent('file')">
            <h3>Files</h3>
        </div>
        <div class="uploaded-block" id="book-block" onclick="showUploadedContent('book')">
            <h3>Book Recommendations</h3>
        </div>
        <div class="uploaded-block" id="link-block" onclick="showUploadedContent('link')">
            <h3>Links</h3>
        </div>
    </div>

    <!-- Content for files, books, and links (initially hidden) -->
    <div id="file-content" class="uploaded-content" style="display: none;">
        <ul id="uploaded-files">
            <?php
            if ($uploaded_files->num_rows > 0) {
                while ($row = $uploaded_files->fetch_assoc()) {
                    echo '<li>';
                    echo '<form method="POST" style="display:inline;">';
                    echo '<input type="hidden" name="resource_id" value="' . $row['id'] . '">';
                    echo '<button type="submit" name="delete_resource">Delete</button>';
                    echo '</form>';
                    echo '<div>';
                    echo '<strong>Resource Name: ' . $row['resource_name'] . '</strong><br>';
                    echo '<span><strong>Download:</strong> <a href="?download=' . urlencode($row['file_path']) . '" target="_blank">Download File</a></span><br>';
                    echo '<span>' . ucfirst($row['upload_type']) . ' - ' . $row['resource_name'] . '</span><br>';
                    echo '<span>Target Branch: ' . ($row['target_branch'] == 'all' ? 'All Branches' : strtoupper($row['target_branch'])) . '</span><br>';
                    echo '<strong>Uploaded At: ' . $row['uploaded_at'] . '</strong><br>';
                    echo '</div>';
                    echo '</li>';
                }
            } else {
                echo '<li>No files uploaded yet.</li>';
            }
            ?>
        </ul>
    </div>

    <div id="book-content" class="uploaded-content" style="display: none;">
        <ul id="uploaded-books">
            <?php
            if ($uploaded_books->num_rows > 0) {
                while ($row = $uploaded_books->fetch_assoc()) {
                    echo '<li>';
                    echo '<form method="POST" style="display:inline;">';
                    echo '<input type="hidden" name="resource_id" value="' . $row['id'] . '">';
                    echo '<button type="submit" name="delete_resource">Delete</button>';
                    echo '</form>';
                    echo '<div>';
                    echo '<strong>Resource Name: ' . $row['resource_name'] . '</strong><br>';
                    echo '<span>Book Name: ' . $row['book_name'] . '</span><br>';
                    echo '<span>Target Branch: ' . ($row['target_branch'] == 'all' ? 'All Branches' : strtoupper($row['target_branch'])) . '</span><br>';
                    echo '<strong>Uploaded At: ' . $row['uploaded_at'] . '</strong><br>';
                    echo '</div>';
                    echo '</li>';
                }
            } else {
                echo '<li>No book recommendations uploaded yet.</li>';
            }
            ?>
        </ul>
    </div>

    <div id="link-content" class="uploaded-content" style="display: none;">
        <ul id="uploaded-links">
            <?php
            if ($uploaded_links->num_rows > 0) {
                while ($row = $uploaded_links->fetch_assoc()) {
                    echo '<li>';
                    echo '<form method="POST" style="display:inline;">';
                    echo '<input type="hidden" name="resource_id" value="' . $row['id'] . '">';
                    echo '<button type="submit" name="delete_resource">Delete</button>';
                    echo '</form>';
                    echo '<div>';
                    echo '<strong>Resource Name: ' . $row['resource_name'] . '</strong><br>';
                    echo 'URL:  <a href="' . $row['link_url'] . '" target="_blank">' . $row['link_url'] . '</a><br>';
                    echo '<span>Target Branch: ' . ($row['target_branch'] == 'all' ? 'All Branches' : strtoupper($row['target_branch'])) . '</span><br>';
                    echo '<strong>Uploaded At: ' . $row['uploaded_at'] . '</strong><br>';
                    echo '</div>';
                    echo '</li>';
                }
            } else {
                echo '<li>No links uploaded yet.</li>';
            }
            ?>
        </ul>
    </div>
</section>

        </main>
    </div>

    <div id="success-popup" class="popup">
        <div class="popup-content">
            <span class="close-btn">&times;</span>
            <p id="popup-message"></p>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('section');
            sections.forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(sectionId).style.display = 'block';
        }

        function toggleFileUpload() {
            const selectedType = document.getElementById('upload-type').value;
            document.getElementById('file-upload-container').style.display = selectedType === 'file' ? 'block' : 'none';
            document.getElementById('book-upload-container').style.display = selectedType === 'book' ? 'block' : 'none';
            document.getElementById('link-upload-container').style.display = selectedType === 'link' ? 'block' : 'none';
        }
          
        function showUploadedContent(contentType) {
    // Hide all content
    const contentSections = document.querySelectorAll('.uploaded-content');
    contentSections.forEach(section => {
        section.style.display = 'none';
    });

    // Show the clicked content
    document.getElementById(contentType + '-content').style.display = 'block';
}

        document.addEventListener("DOMContentLoaded", function () {
        const successMessage = "<?php echo $successMessage; ?>";
        const popup = document.getElementById("success-popup");
        const popupMessage = document.getElementById("popup-message");
        const closeBtn = document.querySelector(".close-btn");

        if (successMessage) {
            popupMessage.textContent = successMessage;
            popup.style.display = "flex"; // Show the popup
        }

        closeBtn.addEventListener("click", function () {
            popup.style.display = "none"; // Hide the popup on close
        });

        // Close popup when clicking outside the content
        window.addEventListener("click", function (event) {
            if (event.target === popup) {
                popup.style.display = "none";
            }
        });
    });
    </script>
</body>
</html>

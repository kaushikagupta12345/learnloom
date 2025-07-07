const express = require("express");
const mysql = require('mysql2');
const bcrypt = require("bcrypt");
const cors = require("cors");

const app = express();
const PORT = 5000;

// Middleware
app.use(express.json());
app.use(cors());

// MySQL Connection
const db = mysql.createConnection({
    host: "localhost",
    user: "root", // Change this to your MySQL username
    $password = "YOUR PASSWORD"; // XAMPP password
    $dbname = "DB_NAME";
});

db.connect(err => {
    if (err) {
        console.error("Database connection failed:", err.stack);
        return;
    }
    console.log("Connected to MySQL database.");
});

// Register User
app.post("/register", async (req, res) => {
    const { username, email, branch, password, user_type } = req.body;

    // Validate input
    if (!username || !email || !branch || !password || !user_type) {
        return res.status(400).json({ message: "All fields are required." });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);

    // Insert user into database
    const sql = "INSERT INTO users (username, email, branch, password, user_type) VALUES (?, ?, ?, ?, ?)";
    db.query(sql, [username, email, branch, hashedPassword, user_type], (err, result) => {
        if (err) {
            if (err.code === "ER_DUP_ENTRY") {
                return res.status(400).json({ message: "Username already exists." });
            }
            return res.status(500).json({ message: "Database error.", error: err });
        }
        res.status(201).json({ message: `${user_type} registered successfully!` });
    });
});

// Login User
app.post("/login", (req, res) => {
    const { username, password } = req.body;

    if (!username || !password) {
        return res.status(400).json({ message: "All fields are required." });
    }

    // Check username in database
    const sql = "SELECT * FROM users WHERE username = ?";
    db.query(sql, [username], async (err, results) => {
        if (err) return res.status(500).json({ message: "Database error.", error: err });
        if (results.length === 0) return res.status(400).json({ message: "User not found." });

        const user = results[0];

        // Verify password
        const isMatch = await bcrypt.compare(password, user.password);
        if (!isMatch) return res.status(400).json({ message: "Invalid password." });

        res.status(200).json({ message: "Login successful!", user_type: user.user_type });
    });
});

// Start Server
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});

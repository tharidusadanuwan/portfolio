<?php
session_start();

include 'db_connect.php';

// Function to safely trim and escape data (though prepared statements are primary security)
function sanitize_input($data) {
    return trim($data);
}

// ---------- SIGNUP ----------
if (isset($_POST['signup'])) {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);

    // Input validation (basic check)
    if (empty($username) || empty($password)) {
        $signup_error = "Username and Password cannot be empty.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 1. Check if username already exists
        $check = $conn->prepare("SELECT username FROM admin_tbl WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $signup_error = "Username already exists! Choose a different one.";
        } else {
            // 2. Insert new user
            $stmt = $conn->prepare("INSERT INTO admin_tbl (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashedPassword);
            if ($stmt->execute()) {
                $_SESSION['admin_username'] = $username;
                header("Location: dashboard.php");
                exit;
            } else {
                error_log("Signup Insert Error: " . $stmt->error);
                $signup_error = "Error creating account! Please try again.";
            }
        }
    }
}

// ---------- LOGIN ----------
if (isset($_POST['login'])) {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);

    // Input validation
    if (empty($username) || empty($password)) {
        $login_error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT username, password FROM admin_tbl WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Login Success
                $_SESSION['admin_username'] = $row['username'];
                // Regenerate session ID to prevent Session Fixation
                session_regenerate_id(true); 
                header("Location: dashboard.php");
                exit;
            } else {
                $login_error = "Invalid username or password!"; // Generic error for security
            }
        } else {
            $login_error = "Invalid username or password!"; // Generic error for security
        }
    }
}

// Determine which box to show on page load based on error
$show_signup = false;
if (isset($signup_error) && !empty($signup_error)) {
    $show_signup = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login / Signup</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Your primary color: #6F9F9C */
            --primary-color: #6F9F9C; 
            /* A slightly darker shade for hover effect */
            --primary-hover: #5D8C89; 
            /* A contrasting light color for the background */
            --bg-light: #f4f7f6;
            /* A soft background gradient */
            --gradient-start: #d7e4e3;
            --gradient-end: #b6c9c8;
            /* Text color */
            --text-color: #333;
            /* Box shadow */
            --shadow-light: 0 10px 25px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow-light);
            padding: 40px;
            width: 320px;
            text-align: center;
            transition: all 0.5s ease-in-out; /* Smooth transition for toggle */
        }
        h2 {
            margin-bottom: 30px;
            color: var(--text-color);
            font-weight: 700;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: var(--primary-color);
        }
        button {
            width: 100%;
            /* Use a slightly different color for buttons to stand out */
            background: var(--primary-color); 
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            margin-top: 10px;
            /* Mouse point new hover animation */
            transition: background 0.3s ease, transform 0.1s ease; 
        }
        button:hover {
            background: var(--primary-hover);
            transform: translateY(-2px); /* Slight lift on hover */
            box-shadow: 0 4px 10px rgba(111, 159, 156, 0.4);
        }
        .error {
            color: #d9534f; /* A standard error red */
            margin-top: 15px;
            font-weight: 500;
        }
        .toggle-link {
            margin-top: 20px;
            color: var(--primary-color);
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            transition: color 0.3s;
        }
        .toggle-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container" id="login-box" style="display: <?php echo $show_signup ? 'none' : 'block'; ?>">
    <h2>Admin Login 🔐</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="login">Log In</button>
        <?php if (!empty($login_error)) echo "<p class='error'>$login_error</p>"; ?>
    </form>
    <p class="toggle-link" onclick="toggleForm()">Don't have an account? Sign up</p>
</div>

<div class="container" id="signup-box" style="display: <?php echo $show_signup ? 'block' : 'none'; ?>">
    <h2>Admin Sign Up 📝</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Create Username" required>
        <input type="password" name="password" placeholder="Create Password" required>
        <button type="submit" name="signup">Create Account</button>
        <?php if (!empty($signup_error)) echo "<p class='error'>$signup_error</p>"; ?>
    </form>
    <p class="toggle-link" onclick="toggleForm()">Already have an account? Log In</p>
</div>

<script>
function toggleForm() {
    const loginBox = document.getElementById('login-box');
    const signupBox = document.getElementById('signup-box');
    
    // Toggle the display property
    const isLoginVisible = loginBox.style.display !== 'none';
    
    // Add a class for a potential animation effect before hiding (optional, for advanced effects)
    // Here we'll stick to a simple toggle for reliability
    
    loginBox.style.display = isLoginVisible ? 'none' : 'block';
    signupBox.style.display = isLoginVisible ? 'block' : 'none';
    
    // Clear error messages when switching forms
    const errorElements = document.querySelectorAll('.error');
    errorElements.forEach(el => el.style.display = 'none');
}

// Check PHP variables to ensure the correct form is shown on page load after an error
document.addEventListener('DOMContentLoaded', () => {
    // This is handled by the inline PHP 'style' attribute, but a fallback is good
    <?php if (isset($signup_error) && !empty($signup_error)): ?>
        toggleForm(); // If there was a signup error, ensure signup is visible
    <?php endif; ?>
});
</script>

</body>
</html>
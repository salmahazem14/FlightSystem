<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "travel";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

// Get user type from the URL parameter (hidden from user)
$userType = isset($_GET['type']) ? $_GET['type'] : ''; // 'passenger' or 'company'

// Check if userType is valid
if (!in_array($userType, ['passenger', 'company'])) {
    $error = 'Invalid user type.';
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    // Validate input
    if (!$email || !$password || !$userType) {
        $error = 'Please fill out all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Determine the correct table based on the user type
        if ($userType === 'passenger') {
            $table = 'passengers';
        } elseif ($userType === 'company') {
            $table = 'companies';
        }

        // Query the database based on user type
        if ($table) {
            $stmt = $conn->prepare("SELECT id, email, password FROM $table WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if user exists
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify the password
                if (password_verify($password, $user['password'])) {
                    // Successful login, set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $userType;

                    // Redirect based on user type
                    if ($userType === 'passenger') {
                        header("Location: passenger-dashboard.php");
                        exit();
                    } elseif ($userType === 'company') {
                        header("Location: company-dashboard.php");
                        exit();
                    }
                } else {
                    $error = 'Incorrect password.';
                }
            } else {
                $error = 'No user found with this email.';
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="form-box">
            <h1>Login</h1>
            <form action="" method="POST">
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <!-- Display Errors -->
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>

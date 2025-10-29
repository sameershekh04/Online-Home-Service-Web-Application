<?php
session_start();

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = "Sameer@123";
$db_name = 'services_app';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, fullName, password, status FROM service_providers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $provider = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $provider['password'])) {
            // Check if provider is verified
            if ($provider['status'] == 'verified') {
                // Set session variables
                $_SESSION['provider_id'] = $provider['id'];
                $_SESSION['provider_name'] = $provider['fullName'];
                $_SESSION['provider_logged_in'] = true;
                
                // Redirect to provider dashboard
                header("Location: provider_dashboard.php");
                exit();
            } else {
                // Provider not verified
                $_SESSION['login_error'] = "Your account is not verified yet. Please wait for admin approval.";
                header("Location: provider_login.php");
                exit();
            }
        } else {
            // Invalid password
            $_SESSION['login_error'] = "Invalid email or password";
            header("Location: provider_login.php");
            exit();
        }
    } else {
        // Provider not found
        $_SESSION['login_error'] = "Invalid email or password";
        header("Location: provider_login.php");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Login - SpitiCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5d3b66;
            --primary-light: #8e44ad;
            --secondary: #ff6b6b;
            --accent: #ff9f43;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --success: #10ac84;
            --warning: #ee5a24;
            --info: #0abde3;
            --text: #333;
            --text-light: #666;
            --bg-light: #ffffff;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            padding: 30px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        .login-header p {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: var(--transition);
        }
        .form-group input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(93, 59, 102, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-login:hover {
            background-color: var(--primary-light);
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .alert-danger {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--secondary);
            border-left: 4px solid var(--secondary);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-user-md"></i> SpitiCare</h1>
            <p>Provider Login</p>
        </div>
        
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="provider_login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div class="login-footer">
            <p>Don't have an account? <a href="serviceproviderhome.html">Register here</a></p>
        </div>
    </div>
</body>
</html>
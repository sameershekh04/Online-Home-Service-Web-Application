<?php
session_start();

// Fixed admin credentials
$admin_username = "admin";
$admin_password = "admin123";

$error = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - SpitiCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Poppins',sans-serif;
            background:linear-gradient(135deg,#e3f2fd,#bbdefb,#90caf9);
            background-size:400% 400%;
            animation:gradientBG 12s ease infinite;
            display:flex;flex-direction:column;
            min-height:100vh;color:var(--text);
            line-height:1.6;overflow-x:hidden;
        }
        @keyframes gradientBG{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}

        header{background:#fff;color:#000;padding:1rem 0;box-shadow:0 4px 20px rgba(0,0,0,.1);position:sticky;top:0;z-index:1000}
        .header-content{display:flex;justify-content:space-between;align-items:center;max-width:1200px;margin:0 auto;padding:0 20px}
        .logo{font-size:1.8rem;font-weight:700;display:flex;align-items:center;text-shadow:0 2px 4px rgba(0,0,0,.2)}
        .logo i{margin-right:10px;color:#ff6600;font-size:2.2rem}
        nav ul{display:flex;list-style:none}
        nav ul li{margin-left:25px}
        nav ul li a{color:#000;text-decoration:none;font-weight:500;transition:.3s;padding:8px 15px;border-radius:30px}
        nav ul li a:hover{color:#ff6600}

        .login-container{
            background:rgba(255,255,255,.95);padding:45px;border-radius:20px;text-align:center;
            max-width:550px;width:90%;margin:auto;margin-top:120px;color:#333;
            box-shadow:var(--shadow);animation:fadeIn 1s ease-in-out;position:relative;transition:var(--transition)
        }
        .login-container:hover{transform:translateY(-8px);box-shadow:var(--shadow-hover)}
        .login-container h1{font-size:32px;margin-bottom:30px;color:#004080}
        .form-group{margin-bottom:25px;text-align:left}
        .form-group label{display:block;margin-bottom:10px;font-weight:500;color:#333;font-size:16px}
        .form-group input{width:100%;padding:15px 18px;border-radius:10px;border:1px solid #ddd;font-size:16px;transition:.3s;background-color:#f9f9f9}
        .form-group input:focus{border-color:#004080;outline:none;box-shadow:0 0 10px rgba(0,64,128,.3);background-color:#fff}
        .login-btn{width:100%;padding:15px;background:#004080;color:#fff;border:none;border-radius:10px;font-size:18px;font-weight:500;cursor:pointer;transition:.3s;margin-top:15px}
        .login-btn:hover{background:#002a66;transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,64,128,.4)}
        .error-message{color:#e74c3c;margin-bottom:20px;padding:12px;border-radius:8px;background-color:#fdecea;font-size:15px}
        .back-link{margin-top:20px;display:block;color:#004080;text-decoration:none;font-weight:500;font-size:16px}
        .back-link:hover{text-decoration:underline}
        @keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    </style>
</head>
<body>
    <!-- Navbar -->
    <header>
        <div class="header-content">
            <div class="logo"><i class="fas fa-home"></i>SpitiCare</div>
            <nav id="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="provider_login.php">Provider Login</a></li>
                    <li><a href="admin_login.php">Admin Login</a></li>
                </ul>
            </nav>                 
        </div>
    </header>
    
    <!-- Login Container -->
    <div class="login-container">
        <h1>Admin Login</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form id="adminLoginForm" method="post" action="" autocomplete="off">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <a href="index.php" class="back-link">Back to Home</a>
    </div>
</body>
</html>

<?php
session_start();
// अगर यूजर लॉग इन नहीं है तो लॉगिन पेज पर भेजें
if (!isset($_SESSION['username'])) {
    header("Location: user-login1.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - SpitiCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #FF6B35, #000000);
            color: white;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }
        
        /* Background Pattern */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle, rgba(255, 107, 53, 0.2) 1px, transparent 1px);
            background-size: 20px 20px;
            z-index: 1;
        }
        
        .splash-container {
            text-align: center;
            animation: fadeIn 1.5s ease-in-out;
            max-width: 700px;
            padding: 0 20px;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* Logo */
        .logo {
            font-size: 3.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            animation: scaleIn 1.2s ease-in-out;
        }
        
        .logo i {
            margin-right: 15px;
            color: #FF6B35;
            font-size: 4rem;
            text-shadow: 0 0 15px rgba(255, 107, 53, 0.7);
        }
        
        .welcome-text {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            animation: slideInUp 1s ease-in-out;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.4);
            position: relative;
            display: inline-block;
        }
        
        .welcome-text::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: #FF6B35;
            border-radius: 2px;
        }
        
        .user-greeting {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 30px;
            animation: slideInUp 1s ease-in-out 0.3s forwards;
            opacity: 0;
            background: rgba(0, 0, 0, 0.4);
            display: inline-block;
            padding: 10px 25px;
            border-radius: 50px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 107, 53, 0.3);
        }
        
        .tagline {
            font-size: 1.4rem;
            margin-bottom: 50px;
            animation: slideInUp 1s ease-in-out 0.6s forwards;
            opacity: 0;
            font-weight: 300;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .services-preview {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-top: 20px;
            animation: slideInUp 1s ease-in-out 0.9s forwards;
            opacity: 0;
        }
        
        .service-icon {
            background: rgba(0, 0, 0, 0.4);
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            transition: all 0.4s ease;
            backdrop-filter: blur(5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 107, 53, 0.3);
        }
        
        .service-icon::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 107, 53, 0.2);
            transform: scale(0);
            border-radius: 50%;
            transition: all 0.5s ease;
        }
        
        .service-icon:hover {
            transform: scale(1.1);
            background: rgba(255, 107, 53, 0.3);
            border-color: rgba(255, 107, 53, 0.6);
        }
        
        .service-icon:hover::before {
            transform: scale(1);
        }
        
        .progress-container {
            position: absolute;
            bottom: 80px;
            width: 250px;
            height: 6px;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 3px;
            overflow: hidden;
            z-index: 2;
            border: 1px solid rgba(255, 107, 53, 0.3);
        }
        
        .progress-bar {
            height: 100%;
            width: 0;
            background: #FF6B35;
            border-radius: 3px;
            animation: progress 3s ease-in-out forwards;
            box-shadow: 0 0 10px rgba(255, 107, 53, 0.7);
        }
        
        .skip-button {
            position: absolute;
            bottom: 30px;
            right: 30px;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #FF6B35;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 2;
            backdrop-filter: blur(5px);
        }
        
        .skip-button:hover {
            background: rgba(255, 107, 53, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        /* Floating particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: #FF6B35;
            border-radius: 50%;
            opacity: 0.3;
            animation: float 15s infinite linear;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes slideInUp {
            from { 
                transform: translateY(50px); 
                opacity: 0; 
            }
            to { 
                transform: translateY(0); 
                opacity: 1; 
            }
        }
        
        @keyframes progress {
            from { width: 0; }
            to { width: 100%; }
        }
        
        @keyframes float {
            from {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            to {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }
        
        @media (max-width: 768px) {
            .logo {
                font-size: 2.8rem;
                margin-bottom: 30px;
            }
            
            .logo i {
                font-size: 3.2rem;
            }
            
            .welcome-text {
                font-size: 2rem;
            }
            
            .user-greeting {
                font-size: 1.6rem;
            }
            
            .tagline {
                font-size: 1.2rem;
            }
            
            .services-preview {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .service-icon {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            
            .progress-container {
                width: 200px;
                bottom: 70px;
            }
            
            .skip-button {
                bottom: 20px;
                right: 20px;
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating particles -->
    <div class="particles" id="particles"></div>
    
    <div class="splash-container">
        <div class="logo">
                       SpitiCare
        </div>
        
        <h1 class="welcome-text">Welcome to SpitiCare</h1>
        <p class="user-greeting">Hi, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>
        <p class="tagline">Your trusted partner for all home services</p>
        
        <div class="services-preview">
            <div class="service-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="service-icon">
                <i class="fas fa-wrench"></i>
            </div>
            <div class="service-icon">
                <i class="fas fa-broom"></i>
            </div>
            <div class="service-icon">
                <i class="fas fa-paint-roller"></i>
            </div>
        </div>
    </div>
    
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
    
    <button class="skip-button" onclick="redirectToHome()">Skip</button>
    
    <script>
        // Create floating particles
        const particlesContainer = document.getElementById('particles');
        const particleCount = 30;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            
            // Random size between 3px and 8px
            const size = Math.random() * 5 + 3;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            
            // Random position
            particle.style.left = `${Math.random() * 100}%`;
            
            // Random animation delay
            particle.style.animationDelay = `${Math.random() * 15}s`;
            
            // Random animation duration
            particle.style.animationDuration = `${Math.random() * 10 + 15}s`;
            
            particlesContainer.appendChild(particle);
        }
        
        // 3 सेकंड के बाद होम पेज पर रीडायरेक्ट करें
        setTimeout(() => {
            redirectToHome();
        }, 3000);
        
        function redirectToHome() {
            window.location.href = "index.php";
        }
    </script>
</body>
</html>
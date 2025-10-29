<?php
session_start();

if (!isset($_SESSION['total_amount'])) {
    echo "❌ No amount found in session.";
    exit;
}
$total_amount = $_SESSION['total_amount'];
?>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payment - SpitiCare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></link>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #000000;
      --secondary-color: #333333;
      --accent-color: #FF6600;
      --accent-2: #FF9933;
      --text-color: #333333;
      --text-light: #666666;
      --light-gray: #f8f9fa;
      --medium-gray: #e2e8f0;
      --dark-gray: #4a5568;
      --white: #ffffff;
      --gradient-1: linear-gradient(135deg, #000000 0%, #333333 100%);
      --gradient-2: linear-gradient(135deg, #FF6600 0%, #FF9933 100%);
      --gradient-3: linear-gradient(135deg, #FF6600 0%, #FF9933 100%);
      --gradient-4: linear-gradient(135deg, #333333 0%, #000000 100%);
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
      --transition: all 0.3s ease;
    }
    
    body { 
      background-color: #f5f5f5; 
      font-family: 'Poppins', sans-serif;
      color: var(--text-color);
      overflow-x: hidden;
    }
    
    .payment-card {
      background: var(--white);
      padding: 2.5rem;
      border-radius: 1.5rem;
      box-shadow: var(--shadow);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }
    
    .payment-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: var(--gradient-2);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.5s ease;
    }
    
    .payment-card:hover::before {
      transform: scaleX(1);
    }
    
    .payment-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }
    
    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .logo i {
      margin-right: 10px;
      color: var(--accent-color);
      font-size: 2.2rem;
    }
    
    nav {
      background: var(--white);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }
    
    .btn-primary {
      background: var(--gradient-1);
      color: var(--white);
      transition: var(--transition);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
    }
    
    .btn-danger {
      background: var(--gradient-2);
      color: var(--white);
      transition: var(--transition);
      box-shadow: 0 4px 10px rgba(255, 102, 0, 0.3);
    }
    
    .btn-danger:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(255, 102, 0, 0.4);
    }
    
    .btn-success {
      background: var(--gradient-2);
      color: var(--white);
      transition: var(--transition);
      box-shadow: 0 4px 10px rgba(255, 102, 0, 0.3);
    }
    
    .btn-success:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(255, 102, 0, 0.4);
    }
    
    .card {
      background: var(--white);
      border-radius: 1.5rem;
      box-shadow: var(--shadow);
      transition: var(--transition);
      overflow: hidden;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }
    
    .card-header {
      background: var(--gradient-1);
      color: var(--white);
      padding: 1rem 1.5rem;
      font-weight: 600;
    }
    
    .cart-item {
      padding: 1rem;
      border-radius: 0.75rem;
      background: var(--light-gray);
      transition: var(--transition);
      border-left: 4px solid transparent;
    }
    
    .cart-item:hover {
      background: #f0f0f0;
      border-left-color: var(--accent-color);
      transform: translateX(5px);
    }
    
    .booking-detail {
      padding: 0.75rem;
      border-radius: 0.5rem;
      background: var(--light-gray);
      margin-bottom: 0.5rem;
      transition: var(--transition);
    }
    
    .booking-detail:hover {
      background: #f0f0f0;
      transform: translateX(5px);
    }
    
    .bg-animation {
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: -1;
      opacity: 0.05;
    }
    
    .bg-animation span {
      position: absolute;
      display: block;
      list-style: none;
      width: 20px;
      height: 20px;
      background: var(--primary-color);
      animation: animate 25s linear infinite;
      bottom: -150px;
      border-radius: 50%;
    }
    
    .bg-animation span:nth-child(1) {
      left: 25%;
      width: 80px;
      height: 80px;
      animation-delay: 0s;
      background: var(--gradient-1);
    }
    
    .bg-animation span:nth-child(2) {
      left: 10%;
      width: 20px;
      height: 20px;
      animation-delay: 2s;
      animation-duration: 12s;
      background: var(--gradient-2);
    }
    
    .bg-animation span:nth-child(3) {
      left: 70%;
      width: 20px;
      height: 20px;
      animation-delay: 4s;
      background: var(--gradient-3);
    }
    
    .bg-animation span:nth-child(4) {
      left: 40%;
      width: 60px;
      height: 60px;
      animation-delay: 0s;
      animation-duration: 18s;
      background: var(--gradient-4);
    }
    
    @keyframes animate {
      0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
      }
      100% {
        transform: translateY(-1000px) rotate(720deg);
        opacity: 0;
      }
    }
    
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(0, 0, 0, 0.3);
      border-radius: 50%;
      border-top-color: var(--primary-color);
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    #navbar {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      list-style: none;
      margin: 0;
      padding: 0;
    }
    
    .user-profile {
      position: relative;
      cursor: pointer;
    }
    
    .profile-icon {
      display: flex;
      align-items: center;
      font-size: 1.8rem;
      color: #ff6600;
      transition: transform 0.2s ease, color 0.2s ease;
    }
    
    .profile-icon:hover {
      transform: scale(1.1);
      color: #222;
    }
    
    .notification-dot {
      position: absolute;
      top: 0;
      right: -3px;
      width: 10px;
      height: 10px;
      background: #ff4444;
      border-radius: 50%;
      border: 2px solid #fff;
    }
    
    .profile-dropdown {
      position: absolute;
      top: 45px;
      right: 0;
      background: #fff;
      width: 220px;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.15);
      display: none;
      flex-direction: column;
      overflow: hidden;
      z-index: 1000;
      animation: fadeIn 0.25s ease-in-out;
    }
    
    .user-profile.show .profile-dropdown {
      display: flex;
    }
    
    .profile-header {
      background: linear-gradient(135deg, #ff6600, #222);
      color: #fff;
      padding: 12px;
      text-align: center;
    }
    
    .profile-header h3 {
      margin: 0;
      font-size: 1rem;
      font-weight: bold;
    }
    
    .profile-header p {
      margin: 2px 0 0;
      font-size: 0.8rem;
      opacity: 0.9;
    }
    
    .profile-dropdown a {
      padding: 12px 15px;
      font-size: 0.9rem;
      color: #333;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.2s, color 0.2s;
    }
    
    .profile-dropdown a:hover {
      background: #ffe6d5;
      color: #ff6600;
    }
    
    .logout-btn {
      border-top: 1px solid #eee;
      font-weight: bold;
      color: #ff4444 !important;
    }
    
    #login-link a {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      background: linear-gradient(135deg, #ff6600, #222);
      color: #fff;
      border-radius: 8px;
      font-size: 0.9rem;
      font-weight: bold;
      text-decoration: none;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    #login-link a:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">
  <!-- Animated Background -->
  <div class="bg-animation">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>
  
  <!-- Header -->
  <header class="relative z-10">
    <nav class="flex items-center justify-between px-4 md:px-6 py-4 shadow">
      <!-- Logo -->
      <div class="logo">
              SpitiCare
      </div>
      
      <!-- Navigation -->
      <ul id="navbar">
        <?php if (isset($_SESSION['username'])): ?>
          <li class="user-profile" id="user-profile">
            <div class="profile-icon" id="profile-icon">
              <i class="fas fa-user-circle"></i>
              <span class="notification-dot"></span>
            </div>
            <div class="profile-dropdown" id="profile-dropdown">
              <div class="profile-header">
                <h3><?= htmlspecialchars($_SESSION['username']) ?></h3>
                <p><?= htmlspecialchars($_SESSION['email'] ?? 'user@example.com') ?></p>
              </div>
              <a href="#"><i class="fas fa-home"></i> Home</a>
              <a href="#"><i class="fas fa-user"></i> My Profile</a>
              <a href="#"><i class="fas fa-cog"></i> Settings</a>
              <a href="#" class="logout-btn" id="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
              </a>
            </div>
          </li>
        <?php else: ?>
          <li id="login-link">
            <a href="user-login1.html">
              <i class="fas fa-sign-in-alt"></i> Login
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>
  
  <!-- Main Content -->
  <main class="flex-grow flex flex-col items-center justify-center py-8 px-4">
    <div class="payment-card w-full max-w-md mx-auto">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Complete Your Payment</h1>
        <p class="text-gray-600">Secure transaction powered by Razorpay</p>
      </div>
      
      <div class="bg-blue-50 rounded-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
          <span class="font-medium text-gray-700">Total Amount</span>
          <span class="text-2xl font-semibold text-orange-600">₹<?php echo $total_amount; ?></span>
        </div>
        <div class="border-t border-blue-100 pt-4">
          <div class="flex items-center text-sm text-gray-500">
            <i class="fas fa-shield-alt mr-2 text-green-500"></i>
            Your payment information is encrypted and secure
          </div>
        </div>
      </div>
      
      <button 
        id="payNow"
        class="btn-success w-full py-3 rounded-lg flex items-center justify-center gap-2"
      >
        <i class="fas fa-lock mr-2"></i>
        Pay ₹<?php echo $total_amount; ?> with Razorpay
      </button>
      
      <div class="mt-6 text-center text-xs text-gray-500">
        <p>By proceeding, you agree to our <a href="t&c.html" class="text-orange-500 hover:underline">Terms & Conditions</a></p>
      </div>
    </div>
  </main>
  
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script>
    document.getElementById("payNow").onclick = function(e){
      e.preventDefault();
      fetch("order.php", { method: "POST" })
      .then(res => res.json())
      .then(order => {
          if (!order.id) {
              alert("❌ Order create failed: " + JSON.stringify(order));
              return;
          }
          var options = {
              "key": "rzp_test_RER5sZtBiQx1eK", // 🔑 Replace with your Key ID
              "amount": order.amount,
              "currency": "INR",
              "name": "SpitiCare",
              "description": "Service Payment",
              "order_id": order.id,
              "handler": function (response){
                  fetch("verify.php", {
                      method: "POST",
                      headers: {"Content-Type":"application/json"},
                      body: JSON.stringify(response)
                  })
                  .then(res => res.json())
                  .then(data => {
                      if(data.status === "success"){
                          window.location.href = "confirmation.php";
                      } else {
                          alert("❌ Payment verification failed");
                      }
                  });
              },
              "theme": {"color": "#3399cc"}
          };
          var rzp = new Razorpay(options);
          rzp.open();
      })
      .catch(err => {
          alert("❌ Error: " + err);
      });
    };
  </script>
  
  <script>
    // Profile dropdown functionality
    document.addEventListener("DOMContentLoaded", () => {
      const profileIcon = document.getElementById("profile-icon");
      const userProfile = document.querySelector(".user-profile");
      
      if (profileIcon && userProfile) {
        profileIcon.addEventListener("click", (e) => {
          e.stopPropagation();
          userProfile.classList.toggle("show");
        });
        
        document.addEventListener("click", () => {
          userProfile.classList.remove("show");
        });
      }
    });
    
    // Logout functionality
    document.addEventListener("DOMContentLoaded", () => {
      const logoutBtn = document.getElementById("logout-btn");
      
      if (logoutBtn) {
        logoutBtn.addEventListener("click", function(e) {
          e.preventDefault();
          fetch("logout.php")
            .then(response => response.text())
            .then(data => {
              if (data.trim() === "success") {
                // Remove profile element
                const userProfile = document.getElementById("user-profile");
                if (userProfile) userProfile.remove();
                
                // Create login link
                const loginLi = document.createElement("li");
                loginLi.id = "login-link";
                loginLi.innerHTML = `
                  <a href="user-login1.html">
                    <i class="fas fa-sign-in-alt"></i> Login
                  </a>
                `;
                
                // Insert into navbar
                document.getElementById("navbar").appendChild(loginLi);
              }
            });
        });
      }
    });
  </script>
</body>
</html>
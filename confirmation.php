
<?php 
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Booking Confirmation - SpitiCare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #6d28d9;
      --secondary-color: #9333ea;
      --accent-color: #f43f5e;
      --accent-2: #f97316;
      --success-color: #10b981;
      --text-color: #1f2937;
      --light-gray: #f9fafb;
      --dark-gray: #374151;
      --white: #ffffff;
      --gradient-1: linear-gradient(135deg, #6d28d9, #9333ea);
      --gradient-2: linear-gradient(135deg, #f43f5e, #f97316);
      --gradient-3: linear-gradient(135deg, #3b82f6, #06b6d4);
      --gradient-4: linear-gradient(135deg, #22c55e, #10b981);
      --shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
      --shadow-hover: 0 12px 35px rgba(0, 0, 0, 0.18);
      --transition: all 0.3s ease;
    }
    body {
      background: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);
      font-family: 'Poppins', sans-serif;
      color: var(--text-color);
      min-height: 100vh;
      overflow-x: hidden;
    }
    /* Navbar */
    nav {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(12px);
      border-radius: 0 0 1rem 1rem;
      box-shadow: var(--shadow);
      transition: var(--transition);
    }
    nav h1 {
      font-weight: 700;
      letter-spacing: -0.5px;
    }
    /* Cards */
    .card {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 1.25rem;
      backdrop-filter: blur(12px);
      box-shadow: var(--shadow);
      transition: var(--transition);
      overflow: hidden;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }
    /* Success Icon */
    .success-icon {
      width: 100px;
      height: 100px;
      background: var(--gradient-4);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6);
      }
      70% {
        box-shadow: 0 0 0 25px rgba(34, 197, 94, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
      }
    }
    /* Buttons */
    .btn-primary, .btn-whatsapp, .btn-danger {
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      border-radius: 0.75rem;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    .btn-primary {
      background: var(--gradient-1);
      color: var(--white);
      box-shadow: 0 4px 12px rgba(109, 40, 217, 0.3);
    }
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(109, 40, 217, 0.4); }
    .btn-whatsapp {
      background: #25D366;
      color: var(--white);
      box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
    }
    .btn-whatsapp:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(37, 211, 102, 0.4); }
    .btn-danger {
      background: var(--gradient-2);
      color: var(--white);
      box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);
    }
    .btn-danger:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(244, 63, 94, 0.4); }
    /* Booking Details + Cart */
    .booking-detail, .cart-item {
      padding: 0.75rem;
      border-radius: 0.75rem;
      background: var(--light-gray);
      margin-bottom: 0.75rem;
      transition: var(--transition);
      border-left: 4px solid transparent;
    }
    .booking-detail:hover, .cart-item:hover {
      background: #f3f4f6;
      border-left-color: var(--primary-color);
      transform: translateX(5px);
    }
    /* Animated Background */
    .bg-animation {
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0; left: 0;
      z-index: -1;
      overflow: hidden;
      opacity: 0.08;
    }
    .bg-animation span {
      position: absolute;
      display: block;
      width: 20px; height: 20px;
      background: var(--primary-color);
      border-radius: 50%;
      animation: animate 20s linear infinite;
      bottom: -150px;
    }
    .bg-animation span:nth-child(1) { left: 25%; width: 80px; height: 80px; background: var(--gradient-1); animation-delay: 0s; }
    .bg-animation span:nth-child(2) { left: 10%; width: 30px; height: 30px; background: var(--gradient-2); animation-delay: 2s; animation-duration: 12s; }
    .bg-animation span:nth-child(3) { left: 70%; width: 25px; height: 25px; background: var(--gradient-3); animation-delay: 4s; }
    .bg-animation span:nth-child(4) { left: 40%; width: 60px; height: 60px; background: var(--gradient-4); animation-delay: 0s; animation-duration: 18s; }
    @keyframes animate {
      0% { transform: translateY(0) rotate(0deg); opacity: 1; }
      100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; }
    }
    /* Scrollbar */
    #addressContainer {
      max-height: 70vh;
      overflow-y: auto;
      padding-right: 8px;
    }
    #addressContainer::-webkit-scrollbar { width: 6px; }
    #addressContainer::-webkit-scrollbar-thumb {
      background: var(--gradient-1);
      border-radius: 10px;
    }
    /* Responsive */
    @media (max-width: 768px) {
      nav { border-radius: 0; }
      aside { position: relative !important; top: 0; left: 0; width: 100% !important; margin: 0 0 1rem 0 !important; }
      main { margin-left: 0 !important; }
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
      color: var(--primary-color);
      font-size: 2.2rem;
    }

  </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <!-- Animated Background -->
  <div class="bg-animation">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>
  
  <!-- Navbar -->
  <nav class="flex justify-between items-center px-6 py-4 shadow z-20 relative">
    <div class="flex items-center gap-2">
       <!-- Left: Logo -->
    <div class="logo">
  
     SpitiCare
    </div>
      <h1 class="absolute left-1/2 transform -translate-x-1/2 text-xl font-bold text-black">
    Booking Confirmed
  </h1>    </div>
    <div class="flex items-center gap-4">
     <div id="welcomeUser" class="flex items-center gap-2 bg-purple-100 px-3 py-1 rounded-full">
  <i class="fas fa-user-circle text-purple-600"></i>
  <span class="text-sm font-semibold text-purple-800">Welcome, <?php echo htmlspecialchars($username); ?></span>
</div>
      <button id="logoutBtn" onclick="logoutUser()" class="btn-danger px-4 py-2 rounded-lg hidden flex items-center gap-2">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </button>
    </div>
  </nav>
  
  <!-- Page Content Wrapper -->
  <div class="flex flex-col lg:flex-row relative pt-4">
    <!-- Booking Details Sidebar -->
    <aside class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 w-full lg:w-80 mx-4 mb-6 lg:mb-0 lg:fixed top-24 left-6 z-10">
      <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center space-x-2">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m2 0a2 2 0 100-4 2 2 0 000 4zM9 12a2 2 0 100-4 2 2 0 000 4zM3 12h.01M21 12h.01M4 16a2 2 0 100-4 2 2 0 000 4zM20 16a2 2 0 100-4 2 2 0 000 4z" />
        </svg>
        <span>Booking Details</span>
      </h2>
      <div id="addressContainer" class="space-y-2 text-sm text-gray-700 leading-relaxed">
        <!-- Filled by JS -->
      </div>
    </aside>
    
    <!-- Main Content -->
    <main class="flex-1 flex flex-col lg:ml-[22rem] p-4">
      <!-- Success Message -->
      <section class="bg-white rounded-2xl shadow-md p-6 text-center">
        <div class="mb-4">
          <div class="success-icon">
            <svg class="w-16 h-16 text-white animate-draw-check" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
              <path class="path" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold mt-4 text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-teal-500">Payment Successful!</h2>
          <p class="text-gray-600 mt-2 text-lg">Your service has been confirmed. We'll reach out to you shortly.</p>
        </div>
        
        <!-- Cart Summary -->
        <div class="text-left bg-gray-50 p-4 rounded-lg mt-6 shadow-sm text-sm text-gray-700 space-y-2">
          <h3 class="font-semibold text-lg text-gray-800 mb-2 flex items-center space-x-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707L17 14a4 4 0 01-4 0H7" />
            </svg>
            <span>Cart Summary</span>
          </h3>
          <div id="cart-items" class="space-y-1"></div>
          <hr />
          <div class="flex justify-between">
            <span><strong>Item Total:</strong></span>
            <span>₹<span id="item-total">0</span></span>
          </div>
          <div class="flex justify-between">
            <span><strong>Taxes (18%):</strong></span>
            <span>₹<span id="tax">0</span></span>
          </div>
          <div class="flex justify-between">
            <span><strong>Platform Charges:</strong></span>
            <span>₹69</span>
          </div>
          <hr />
          <div class="flex justify-between font-semibold text-gray-900">
            <span>Total Amount:</span>
            <span>₹<span id="total-amount">0</span></span>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
         
          <a href="index.php" class="btn-primary px-6 py-3 rounded-lg flex items-center justify-center gap-2">
            <i class="fas fa-home"></i>
            Back to Home
          </a>
        </div>
      </section>
    </main>
  </div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Fetch cart and booking info
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const bookingStored = JSON.parse(localStorage.getItem("bookingInfo")) || null;
    const username = localStorage.getItem("loggedInUser") || "Guest";

    if (!bookingStored || !bookingStored.data) {
        console.log("⚠️ No booking data found, skipping save/email.");
        return;
    }

    const booking = bookingStored.data;

    // 1️⃣ Display Booking Details
    const container = document.getElementById("addressContainer");
    if (container) {
        let html = "";

        if (cart.length > 0) {
            html += `<div class="mb-4">
                <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                    <i class="fas fa-list-ul text-orange-600"></i> Selected Service(s)
                </h3>
                <ul class="list-disc pl-5 space-y-1">`;
            cart.forEach(item => {
                html += `<li class="text-gray-700">${item.name} — ₹${item.price} x ${item.qty}</li>`;
            });
            html += `</ul></div>`;
        }

        html += `
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-map-marker-alt text-orange-600"></i> Location:</span> <span>${booking.detectedLocation || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-home text-orange-600"></i> Flat No.:</span> <span>${booking.flat || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-landmark text-orange-600"></i> Landmark:</span> <span>${booking.landmark || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-road text-orange-600"></i> Street:</span> <span>${booking.street || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-mail-bulk text-orange-600"></i> Pincode:</span> <span>${booking.pincode || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-calendar-alt text-orange-600"></i> Date:</span> <span>${booking.date || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-clock text-orange-600"></i> Time:</span> <span>${booking.time || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-phone text-orange-600"></i> Phone:</span> <span>${booking.phone || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-envelope text-orange-600"></i> Email:</span> <span>${booking.email || "Not provided"}</span></div>
<div class="booking-detail">
  <span class="font-medium flex items-center gap-2">
    <i class="fas fa-tools text-orange-600"></i>
    Service Type:
  </span>
  <span>${booking.serviceType || "Not specified"}</span>
</div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-comment-alt text-orange-600"></i> Instructions:</span> <span>${booking.instructions || "Not provided"}</span></div>
        <div class="booking-detail"><span class="font-medium flex items-center gap-2"><i class="fas fa-history text-orange-600"></i> Submitted At:</span> <span>${new Date(booking._timestamp || Date.now()).toLocaleString()}</span></div>
<div class="booking-detail">
  <span class="font-medium flex items-center gap-2">
    <i class="fas fa-tools text-orange-600"></i>
    Service Type:
  </span>
  <span>${booking.serviceType || "Not specified"}</span>
</div>

        `;
        container.innerHTML = html;
    }

    // 2️⃣ Calculate total correctly
    let itemTotal = 0;
    cart.forEach(item => {
        itemTotal += item.price * item.qty;
    });
    const tax = Math.round(itemTotal * 0.18);
    const totalAmount = itemTotal + tax + 69; // include platform charge

    // Update DOM totals if elements exist
    if (document.getElementById("item-total")) document.getElementById("item-total").textContent = itemTotal;
    if (document.getElementById("tax")) document.getElementById("tax").textContent = tax;
    if (document.getElementById("total-amount")) document.getElementById("total-amount").textContent = totalAmount;

    // 3️⃣ Prepare booking data for DB & Email
    const services = cart.length > 0 ? cart.map(item => `${item.name} - ₹${item.price} x ${item.qty}`).join(", ") : "No Service";

    const bookingData = {
        username: username,
        service_details: services,
detected_location: booking.detectedLocation || "",
        flat_no: booking.flat || "",
        street: booking.street || "",
        landmark: booking.landmark || "",
        pincode: booking.pincode || "",
        booking_date: booking.date || "",
        booking_time: booking.time || "",
        phone: booking.phone || "",
        email: booking.email || "",
       service_type: booking.serviceType || "Not specified", // ✅ added service_type

        instructions: booking.instructions || "",
        total_amount: totalAmount // ✅ now correct
   
    };

    // 4️⃣ Save booking to DB
    fetch("save_booking.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(bookingData)
    })
    .then(res => res.json())
    .then(data => {
        console.log("✅ Booking DB Response:", data);
        if (data.status !== "success") {
            console.error("❌ DB Save Error:", data.message);
        }
    })
    .catch(err => console.error("❌ DB Fetch Error:", err));

    // 5️⃣ Send email via SMTP
    fetch("send_email.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(bookingData)
    })
    .then(res => res.json())
    .then(data => {
        console.log("📧 Email Response:", data);
        if (data.status !== "success") {
            console.error("❌ Email Error:", data.message);
        }
    })
    .catch(err => console.error("❌ Email Fetch Error:", err));
});

</script>  
</body>
</html>
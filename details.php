
<?php
session_start();

// Retrieve service type from session
$serviceType = isset($_SESSION['service_type']) ? htmlspecialchars($_SESSION['service_type']) : '';

// If service type isn't set, redirect back to services page
if (empty($serviceType)) {
    header('Location: services.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Booking Details - SmartPoint</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.1.0/remixicon.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #000000;
      --secondary-color: #333333;
      --accent-color: #ffffff;
      --text-color: #333333;
      --text-light: #666666;
      --light-gray: #f8f9fa;
      --medium-gray: #e2e8f0;
      --dark-gray: #4a5568;
      --white: #ffffff;
      --gradient-1: linear-gradient(135deg, #000000 0%, #333333 100%);
      --gradient-2: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
      --gradient-3: linear-gradient(135deg, #333333 0%, #000000 100%);
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
    
    /* Navbar styling */
    nav {
      background: var(--white);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
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
    
    /* Button styling */
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
    
    /* Card styling */
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
    
    input:invalid { border-color: red; }
    input:valid { border-color: #22c55e; }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      nav {
        padding: 0.75rem 1rem;
      }
      
      .logo {
        font-size: 1.5rem;
      }
      
      .logo i {
        font-size: 1.8rem;
      }
      
      h1 {
        font-size: 1.2rem !important;
      }
    }
    
    @media (max-width: 480px) {
      .px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
      }
      
      .btn-primary {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">
  <!-- Navbar -->
  <nav class="flex items-center justify-between px-4 md:px-6 py-4 shadow relative z-10">
    <!-- Left: Logo -->
    <div class="logo">
  
     SpitiCare
    </div>
    
    <!-- Center: Title -->
    <h1 class="absolute left-1/2 transform -translate-x-1/2 text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-black to-gray-500">
      Booking Details
    </h1>
    
    <!-- Right: User info -->
    <div class="flex items-center gap-2 md:gap-4">
      <?php if (isset($_SESSION['username'])): ?>
        <div class="flex items-center gap-2 bg-gray-100 px-3 py-1 rounded-full">
          <i class="fas fa-user-circle text-gray-700"></i>
          <span class="text-sm font-semibold text-gray-800">
            <?= htmlspecialchars($_SESSION['username']) ?>
          </span>
        </div>
        <a href="logout.php" class="btn-primary px-3 py-2 rounded-lg flex items-center gap-2 text-sm">
          <i class="fas fa-sign-out-alt"></i>
          <span class="hidden sm:inline">Logout</span>
        </a>
      <?php endif; ?>
    </div>
  </nav>
  
  <!-- Page Content -->
  <div class="flex-grow flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-3xl bg-white shadow-xl rounded-2xl p-6 md:p-8 space-y-6">
      <h2 class="text-2xl font-bold text-center text-gray-800 flex items-center justify-center gap-2">
        <i class="fas fa-clipboard-list text-black"></i>
        Booking & Address Details
      </h2>
      <form id="bookingForm" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
        <!-- Location -->
        <div>
          <label class="block mb-1 font-medium">Detected Location 
            <button type="button" id="getLocation" class="text-sm text-black hover:underline">(Detect)</button>
          </label>
          <input type="text" id="detectedLocation" placeholder="E.g. Mumbai" class="w-full border rounded px-3 py-2" required />
        </div>
        <!-- House -->
        <div>
          <label class="block mb-1 font-medium">Flat / House No.</label>
          <input type="text" id="flat" placeholder="B-302, Silver Heights" class="w-full border rounded px-3 py-2" required />
        </div>
        <!-- Landmark -->
        <div>
          <label class="block mb-1 font-medium">Landmark</label>
          <input type="text" id="landmark" placeholder="Near Axis Bank" class="w-full border rounded px-3 py-2" />
        </div>
        <!-- Street -->
        <div>
          <label class="block mb-1 font-medium">Street / Area</label>
          <input type="text" id="street" placeholder="Andheri West" class="w-full border rounded px-3 py-2" required />
        </div>
        <!-- Pincode -->
        <div>
          <label class="block mb-1 font-medium">Pincode</label>
          <input type="text" id="pincode" placeholder="400053" pattern="\d{6}" title="Enter a valid 6-digit pincode" class="w-full border rounded px-3 py-2" required />
        </div>
        <!-- Date -->
        <div>
          <label class="block mb-1 font-medium">Booking Date</label>
          <input type="date" id="date" class="w-full border rounded px-3 py-2" required />
        </div>
        <!-- Time -->
        <div>
          <label class="block mb-1 font-medium">Service Time</label>
          <input type="time" id="time" class="w-full border rounded px-3 py-2" required />
        </div>
        <!-- Phone -->
        <div>
          <label class="block mb-1 font-medium">Phone Number</label>
          <input type="tel" id="phone" placeholder="9876543210" pattern="[0-9]{10}" title="Enter a valid 10-digit phone number" class="w-full border rounded px-3 py-2" required />
        </div>
        <!-- Email -->
        <div>
          <label class="block mb-1 font-medium">Email Address</label>
          <input type="email" id="email" placeholder="you@example.com" class="w-full border rounded px-3 py-2" required />
        </div>
 <!-- Service Type Column (NEW) -->
  <div class="md:col-span-2">
    <label class="block mb-1 font-medium">Service Type</label>
    <div class="bg-gray-100 p-3 rounded border border-gray-200">
      <?= htmlspecialchars($serviceType) ?>
    </div>
  </div>
  
        <!-- Instructions -->
        <div class="md:col-span-2">
          <label class="block mb-1 font-medium">Special Instructions</label>
          <textarea id="instructions" rows="3" placeholder="Any specific notes..." class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <!-- Submit -->
        <div class="md:col-span-2 text-center mt-4">
          <button type="submit" class="btn-primary w-full md:w-auto px-6 py-3 rounded-lg font-semibold flex items-center justify-center gap-2 mx-auto">
            <i class="fas fa-save"></i>
            Save & Continue to Cart
          </button>
        </div>
      </form>
    </div>
  </div>

<script>
// 1. Detect Location
document.getElementById("getLocation").addEventListener("click", async () => {
  // ✅ HTTPS or localhost check
  if (location.protocol !== "https:" && location.hostname !== "localhost") {
    alert("Location detection requires HTTPS or localhost.");
    return;
  }
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;
        console.log("Lat:", lat, "Lon:", lon);
        try {
          const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`, {
            headers: {
              'User-Agent': 'SmartPointApp/1.0',
              'Accept-Language': 'en'
            }
          });
          const data = await response.json();
          console.log("Location Data:", data);
          const locationName = data.address.city || data.address.town || data.address.village || data.address.state || data.address.country || "";
          document.getElementById("detectedLocation").value = locationName;
        } catch (error) {
          console.error(error);
          document.getElementById("detectedLocation").value = `${lat}, ${lon}`; // fallback
        }
      },
      (err) => {
        console.error(err);
        alert("Permission denied. Please enable location in browser settings.");
      }
    );
  } else {
    alert("Geolocation not supported by your browser.");
  }
});

// 2. Set min date to today
const today = new Date().toISOString().split("T")[0];
document.getElementById("date").setAttribute("min", today);

// 3. Form Submit Validation
document.getElementById("bookingForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const pincode = document.getElementById("pincode").value.trim();
  const phone = document.getElementById("phone").value.trim();
  const email = document.getElementById("email").value.trim();
  if (!/^\d{6}$/.test(pincode)) {
    alert("Enter a valid 6-digit pincode.");
    return;
  }
  if (!/^\d{10}$/.test(phone)) {
    alert("Enter a valid 10-digit phone number.");
    return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    alert("Enter a valid email address.");
    return;
  }
  // ✅ Save data to localStorage
const serviceType = "<?= htmlspecialchars($serviceType) ?>";
  const bookingData = {
    detectedLocation: document.getElementById("detectedLocation").value.trim(),
    flat: document.getElementById("flat").value.trim(),
    landmark: document.getElementById("landmark").value.trim(),
    street: document.getElementById("street").value.trim(),
    pincode,
    date: document.getElementById("date").value.trim(),
    time: document.getElementById("time").value.trim(),
    phone,
    email,
    serviceType,  // Add this line to include service type
    instructions: document.getElementById("instructions").value.trim(),
    _timestamp: new Date().getTime()
  };
  const payload = {
    data: bookingData,
    expiry: Date.now() + 60000
  };
  localStorage.setItem("bookingInfo", JSON.stringify(payload));
  // Redirect
  window.location.href = "electric1.php";
});

// Add some animation on page load
window.addEventListener('load', () => {
  const elements = document.querySelectorAll('.card');
  elements.forEach((el, index) => {
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'all 0.5s ease';
      
      setTimeout(() => {
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';
      }, 100);
    }, index * 100);
  });
});
</script>
</body>
</html>
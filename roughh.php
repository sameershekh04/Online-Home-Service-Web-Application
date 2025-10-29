<?php
require_once 'config.php';

// Get all services
$services = $pdo->query("SELECT * FROM services ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);

// Group services by category
$servicesByCategory = [];
foreach ($services as $service) {
    $servicesByCategory[$service['category']][] = $service;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #5d3b66;
      --secondary-color: #8e44ad;
      --accent-color: #ff6b6b;
      --accent-2: #ff9f43;
      --text-color: #2d3748;
      --light-gray: #f8f9fa;
      --medium-gray: #e2e8f0;
      --dark-gray: #4a5568;
      --white: #ffffff;
      --gradient-1: linear-gradient(135deg, #5d3b66 0%, #8e44ad 100%);
      --gradient-2: linear-gradient(135deg, #ff6b6b 0%, #ff9f43 100%);
      --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --gradient-4: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
      --transition: all 0.3s ease;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f5f7fa;
      color: var(--text-color);
      line-height: 1.6;
      overflow-x: hidden;
    }

    .container {
      display: flex;
      padding: 30px;
      gap: 20px;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* Header Styles */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 40px;
  background-color: #fff;  /* White background */
  color: #000;             /* Black text */
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  transition: all 0.3s ease;
  font-family: 'Poppins', sans-serif;
}

/* When scrolling */
.header.scrolled {
  padding: 10px 40px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

/* Header links */
.header a,
.header nav ul li a {
  color: #000 !important;  /* Black text */
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease, border-color 0.3s ease;
}

/* Hover effect */
.header a:hover {
  color: #ff6600;  /* Orange text on hover */
}

/* Optional underline animation for modern look */
.header nav ul li a {
  position: relative;
  padding-bottom: 3px;
}
.header nav ul li a::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: 0;
  width: 0%;
  height: 2px;
  background: #ff6600;
  transition: width 0.3s ease;
}
.header nav ul li a:hover::after {
  width: 100%;
}

    .left-section .logo img {
      height: 65px;
      width: 170px;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      border: 1px solid #e5e7eb;
      background-color: white;
      padding: 4px;
      transition: transform 0.3s ease;
    }

    .left-section .logo img:hover {
      transform: scale(1.05);
    }

    .center-section {
      flex-grow: 1;
      display: flex;
      justify-content: center;
    }

    .location-selector {
      display: flex;
      align-items: center;
      gap: 8px;
      background: white;
      padding: 10px 16px;
      border-radius: 30px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      cursor: pointer;
      transition: all 0.3s ease;
      border: 1px solid var(--medium-gray);
    }

    .location-selector:hover {
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
      transform: translateY(-2px);
    }

    #location-name {
      font-size: 15px;
      font-weight: 500;
      color: var(--text-color);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 200px;
    }

    .dropdown-arrow {
      font-size: 14px;
      color: var(--dark-gray);
      transition: transform 0.3s ease;
    }

    .location-selector:hover .dropdown-arrow {
      transform: rotate(180deg);
    }

    .right-section {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .nav-links {
      list-style: none;
      display: flex;
      gap: 25px;
      margin: 0;
      padding: 0;
    }

    .nav-links li a {
      text-decoration: none;
      color: var(--primary-color);
      font-weight: 600;
      font-size: 16px;
      position: relative;
      transition: color 0.3s ease;
    }

    .nav-links li a::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 2px;
      background: var(--gradient-2);
      transition: width 0.3s ease;
    }

    .nav-links li a:hover {
      color: var(--accent-color);
    }

    .nav-links li a:hover::after {
      width: 100%;
    }

    /* Main Content */
    .main-content {
      margin-left: 270px;
      padding: 20px;
      margin-top: 80px;
    }

    /* Left Panel */
    .left-panel {
      position: fixed;
      top: 100px;
      left: 20px;
      width: 250px;
      background-color: white;
      border-radius: 15px;
      padding: 20px;
      box-shadow: var(--shadow);
      z-index: 100;
      max-height: calc(100vh - 120px);
      overflow-y: auto;
      transition: all 0.3s ease;
    }

    .left-panel h3 {
      margin-bottom: 20px;
      font-size: 20px;
      color: var(--primary-color);
      font-weight: 600;
      text-align: center;
      position: relative;
      padding-bottom: 10px;
    }

    .left-panel h3::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 50px;
      height: 3px;
      background: var(--gradient-1);
      border-radius: 2px;
    }

    .service-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }

    .service-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 15px 10px;
      border-radius: 12px;
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .service-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--gradient-1);
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: -1;
    }

    .service-item:hover::before {
      opacity: 0.1;
    }

    .service-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .service-item img {
      width: 60px;
      height: 60px;
      object-fit: contain;
      border-radius: 10px;
      padding: 5px;
      background-color: var(--light-gray);
      transition: all 0.3s ease;
    }

    .service-item:hover img {
      transform: scale(1.1);
    }

    .service-item span {
      font-size: 14px;
      margin-top: 10px;
      font-weight: 500;
      color: var(--dark-gray);
      transition: color 0.3s ease;
    }

    .service-item:hover span {
      color: var(--primary-color);
    }

    /* Right Panel */
    .right-panel {
      flex-grow: 1;
    }

    .service-gridd {
      padding: 20px 0 30px;
    }

    .service-gridd h1 {
      font-size: 32px;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 10px;
      position: relative;
      display: inline-block;
    }

    .service-gridd h1::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 80px;
      height: 4px;
      background: var(--gradient-2);
      border-radius: 2px;
    }

    .banner {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(135deg, #fef1dc, #ffd8b1);
      border-radius: 15px;
      overflow: hidden;
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
    }

    .banner:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }

    .banner-text {
      max-width: 60%;
    }

    .banner-text h2 {
      font-size: 24px;
      margin-bottom: 10px;
      color: var(--text-color);
      font-weight: 600;
    }

    .banner-text .tag {
      display: inline-block;
      background-color: #1ea97c;
      color: white;
      font-size: 13px;
      padding: 5px 12px;
      border-radius: 20px;
      margin-bottom: 10px;
      font-weight: 500;
    }

    .banner img {
      width: 300px;
      height: 250px;
      border-radius: 10px;
      object-fit: cover;
      transition: all 0.3s ease;
    }

    .banner:hover img {
      transform: scale(1.05);
    }

    /* Service Section */
    .service-section {
      margin-bottom: 40px;
    }

    .service-section h1 {
      font-size: 28px;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--medium-gray);
    }

    /* Service Cards */
    .service-card {
      background: white;
      border: 1px solid var(--medium-gray);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .service-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: var(--gradient-1);
      transform: scaleY(0);
      transition: transform 0.3s ease;
    }

    .service-card:hover::before {
      transform: scaleY(1);
    }

    .service-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }

    .service-info {
      flex: 1;
      margin-right: 20px;
    }

    .service-info h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
      color: var(--text-color);
      margin-bottom: 8px;
    }

    .rating {
      color: #f39c12;
      font-size: 14px;
      margin: 5px 0;
      display: flex;
      align-items: center;
    }

    .rating i {
      margin-right: 3px;
    }

    .price {
      font-weight: 600;
      font-size: 16px;
      margin-bottom: 8px;
      color: var(--accent-color);
    }

    .desc {
      font-size: 14px;
      color: var(--dark-gray);
      margin-bottom: 8px;
    }

    .view-link {
      display: inline-block;
      font-size: 14px;
      color: var(--primary-color);
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .view-link:hover {
      color: var(--secondary-color);
      text-decoration: underline;
    }

    .service-image {
      text-align: center;
    }

    .service-image img {
      width: 70px;
      height: 70px;
      object-fit: contain;
      margin-bottom: 10px;
      border-radius: 10px;
      background-color: var(--light-gray);
      padding: 5px;
      transition: all 0.3s ease;
    }

    .service-card:hover .service-image img {
      transform: scale(1.1);
    }

    .add-button {
      padding: 10px 20px;
      background: var(--gradient-1);
      color: white;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      box-shadow: 0 4px 8px rgba(93, 59, 102, 0.3);
    }

    .add-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(93, 59, 102, 0.4);
    }

    .options {
      font-size: 12px;
      color: var(--dark-gray);
      margin-top: 5px;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.6);
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background-color: white;
      margin: 5% auto;
      padding: 30px;
      border-radius: 15px;
      width: 90%;
      max-width: 600px;
      position: relative;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from { transform: translateY(-50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .close-btn {
      position: absolute;
      right: 20px;
      top: 15px;
      font-size: 24px;
      font-weight: bold;
      color: var(--dark-gray);
      cursor: pointer;
      transition: all 0.3s ease;
      width: 35px;
      height: 35px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
    }

    .close-btn:hover {
      color: white;
      background-color: var(--accent-color);
      transform: rotate(90deg);
    }

    .modal-content h2 {
      margin-bottom: 10px;
      font-size: 24px;
      color: var(--primary-color);
      font-weight: 600;
    }

    .modal-content p {
      color: var(--dark-gray);
      margin-bottom: 20px;
      font-size: 16px;
    }

    .process-section {
      background-color: #f8f9fa;
      padding: 20px;
      margin-top: 20px;
      border-radius: 12px;
      border-left: 4px solid var(--primary-color);
    }

    .process-section h2 {
      font-size: 20px;
      margin-bottom: 15px;
      color: var(--primary-color);
    }

    .process-step {
      padding: 12px 0;
      border-bottom: 1px solid var(--medium-gray);
      color: var(--text-color);
    }

    .process-step:last-child {
      border-bottom: none;
    }

    .process-step strong {
      color: var(--primary-color);
      font-size: 16px;
      display: block;
      margin-bottom: 5px;
    }

    .modal-footer {
      text-align: center;
      margin-top: 25px;
    }

    .done-btn {
      background: var(--gradient-1);
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 25px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(93, 59, 102, 0.3);
    }

    .done-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(93, 59, 102, 0.4);
    }

    /* Cart Panel */
    .side-cart {
      position: fixed;
      top: 100px;
      right: 20px;
      width: 320px;
      z-index: 99;
    }

    #cartPanel {
      background-color: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
    }

    #cartPanel:hover {
      box-shadow: var(--shadow-hover);
    }

    #cartPanel h3 {
      font-size: 20px;
      margin-bottom: 15px;
      color: var(--primary-color);
      font-weight: 600;
      text-align: center;
      position: relative;
      padding-bottom: 10px;
    }

    #cartPanel h3::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 50px;
      height: 3px;
      background: var(--gradient-1);
      border-radius: 2px;
    }

    #cart-items {
      max-height: 200px;
      overflow-y: auto;
      margin-bottom: 15px;
      padding-right: 5px;
    }

    #cart-items::-webkit-scrollbar {
      width: 5px;
    }

    #cart-items::-webkit-scrollbar-track {
      background: var(--light-gray);
      border-radius: 10px;
    }

    #cart-items::-webkit-scrollbar-thumb {
      background: var(--gradient-1);
      border-radius: 10px;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      background: var(--light-gray);
      padding: 10px;
      border-radius: 8px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .cart-item:hover {
      background: #f0f0f0;
      transform: translateX(5px);
    }

    .cart-item button {
      background: transparent;
      border: none;
      color: var(--accent-color);
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .cart-item button:hover {
      transform: scale(1.2);
    }

    #cart-total {
      font-weight: 600;
      font-size: 16px;
      color: var(--primary-color);
      text-align: right;
      margin-bottom: 15px;
    }

    .cart-summary {
      margin-top: 15px;
      background-color: var(--light-gray);
      border-radius: 10px;
      padding: 15px;
      font-size: 14px;
      border: 1px solid var(--medium-gray);
    }

    .cart-summary p {
      margin: 8px 0;
      display: flex;
      justify-content: space-between;
    }

    .cart-summary hr {
      margin: 12px 0;
      border: none;
      border-top: 1px solid var(--medium-gray);
    }

    .cart-summary strong {
      font-weight: 600;
      color: var(--primary-color);
    }

    .view-cart-wrapper {
      text-align: center;
      margin-top: 20px;
    }

    .view-cart-btn {
      background: var(--gradient-2);
      color: white;
      padding: 12px 25px;
      font-size: 16px;
      border: none;
      border-radius: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 600;
    }

    .view-cart-btn:hover {
      background: var(--gradient-1);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
      .container {
        padding: 20px;
      }
      
      .left-panel {
        width: 220px;
      }
      
      .main-content {
        margin-left: 240px;
      }
      
      .banner img {
        width: 250px;
        height: 200px;
      }
    }

    @media (max-width: 992px) {
      .left-panel {
        position: relative;
        top: 0;
        left: 0;
        width: 100%;
        margin-bottom: 20px;
        max-height: none;
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .side-cart {
        position: relative;
        top: 0;
        right: 0;
        width: 100%;
        margin-top: 20px;
      }
      
      .service-grid {
        grid-template-columns: repeat(3, 1fr);
      }
      
      .banner {
        flex-direction: column;
        text-align: center;
      }
      
      .banner-text {
        max-width: 100%;
        margin-bottom: 20px;
      }
      
      .banner img {
        width: 100%;
        max-width: 400px;
        height: auto;
      }
    }

    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        padding: 15px 20px;
      }
      
      .header .left-section,
      .header .center-section,
      .header .right-section {
        width: 100%;
        justify-content: center;
        margin-bottom: 10px;
      }
      
      .nav-links {
        flex-wrap: wrap;
        justify-content: center;
      }
      
      .service-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .service-card {
        flex-direction: column;
        text-align: center;
      }
      
      .service-info {
        margin-right: 0;
        margin-bottom: 15px;
      }
    }

    @media (max-width: 576px) {
      .service-grid {
        grid-template-columns: 1fr;
      }
      
      .modal-content {
        width: 95%;
        padding: 20px;
      }
      
      .modal-content h2 {
        font-size: 20px;
      }
      
      .process-step {
        padding: 8px 0;
        font-size: 14px;
      }
      
      .done-btn {
        padding: 10px 20px;
        font-size: 14px;
      }
    }

    /* Highlight animation */
    .highlight {
      animation: highlight 2s ease;
    }

    @keyframes highlight {
      0% { background-color: rgba(93, 59, 102, 0.2); }
      100% { background-color: transparent; }
    }

    /* Loading animation */
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(93, 59, 102, 0.3);
      border-radius: 50%;
      border-top-color: var(--primary-color);
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
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
            color: var(--accent);
            font-size: 2.2rem;
        }

          </style>
</head>

<body>
    <!-- Keep your existing header -->
     <!-- Header -->
  <header class="header">
    <div class="left-section">
       <div class="logo">
                
                SpitiCare
            </div>
    </div>
    <div class="center-section">
      <div class="location-selector" onclick="detectLocation()">
        <label class="search-label">
          <img src="location.png" alt="Location Icon" style="width: 18px; margin-right: 6px;" />
        </label>
        <span id="location-name">Use Current Location</span>
        <span class="dropdown-arrow">▼</span>
      </div>
    </div>
    <div class="right-section">
      <nav>
        <ul class="nav-links">
          <li><a href="roughh.php">Home</a></li>
          <li><a href="services.html">Services</a></li>
                </ul>
      </nav>
    </div>
  </header>

    <div class="container">
        <div class="left-panel">
            <h3>Select a service</h3>
            <div class="service-grid">
                <!-- Service categories -->
                <div class="service-item">
                    <img src="1switch.png" alt="Switch & socket" class="service-icon" data-target="switch-socket" />
                    <span>Switch & socket</span>
                </div>
                <div class="service-item">
                    <img src="regular-ceiling-fan.png" alt="Fan" class="service-icon" data-target="fan"/>
                    <span>Fan</span>
                </div>
                <!-- Add other categories similarly -->
<div class="service-item">
          <img src="light.png" alt="Light" class="service-icon" data-target="Light"/>
          <span>Light</span>
        </div>
        <div class="service-item">
          <img src="wiring.png" alt="Wiring" class="service-icon" data-target="Wiring"/>
          <span>Wiring</span>
        </div>
        <div class="service-item">
          <img src="regular-doorbell.png" alt="Doorbell & Security" class="service-icon" data-target="Doorbell & Security"/>
          <span>Doorbell & Security</span>
        </div>
        <div class="service-item">
          <img src="two-switches.png" alt="MCB/fuse" class="service-icon" data-target="MCB/fuse"/>
          <span>MCB/fuse</span>
        </div>
        <div class="service-item">
          <img src="tv-installation.png" alt="Appliances" class="service-icon" data-target="Appliances"/>
          <span>Appliances</span>
        </div>
        <div class="service-item">
          <img src="electric-consultation.png" alt="Consultation" class="service-icon" data-target="Book a consultation"/>
          <span>Book a consultation</span>
        </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="right-panel">
                <div class="service-gridd">
                    <h1>Electrician</h1>
                </div>
                
                <div class="banner">
                    <!-- Keep your existing banner -->
<div class="banner-text">
            <div class="tag">Super saver</div>
            <h2>Affordable repairs starting at just ₹49</h2>
          </div>
          <img src="switch.png" alt="Switch Repair Image" />

                </div>
                
                <!-- Dynamic service sections -->
                <?php foreach ($servicesByCategory as $category => $categoryServices): ?>
                <section id="<?= $category ?>" class="service-section">
                    <h1><?= str_replace('_', ' ', $category) ?></h1>
                    
                    <?php foreach ($categoryServices as $service): ?>
                    <div class="service-card">
                        <div class="service-info">
                            <h2><?= $service['name'] ?></h2>
                            <div class="rating">
                                <?php 
                                // Display stars based on rating
                                $rating = $service['rating'];
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= floor($rating)) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i - 0.5 <= $rating) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                                <?= $rating ?> (<?= $service['reviews'] ?> reviews)
                            </div>
                            <div class="price">Starts at ₹<?= $service['price'] ?></div>
                            <?php if ($service['description']): ?>
                            <div class="desc"><?= $service['description'] ?></div>
                            <?php endif; ?>
                            <a href="#" class="view-link">View details</a>
                        </div>
                        <div class="service-image">
                            <img src="<?= $service['image_url'] ?: 'default.png' ?>" alt="<?= $service['name'] ?>">
                            <button class="add-button" onclick="openModal('modal<?= $service['id'] ?>')">Add</button>
                            <?php if ($service['options'] > 0): ?>
                            <div class="options"><?= $service['options'] ?> options</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Modal for this service -->
                    <div id="modal<?= $service['id'] ?>" class="modal">
                        <div class="modal-content">
                            <span class="close-btn" onclick="closeModal('modal<?= $service['id'] ?>')">×</span>
                            <h2><?= $service['name'] ?></h2>
                            <p>★ <?= $service['rating'] ?> (<?= $service['reviews'] ?> reviews)</p>
                            
                            <div class="process-section">
                                <h2>Our process</h2>
                                <div class="process-step" data-step="1">
                                    <strong>Inspection</strong><br>
                                    We inspect your switchboard & share a repair quote for approval
                                </div>
                                <div class="process-step" data-step="2">
                                    <strong>Quote approval</strong><br>
                                    You can approve the quote to proceed, or pay a visitation charge if declined
                                </div>
                                <div class="process-step" data-step="3">
                                    <strong>Repair & spare parts</strong><br>
                                    If needed, we will source spare parts from the local market
                                </div>
                                <div class="process-step" data-step="4">
                                    <strong>Replacement if needed</strong><br>
                                    We will replace the damaged switchboard if repair is not possible
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="done-btn" onclick="addToCart('<?= addslashes($service['name']) ?>', <?= $service['price'] ?>, 'modal<?= $service['id'] ?>')">Done</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </section>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Keep your existing cart panel -->
  <div class="side-cart">
      <div class="card" id="cartCard">
        <div id="cartPanel">
          <h3>Your Cart</h3>
          <div id="cart-items"></div>
          <div><strong>Total:</strong> <span id="cart-total">₹0</span></div>
          
          <!-- Cart Summary Section -->
          <div class="cart-summary" id="cartSummary" style="margin-top: 10px;">
            <hr>
            <p><strong>Item Total:</strong> ₹<span id="item-total">0</span></p>
            <p><strong>Taxes (18%):</strong> ₹<span id="tax">0</span></p>
            <p><strong>Platform Charges:</strong> ₹69</p>
            <hr>
            <p><strong>Total Amount:</strong> ₹<span id="total-amount">0</span></p>
          </div>

     <!-- View Cart Button -->
          <div class="view-cart-wrapper">
            <button onclick="goToCart()" class="view-cart-btn">🛒 View Cart</button>
          </div>
        </div>
      </div>
    </div>
  </div>

    
    <!-- Keep your existing JavaScript -->
 <script>
    // Modal functions
    function openModal(modalId) {
      document.getElementById(modalId).style.display = 'block';
    }
    
    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }
    
    // Close modal when clicking outside content
    window.onclick = function(event) {
      const modals = document.querySelectorAll('.modal');
      modals.forEach(modal => {
        if (event.target == modal) {
          modal.style.display = 'none';
        }
      });
    }
    
    // Service icon click handler
    document.querySelectorAll('.service-icon').forEach(icon => {
      icon.addEventListener('click', () => {
        const targetId = icon.getAttribute('data-target');
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
          // Scroll to the section smoothly
          targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
          // Highlight the section
          targetSection.classList.add('highlight');
          // Remove highlight after a few seconds
          setTimeout(() => {
            targetSection.classList.remove('highlight');
          }, 2000);
        }
      });
    });
    
    // Location detection
    function detectLocation() {
      const locationSpan = document.getElementById("location-name");
      locationSpan.textContent = "Detecting...";
      locationSpan.innerHTML = '<div class="loading"></div>';
      
      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
          async function (position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            try {
              const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&zoom=10&addressdetails=1`, {
                headers: {
                  'User-Agent': 'YourAppName/1.0 (your@email.com)',
                  'Referer': window.location.href
                }
              });
              if (!response.ok) {
                throw new Error("Failed to fetch location");
              }
              const data = await response.json();
              const { city, town, village, state, country } = data.address;
              // Prefer city/town/village then fallback
              const location = city || town || village || state || country || "Location unavailable";
              locationSpan.textContent = location;
            } catch (error) {
              console.error(error);
              locationSpan.textContent = "Unable to fetch location";
            }
          },
          function (error) {
            console.error("Geolocation error:", error);
            switch (error.code) {
              case error.PERMISSION_DENIED:
                locationSpan.textContent = "Permission denied";
                break;
              case error.POSITION_UNAVAILABLE:
                locationSpan.textContent = "Location unavailable";
                break;
              case error.TIMEOUT:
                locationSpan.textContent = "Location request timed out";
                break;
              default:
                locationSpan.textContent = "Unknown error";
            }
          },
          { enableHighAccuracy: true, timeout: 10000 }
        );
      } else {
        locationSpan.textContent = "Geolocation not supported";
      }
    }
    
    // Cart functionality
    let cart = [];
    let total = 0;
    
    function addToCart(serviceName, price, modalId) {
      // Check if item already exists in cart
      const existingItem = cart.find(item => item.name === serviceName);
      if (existingItem) {
        existingItem.qty += 1;
      } else {
        cart.push({ name: serviceName, price: price, qty: 1 });
      }
      updateCartUI();
      closeModal(modalId);
    }
    
    function updateCartUI() {
      const cartItems = document.getElementById('cart-items');
      cartItems.innerHTML = ""; // Clear previous items
      total = 0;
      cart.forEach(item => {
        const subTotal = item.price * item.qty;
        total += subTotal;
        const itemDiv = document.createElement('div');
        itemDiv.className = 'cart-item';
        itemDiv.innerHTML = `
          <span>${item.name} - ₹${item.price} × ${item.qty} = ₹${subTotal}</span>
          <button onclick="removeFromCart('${item.name}')">×</button>
        `;
        cartItems.appendChild(itemDiv);
      });
      document.getElementById('cart-total').textContent = `₹${total}`;
      document.getElementById('item-total').textContent = total;
      const tax = (total * 0.18).toFixed(2);
      document.getElementById('tax').textContent = tax;
      const finalAmount = (total + parseFloat(tax) + 69).toFixed(2);
      document.getElementById('total-amount').textContent = finalAmount;
      document.getElementById('cartSummary').style.display = 'block';
      // Save cart to localStorage
      localStorage.setItem("cart", JSON.stringify(cart));
    }
    
    function removeFromCart(serviceName) {
      cart = cart.filter(item => item.name !== serviceName);
      updateCartUI();
    }
    
    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }
    
    function goToCart() {
      // Save cart again (redundant safety)
      localStorage.setItem("cart", JSON.stringify(cart));
      window.location.href = "electric1.php";
    }
    
    // Header scroll effect
    window.addEventListener('scroll', function() {
      const header = document.querySelector('.header');
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
    
    // Load cart from localStorage on page load
    window.addEventListener('load', function() {
      const savedCart = localStorage.getItem("cart");
      if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartUI();
      }
    });


// Function to toggle service details
function toggleServiceDetails(cardElement) {
    let detailsContainer = cardElement.querySelector('.service-details');
    
    if (detailsContainer) {
        // Agar already hai to toggle karo
        if (detailsContainer.style.display === 'block') {
            detailsContainer.style.display = 'none';
        } else {
            detailsContainer.style.display = 'block';
        }
    } else {
        // Agar details container nahi hai to create karo
        const detailsHTML = `
            <div class="service-details" style="display: block; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; position: relative;">
                <!-- Close button -->
                <button class="close-details" style="position: absolute; top: 8px; right: 10px; border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
                
                <h3>Service Details</h3>
                <p><strong>Description:</strong> ${cardElement.querySelector('.desc').textContent}</p>
                <p><strong>Price Range:</strong> Starts at ₹${cardElement.querySelector('.price').textContent.replace('Starts at ₹', '')}</p>
                <p><strong>Rating:</strong> ${cardElement.querySelector('.rating').innerHTML}</p>
                <p><strong>Process:</strong></p>
                <ol style="padding-left: 20px; margin-top: 10px;">
                    <li>Initial inspection and diagnosis</li>
                    <li>Quotation and customer approval</li>
                    <li>Professional repair work</li>
                    <li>Quality testing and cleanup</li>
                </ol>
                <p><strong>Guarantee:</strong> All our electrical services come with a 30-day quality guarantee.</p>
            </div>
        `;
        cardElement.insertAdjacentHTML('beforeend', detailsHTML);

        // Close button event
        detailsContainer = cardElement.querySelector('.service-details');
        const closeBtn = detailsContainer.querySelector('.close-details');
        closeBtn.addEventListener('click', () => {
            detailsContainer.style.display = 'none';
        });
    }
}

// Add click event listeners to all "View details" links
document.addEventListener('DOMContentLoaded', function() {
    const viewDetailsLinks = document.querySelectorAll('.view-link');
    
    viewDetailsLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Find the parent service card
            const serviceCard = this.closest('.service-card');
            
            // Toggle the details
            toggleServiceDetails(serviceCard);
        });
    });
});

  </script>


</body>
</html>
<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpitiCare - Home Services</title>
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
            --bg-light: #ffffff; /* Changed to pure white */
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light); /* Now pure white */
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
/* ================= HEADER ================= */
header {
  background: #fff; /* White background */
  color: #000;      /* Black text */
  padding: 1rem 0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
  animation: slideDown 0.5s ease-out;
}
/* Smooth slide down animation */
@keyframes slideDown {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
/* Header Flex Content */
.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
/* Logo */
.logo {
  font-size: 1.8rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
.logo i {
  margin-right: 10px;
  color: #ff6600; /* Accent orange */
  font-size: 2.2rem;
}
/* ================= NAVIGATION ================= */
nav ul {
  display: flex;
  list-style: none;
}
nav ul li {
  margin-left: 25px;
}
nav ul li a {
  color: #000; /* Black text */
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease, background 0.3s ease;
  padding: 8px 15px;
  border-radius: 30px;
  position: relative;
  overflow: hidden;
}
nav ul li a:hover {
  color: #ff6600; /* Orange on hover */
}
/* Hover highlight background */
nav ul li a::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 0;
  height: 100%;
  background-color: rgba(255, 102, 0, 0.1); /* Light orange overlay */
  transition: width 0.3s ease;
  z-index: -1;
}
nav ul li a:hover::before {
  width: 100%;
}
/* ================= USER PROFILE ================= */
.user-profile {
  position: relative;
}
.profile-icon {
  display: flex;
  align-items: center;
  cursor: pointer;
  position: relative;
  color: #000; /* Black icon */
  font-size: 1.8rem;
  transition: transform 0.3s ease, color 0.3s ease;
}
.profile-icon:hover {
  transform: scale(1.1);
  color: #ff6600; /* Orange hover */
}
/* Notification Dot */
.notification-dot {
  position: absolute;
  top: 0;
  right: 0;
  width: 10px;
  height: 10px;
  background-color: #ff6600;
  border-radius: 50%;
  border: 2px solid #fff;
  animation: pulse 2s infinite;
}
/* Pulse animation */
@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 107, 107, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 107, 107, 0);
            }
        }
        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 220px;
            z-index: 1000;
            margin-top: 15px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
        }
        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .profile-dropdown .profile-header {
            padding: 20px;
           background: linear-gradient(135deg, #ff6600, #222);
            color: var(--white);
            border-radius: 12px 12px 0 0;
            text-align: center;
        }
        .profile-dropdown .profile-header h3 {
            margin-bottom: 5px;
            font-size: 1.2rem;
        }
        .profile-dropdown .profile-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .profile-dropdown a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text);
            text-decoration: none;
            transition: var(--transition);
        }
        .profile-dropdown a:hover {
            background-color: var(--bg-light);
        }
        .profile-dropdown a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            color: var(--text-light);
        }
        .profile-dropdown .logout-btn {
            color: var(--secondary);
            border-top: 1px solid #eee;
            font-weight: 500;
        }
        .profile-dropdown .logout-btn i {
            color: var(--secondary);
        }
        .profile-dropdown::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 20px;
            width: 16px;
            height: 16px;
            background: var(--white);
            transform: rotate(45deg);
            box-shadow: -3px -3px 5px rgba(0, 0, 0, 0.05);
            z-index: -1;
        }
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1556761175-5973dc0f32e7?ixlib=rb-4.0.3') center/cover;
            color: var(--white);
            text-align: center;
            padding: 120px 20px;
            margin-bottom: 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(255, 107, 107, 0.3), transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(93, 59, 102, 0.3), transparent 50%);
            z-index: 1;
        }
        .hero .container {
            position: relative;
            z-index: 2;
        }
        .hero h1 {
            font-size: 3.8rem;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 0.8s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .hero p {
            font-size: 1.5rem;
            max-width: 700px;
            margin: 0 auto 40px;
            animation: fadeInUp 0.8s ease-out 0.2s;
            animation-fill-mode: both;
        }
        .search-bar {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            animation: fadeInUp 0.8s ease-out 0.4s;
            animation-fill-mode: both;
        }
        .search-bar input {
            width: 100%;
            padding: 18px 60px 18px 25px;
            border-radius: 50px;
            border: none;
            font-size: 1.1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background-color: rgba(255, 255, 255, 0.9);
            transition: var(--transition);
        }
        .search-bar input:focus {
            outline: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            background-color: var(--white);
        }
        .search-bar button {
            position: absolute;
            right: 5px;
            top: 5px;
            background: var(--secondary);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            color: var(--white);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .search-bar button:hover {
            background: #ff5252;
            transform: scale(1.05);
        }
        /* Services Section */
        .services-section {
            padding: 18px 0;
            background-color: var(--white); /* Ensure white background */
        }
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        .section-title h2 {
            font-size: 3rem;
            color: var(--dark);
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            border-radius: 2px;
        }
        .section-title p {
            color: var(--text-light);
            max-width: 700px;
            margin: 25px auto 0;
            font-size: 1.2rem;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 40px;
        }
        .service-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .service-card:nth-child(1) { animation-delay: 0.1s; }
        .service-card:nth-child(2) { animation-delay: 0.2s; }
        .service-card:nth-child(3) { animation-delay: 0.3s; }
        .service-card:nth-child(4) { animation-delay: 0.4s; }
        .service-card:nth-child(5) { animation-delay: 0.5s; }
        .service-card:nth-child(6) { animation-delay: 0.6s; }
        .service-card:nth-child(7) { animation-delay: 0.7s; }
        .service-card:nth-child(8) { animation-delay: 0.8s; }
        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: var(--shadow-hover);
        }
        .service-icon {
            height: 250px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4.5rem;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }
        .service-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .service-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--primary), var(--primary-light));
            opacity: 0.9;
            z-index: -1;
            transition: var(--transition);
        }
        .service-card:hover .service-icon::before {
            opacity: 1;
            transform: scale(1.05);
        }
        .service-content {
            padding: 30px;
        }
        .service-content h3 {
            font-size: 1.7rem;
            margin-bottom: 15px;
            color: var(--dark);
        }
        .service-content p {
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .service-features {
            margin-bottom: 25px;
        }
        .service-features li {
            margin-bottom: 12px;
            list-style-type: none;
            display: flex;
            align-items: center;
        }
        .service-features li i {
            color: var(--success);
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .service-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary);
        }
        .book-btn {
            background: linear-gradient(135deg, var(--secondary), #ff5252);
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }
        .book-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
        }
        /* Service-specific colors */
        .electrician .service-icon::before { background: linear-gradient(45deg, #f39c12, #f1c40f); }
        .plumber .service-icon::before { background: linear-gradient(45deg, #3498db, #2980b9); }
        .cleaner .service-icon::before { background: linear-gradient(45deg, #2ecc71, #27ae60); }
        .painter .service-icon::before { background: linear-gradient(45deg, #9b59b6, #8e44ad); }
        .carpenter .service-icon::before { background: linear-gradient(45deg, #e67e22, #d35400); }
        .ac-repair .service-icon::before { background: linear-gradient(45deg, #1abc9c, #16a085); }
        .appliance .service-icon::before { background: linear-gradient(45deg, #34495e, #2c3e50); }
        .beauty .service-icon::before { background: linear-gradient(45deg, #e91e63, #ad1457); }
        /* Why Choose Us Section */
        .why-choose {
            background: #ffffff; /* Changed to pure white */
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        .why-choose::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(93, 59, 102, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(255, 107, 107, 0.05) 0%, transparent 20%);
            z-index: 0;
        }
        .why-choose .container {
            position: relative;
            z-index: 1;
        }
        .why-choose h2 {
            text-align: center;
            font-size: 3rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 50px;
            position: relative;
        }
        .why-choose h2::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            border-radius: 2px;
        }
        .features-grid {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        .feature-card {
            background: var(--white);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            width: 320px;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--primary-light));
            transition: var(--transition);
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        .feature-card:hover::before {
            width: 100%;
            opacity: 0.1;
        }
        .feature-icon {
            background-color: var(--accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 25px;
            font-size: 2rem;
            color: var(--white);
            box-shadow: 0 10px 20px rgba(255, 159, 67, 0.3);
            transition: var(--transition);
        }
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
        }
        .feature-card p {
            font-size: 1rem;
            color: var(--text-light);
            line-height: 1.6;
        }
        /* Safety Section */
        .safety-section {
            padding: 0px 0;
            background-color: var(--white); /* Ensure white background */
        }
        .safety-section .container h1 {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 50px;
            font-weight: bold;
            color: var(--dark);
        }
        .safety-section .container .highlight {
            color: var(--secondary);
        }
        .safety-content {
            display: flex;
            gap: 60px;
            align-items: center;
        }
        .safety-image {
            flex: 1;
            animation: fadeInLeft 0.8s ease-out;
        }
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .safety-image img {
            max-width: 100%;
            border-radius: 20px;
            box-shadow: var(--shadow-hover);
            transition: var(--transition);
        }
        .safety-image img:hover {
            transform: scale(1.02);
        }
        .safety-text {
            flex: 1;
            animation: fadeInRight 0.8s ease-out;
        }
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .safety-item {
            margin-bottom: 30px;
            padding: 25px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .safety-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .safety-item h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .safety-item h2 i {
            margin-right: 15px;
            color: var(--primary);
            font-size: 1.8rem;
        }
        .safety-item p {
            font-size: 1rem;
            color: var(--text-light);
            line-height: 1.6;
            margin-left: 40px;
        }
        /* Booking Steps Section */
        .booking-steps {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            padding: 80px 20px;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }
        .booking-steps::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 20%);
            z-index: 0;
        }
        .booking-steps .container {
            position: relative;
            z-index: 1;
        }
        .booking-steps h2 {
            text-align: center;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 50px;
            position: relative;
        }
        .booking-steps h2::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--white);
            border-radius: 2px;
        }
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        .step-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }
        .step-box:nth-child(1) { animation-delay: 0.1s; }
        .step-box:nth-child(2) { animation-delay: 0.2s; }
        .step-box:nth-child(3) { animation-delay: 0.3s; }
        .step-box:nth-child(4) { animation-delay: 0.4s; }
        .step-box:nth-child(5) { animation-delay: 0.5s; }
        .step-box:nth-child(6) { animation-delay: 0.6s; }
        .step-box:nth-child(7) { animation-delay: 0.7s; }
        .step-box:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.2);
        }
        .step-number {
            background: var(--accent);
            color: var(--white);
            font-size: 1.5rem;
            font-weight: bold;
            width: 60px;
            height: 60px;
            line-height: 60px;
            border-radius: 50%;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(255, 159, 67, 0.3);
            transition: var(--transition);
        }
        .step-box:hover .step-number {
            transform: scale(1.1) rotate(5deg);
        }
        .step-box p {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .step-box span {
            display: block;
            font-size: 0.9rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.8);
        }
        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: var(--white);
            padding: 70px 0 30px;
            font-family: 'Poppins', sans-serif;
            position: relative;
            overflow: hidden;
        }
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(93, 59, 102, 0.2) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(255, 107, 107, 0.2) 0%, transparent 20%);
            z-index: 0;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }
        .footer-brand {
            width: 100%;
            margin-bottom: 40px;
            text-align: center;
        }
        .footer-brand .logo {
            justify-content: center;
            margin-bottom: 20px;
        }
        .footer-sections {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            justify-content: space-between;
        }
        .footer-column {
            width: calc(25% - 20px);
            margin-bottom: 40px;
        }
        .footer-column h2 {
            font-size: 1.5rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
            color: var(--white);
        }
        .footer-column h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary);
        }
        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-column ul li {
            margin-bottom: 15px;
        }
        .footer-column ul li a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 1rem;
            transition: var(--transition);
            display: inline-block;
        }
        .footer-column ul li a:hover {
            color: var(--secondary);
            padding-left: 5px;
        }
        .social {
            text-align: center;
        }
        .social-icons {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .social-icons a {
            margin: 0 12px;
            display: inline-block;
            transition: var(--transition);
        }
        .social-icons a:hover {
            transform: translateY(-5px);
        }
        .social-icons img {
            width: 40px;
            height: 40px;
            filter: brightness(0) invert(1);
            transition: var(--transition);
        }
        .social-icons a:hover img {
            filter: brightness(0) invert(1) sepia(100%) saturate(10000%) hue-rotate(0deg);
        }
        .app-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .app-links a img {
            max-width: 150px;
            height: auto;
            transition: var(--transition);
            border-radius: 10px;
        }
        .app-links a img:hover {
            transform: scale(1.05);
        }
        .footer-bottom {
            max-width: 1200px;
            margin: 40px auto 0;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .footer-bottom p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin: 5px 0;
            line-height: 1.5;
        }
        /* Notification Styles */
        .notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--success);
            color: var(--white);
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
            z-index: 1000;
            display: flex;
            align-items: center;
            max-width: 400px;
        }
        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }
        .notification i {
            font-size: 1.5rem;
            margin-right: 15px;
        }
        /* No Results Styles */
        .no-results {
            text-align: center;
            padding: 60px 30px;
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            display: none;
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .no-results h3 {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 2rem;
        }
        .no-results p {
            color: var(--text-light);
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        .no-results button {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(93, 59, 102, 0.3);
        }
        .no-results button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(93, 59, 102, 0.4);
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #000;
            font-size: 1.8rem;
            cursor: pointer;
            position: relative;
            z-index: 1100;
        }
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .container {
                padding: 0 15px;
            }
            
            .hero h1 {
                font-size: 3.2rem;
            }
            
            .section-title h2 {
                font-size: 2.8rem;
            }
            
            .why-choose h2 {
                font-size: 2.8rem;
            }
            
            .safety-section .container h1 {
                font-size: 2.8rem;
            }
            
            .booking-steps h2 {
                font-size: 2.8rem;
            }
        }
        
        @media (max-width: 992px) {
            .footer-column {
                width: calc(50% - 20px);
            }
            
            .social {
                width: 100%;
                margin-top: 30px;
            }
            
            .hero h1 {
                font-size: 2.8rem;
            }
            
            .hero p {
                font-size: 1.3rem;
            }
            
            .section-title h2 {
                font-size: 2.5rem;
            }
            
            .services-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 30px;
            }
            
            .safety-content {
                gap: 40px;
            }
            
            .features-grid {
                gap: 30px;
            }
            
            .feature-card {
                width: 300px;
            }
        }
        
        @media (max-width: 768px) {
            /* Mobile Menu Button */
            .mobile-menu-btn {
                display: block;
            }
            
            /* Navigation */
            nav {
                position: fixed;
                top: 0;
                right: -300px;
                width: 300px;
                height: 100vh;
                background-color: #fff;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
                transition: right 0.3s ease;
                z-index: 1050;
                padding-top: 80px;
            }
            
            nav.active {
                right: 0;
            }
            
            nav ul {
                flex-direction: column;
                padding: 20px;
            }
            
            nav ul li {
                margin: 0 0 15px 0;
            }
            
            nav ul li a {
                display: block;
                padding: 12px 15px;
                border-radius: 8px;
            }
            
            /* Header */
            .header-content {
                padding: 0 15px;
            }
            
            .logo {
                font-size: 1.6rem;
            }
            
            .logo i {
                font-size: 2rem;
            }
            
            /* Hero Section */
            .hero {
                padding: 80px 15px 60px;
            }
            
            .hero h1 {
                font-size: 2.4rem;
                margin-bottom: 20px;
            }
            
            .hero p {
                font-size: 1.1rem;
                margin-bottom: 30px;
            }
            
            /* Sections */
            .section-title h2 {
                font-size: 2.2rem;
            }
            
            .section-title p {
                font-size: 1.1rem;
            }
            
            .why-choose h2 {
                font-size: 2.2rem;
            }
            
            .safety-section .container h1 {
                font-size: 2.2rem;
            }
            
            .booking-steps h2 {
                font-size: 2.2rem;
            }
            
            .safety-content {
                flex-direction: column;
                gap: 30px;
            }
            
            .features-grid {
                flex-direction: column;
                align-items: center;
            }
            
            .feature-card {
                width: 100%;
                max-width: 350px;
            }
            
            /* Services */
            .services-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .service-content {
                padding: 20px;
            }
            
            .service-content h3 {
                font-size: 1.5rem;
            }
            
            .service-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .book-btn {
                width: 100%;
                text-align: center;
            }
            
            /* Steps */
            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .step-box {
                padding: 20px 15px;
            }
            
            .step-number {
                width: 50px;
                height: 50px;
                line-height: 50px;
                font-size: 1.2rem;
            }
            
            .step-box p {
                font-size: 1rem;
            }
            
            /* How it works section */
            .content {
                flex-direction: column;
            }
            
            .image-box img {
                max-width: 100%;
            }
            
            .steps {
                width: 100%;
            }
            
            .step {
                padding: 12px 15px;
            }
            
            .step-number {
                width: 32px;
                height: 32px;
                line-height: 32px;
                font-size: 0.9rem;
            }
            
            .step p {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 576px) {
            /* Footer */
            .footer-column {
                width: 100%;
                margin-bottom: 30px;
            }
            
            .footer-brand {
                margin-bottom: 30px;
            }
            
            .footer-sections {
                flex-direction: column;
            }
            
            .social-icons {
                margin-bottom: 20px;
            }
            
            .app-links {
                flex-direction: column;
                align-items: center;
            }
            
            .app-links a img {
                max-width: 120px;
            }
            
            /* Hero Section */
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            /* Sections */
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .section-title p {
                font-size: 1rem;
            }
            
            .why-choose h2 {
                font-size: 1.8rem;
            }
            
            .safety-section .container h1 {
                font-size: 1.8rem;
            }
            
            .booking-steps h2 {
                font-size: 1.8rem;
            }
            
            /* Services */
            .services-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .service-icon {
                height: 200px;
            }
            
            /* Steps */
            .steps-grid {
                grid-template-columns: 1fr;
            }
            
            /* Safety Section */
            .safety-item {
                padding: 15px;
            }
            
            .safety-item h2 {
                font-size: 1.2rem;
            }
            
            .safety-item h2 i {
                font-size: 1.5rem;
                margin-right: 10px;
            }
            
            .safety-item p {
                margin-left: 30px;
                font-size: 0.9rem;
            }
            
            /* Notification */
            .notification {
                right: 15px;
                left: 15px;
                max-width: none;
            }
            
            /* SpitiCare Section */
            .spiticare-section h4 {
                font-size: 32px;
            }
            
            .spiticare-section h1 {
                font-size: 28px;
            }
            
            .spiticare-section p {
                font-size: 18px;
            }
            
            /* Section Heading */
            .section-heading {
                font-size: 28px;
            }
            
            /* How it works section */
            .step p {
                font-size: 1rem;
            }
            
            .step-number {
                width: 28px;
                height: 28px;
                line-height: 28px;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 400px) {
            .container {
                padding: 0 10px;
            }
            
            .header-content {
                padding: 0 10px;
            }
            
            .logo {
                font-size: 1.4rem;
            }
            
            .logo i {
                font-size: 1.8rem;
            }
            
            .hero {
                padding: 60px 10px 40px;
            }
            
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .hero p {
                font-size: 0.9rem;
            }
            
            .search-bar input {
                padding: 15px 50px 15px 20px;
                font-size: 1rem;
            }
            
            .search-bar button {
                width: 40px;
                height: 40px;
            }
            
            .section-title h2 {
                font-size: 1.6rem;
            }
            
            .service-content {
                padding: 15px;
            }
            
            .service-content h3 {
                font-size: 1.3rem;
            }
            
            .price {
                font-size: 1.2rem;
            }
            
            .book-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .footer-bottom p {
                font-size: 0.8rem;
            }
            
            .spiticare-section h4 {
                font-size: 28px;
            }
            
            .spiticare-section h1 {
                font-size: 24px;
            }
            
            .spiticare-section p {
                font-size: 16px;
            }
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
.spiticare-section {
    padding: 0px 20px;
    text-align: center;
    background-color: #fff;
  }
  .spiticare-section h1 {
    font-family: 'Algerian', serif;
    font-size: 22px;
    color: #2f0423;
    text-align: center;
    letter-spacing: 2px;
    margin-bottom: 20px;
  }
  .spiticare-section h4 {
    font-size: 42px;
    color: #000000;
    margin-bottom: 25px;
    font-family: 'Alkatra', cursive;
    letter-spacing: 1.5px;
    text-transform: uppercase;
  }
  .spiticare-section p {
    font-size: 22px;
    max-width: 800px;
    margin: 0 auto 30px auto;
    color: #000000;
    line-height: 1.6;
  }
.section {
  padding: 35px 20px;
  text-align: center;
  background-color: #fff;
  margin-bottom: 0;  /* Ensure no bottom gap */
}
.section-heading {
  text-align: center;
  font-size: 36px;
  color: #2f0423;
  font-family: 'Algerian', serif;
  letter-spacing: 2px;
  margin-bottom: 30px;
}
.section-heading .highlight {
  background-color: #007B8F; /* Highlight background */
  color: #000000; /* Black text */
  padding: 4px 8px;
  border-radius: 4px;
}
.center-heading {
  text-align: center;
  font-size: 32px;
  font-weight: bold;
  color: #2f0423;
  margin-top: 0;     /* Remove gap from top */
  margin-bottom: 20px;  /* Adjust as needed */
}
.container{
  border-radius: 0;
}
.social-section {
  text-align: center;
  padding: 20px;
}
.social-section h3 {
  font-size: 24px;
  margin-bottom: 10px;
  color: #333;
}
.social-icons {
  display: flex;
  justify-content: center;
  gap: 15px;
}
.social-icons a img {
  width: 30px;
  height: 30px;
  transition: transform 0.3s ease;
}
.social-icons a img:hover {
  transform: scale(1.2);
}
/* Booking Steps Section */
.how-it-works {
  background: #fff; /* White container */
  border-radius: 16px;
  padding: 2px 40px;
  max-width: 1200px;
  margin: 50px auto;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  font-family: 'Poppins', sans-serif;
  text-align: center;
}
/* Heading */
.section-heading {
  font-size: 2.5rem;
  margin-bottom: 40px;
  color: #111; /* Black text */
  font-weight: 700;
}
.section-heading .highlight {
  color: #ff6600; /* Orange highlight */
}
/* Flex Layout */
.content {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 40px;
}
/* Image Box */
.image-box {
  flex: 1;
  display: flex;
  justify-content: center;
}
.image-box img {
  max-width: 500px;
  border-radius: 12px;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}
/* Steps Container */
.steps {
  flex: 1.5;
  display: flex;
  flex-direction: column;
  gap: 20px;
  text-align: left;
}
/* Step Card */
.step {
  display: flex;
  align-items: center;
  background: #fdfdfd;
  padding: 15px 20px;
  border-radius: 12px;
  border: 1px solid #eee;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}
.step:hover {
  transform: translateX(6px);
  border-color: #ff6600;
  box-shadow: 0 6px 14px rgba(255, 102, 0, 0.2);
}
/* Step Number */
.step-number {
  background: #ff6600;
  color: #fff;
  font-weight: 600;
  font-size: 1rem;
  width: 36px;
  height: 36px;
  line-height: 36px;
  text-align: center;
  border-radius: 50%;
  margin-right: 15px;
  flex-shrink: 0;
  box-shadow: 0 3px 8px rgba(255, 102, 0, 0.4);
}
/* Step Text */
.step p {
  margin: 0;
  font-size: 1.5rem;
  color: #111; /* Black text */
}
.step p strong {
  color: #000; /* Orange for keywords */
  font-weight: 600;
}
/* Responsive Design */
@media (max-width: 900px) {
  .content {
    flex-direction: column;
    text-align: center;
  }
  .steps {
    align-items: center;
    text-align: left;
    width: 100%;
  }
  .step {
    max-width: 450px;
    width: 100%;
  }
}
/* Video Section Styles */
.video-section {
    padding: 80px 0;
    background-color: var(--white);
    position: relative;
}

.video-container {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.video-wrapper {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-hover);
    max-width: 900px;
    margin: 0 auto;
    background-color: #000;
}

.feature-video {
    width: 100%;
    height: auto;
    display: block;
    aspect-ratio: 16/9;
}

.video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.1) 100%);
    display: flex;
    align-items: flex-end;
    padding: 40px;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.video-wrapper:hover .video-overlay {
    opacity: 1;
}

.overlay-content {
    color: var(--white);
    max-width: 600px;
}

.overlay-content h2 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.overlay-content p {
    font-size: 1.2rem;
    margin-bottom: 25px;
    opacity: 0.9;
}

.cta-button {
    display: inline-block;
    background: linear-gradient(135deg, var(--secondary), #ff5252);
    color: var(--white);
    text-decoration: none;
    padding: 14px 30px;
    border-radius: 50px;
    font-weight: 600;
    transition: var(--transition);
    box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
}

.cta-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
}

.video-info {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.video-info h3 {
    font-size: 2rem;
    color: var(--dark);
    margin-bottom: 20px;
}

.video-info p {
    font-size: 1.1rem;
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 30px;
}

.video-features {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

.video-feature {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 25px;
    background-color: var(--bg-light);
    border-radius: 50px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: var(--transition);
}

.video-feature:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.video-feature i {
    color: var(--success);
    font-size: 1.2rem;
}

.video-feature span {
    font-weight: 500;
    color: var(--text);
}

/* Responsive styles for video section */
@media (max-width: 992px) {
    .overlay-content h2 {
        font-size: 2rem;
    }
    
    .overlay-content p {
        font-size: 1rem;
    }
    
    .video-info h3 {
        font-size: 1.8rem;
    }
    
    .video-features {
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .video-section {
        padding: 60px 0;
    }
    
    .video-container {
        gap: 30px;
    }
    
    .overlay-content {
        padding: 0 20px 40px;
    }
    
    .overlay-content h2 {
        font-size: 1.8rem;
    }
    
    .video-info h3 {
        font-size: 1.6rem;
    }
    
    .video-features {
        flex-direction: column;
        align-items: center;
    }
    
    .video-feature {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .video-section {
        padding: 40px 0;
    }
    
    .overlay-content h2 {
        font-size: 1.5rem;
    }
    
    .overlay-content p {
        font-size: 0.9rem;
    }
    
    .video-info h3 {
        font-size: 1.4rem;
    }
    
    .video-info p {
        font-size: 1rem;
    }
}
  .support-icon {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ff7a18, #af002d, #319197);
      color: white;
      font-size: 26px;
      text-decoration: none;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transition: all 0.3s ease-in-out;
margin: 0 40px;
    }

    .support-icon:hover {
      transform: scale(1.15) rotate(10deg);
      box-shadow: 0 6px 15px rgba(0,0,0,0.3);
    }


    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                
                SpitiCare
            </div>
<div class="center-section">
  <div class="location-selector" onclick="detectLocation()">
    <label class="search-label">
      <img src="location.png" alt="Location Icon" style="width: 18px; margin-right: 6px;" />
    </label>
    <!-- ✅ Default text -->
    <span id="location-name" style="color: black;">Use Current Location</span>
    <span class="dropdown-arrow">▼</span>
  </div>
</div>
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" id="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav id="main-nav">
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <?php if (isset($_SESSION['username'])): ?>
            <li class="user-profile">
                <div class="profile-icon" id="profile-icon">
   <i class="fas fa-user-circle"></i>
   <span class="notification-dot"></span>
</div>
<div class="profile-dropdown" id="profile-dropdown">
                    <div class="profile-header">
                        <h3><?= htmlspecialchars($_SESSION['username']) ?></h3>
                        <p><?= htmlspecialchars($_SESSION['email'] ?? 'user@example.com') ?></p>
                    </div>
                    <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                                     <a href="about.html"><!-- About Us Icon -->
<i class="fas fa-address-card"></i>   <!-- Replaces settings -->About Us</a>
                    <a href="#" class="logout-btn" id="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </li>
        <?php else: ?>
            <li id="login-link"><a href="user-login1.php">Login</a></li>
        <?php endif; ?>
<!-- Round Support Us Icon -->
  <a href="https://spiticare.app.n8n.cloud/webhook/79ccd402-58ba-4b97-ad6d-032fb320c5e9/chat" target="_blank" class="support-icon">
    <i class="fas fa-hands-helping"></i>
  </a>


    </ul>
</nav>                 </div>
    </header>
    <section class="hero">

        <div class="container">
            <h1>Professional Home Services</h1>
            <p style="font-size:25px; font-family:'Poppins', sans-serif; font-weight:300; color:#fff; line-height:1.3;">
                Book trusted professionals for all your 
                <span style="font-weight:700; background-color:var(--accent); color:#000; padding:0 8px; border-radius:4px;">home service needs</span>. 
                Quality service at your doorstep.
            </p>
           <div class="search-bar">
    <input type="text" id="search-input" placeholder="Search for a service...">
    <button id="search-button"><i class="fas fa-search"></i></button>
  </div>        </div>
    </section>
<div class="spiticare-section">
  <h4>Welcome to SpitiCare</h4>
  <h1>Quality Service at Your Doorstep!</h1>
  <p>SpitiCare provides reliable home services including appliance repair, plumbing, painting, cleaning, and more — all at your doorstep with trusted professionals.</p>
</div>
<!-- Video Section -->
<section class="video-section">
    <div class="container">
        <div class="video-container">
            <div class="video-wrapper">
                <video controls autoplay muted loop class="feature-video">
                    <source src="VideoSpt.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div class="video-overlay">
                    <div class="overlay-content">
                                         </div>
                </div>
            </div>
            <div class="video-info">
                <h3>Professional Home Services at Your Fingertips</h3>
                <p>At SpitiCare, we connect you with verified professionals who deliver exceptional service right at your doorstep. Our commitment to quality, safety, and customer satisfaction sets us apart.</p>
                <div class="video-features">
                    <div class="video-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Verified Professionals</span>
                    </div>
                    <div class="video-feature">
                        <i class="fas fa-clock"></i>
                        <span>On-time Service</span>
                    </div>
                    <div class="video-feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Safety Guaranteed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
    <section class="services-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>We offer a wide range of home services performed by certified professionals at your convenience.</p>
            </div>
            <div class="services-grid" id="services-grid">
                <!-- Electrician Service -->
<div class="service-card electrician">
    <div class="service-icon">
        <!-- Replace icon with image -->
        <img src="electrician.jpg" alt="Electrician" style="width:100%; height:100%; object-fit:cover;">
    </div>
    <div class="service-content">
        <h3>Electrician</h3>
        <p>Expert electrical repair and installation services for your home.</p>
        <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Wiring & Rewiring</li>
            <li><i class="fas fa-check-circle"></i> Switch & Socket Repair</li>
            <li><i class="fas fa-check-circle"></i> Light Fixture Installation</li>
        </ul>
        <div class="service-footer">
            <div class="price">₹299 onwards</div>
            <button class="book-btn" data-service="Electrician">Book Now</button>
        </div>
    </div>
</div>
                <!-- Plumber Service -->
                <div class="service-card plumber">
                    <div class="service-icon">
                       <!-- Replace icon with image -->
        <img src="plumbing.jpg" alt="Plumber" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="service-content">
                        <h3>Plumber</h3>
                        <p>Reliable plumbing solutions for all your water-related needs.</p>
                        <ul class="service-features">
                            <li><i class="fas fa-check-circle"></i> Tap & Faucet Repair</li>
                            <li><i class="fas fa-check-circle"></i> Drain Cleaning</li>
                            <li><i class="fas fa-check-circle"></i> Water Tank Installation</li>
                        </ul>
                        <div class="service-footer">
                            <div class="price">₹249 onwards</div>
                            <button class="book-btn" data-service="Plumber">Book Now</button>
                        </div>
                    </div>
                </div>
                <!-- Cleaner Service -->
                <div class="service-card cleaner">
                    <div class="service-icon">
                      <!-- Replace icon with image -->
        <img src="carpet.png" alt="Cleaning" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="service-content">
                        <h3>Home Cleaning</h3>
                        <p>Professional cleaning services for a spotless and hygienic home.</p>
                        <ul class="service-features">
                            <li><i class="fas fa-check-circle"></i> Deep Cleaning</li>
                            <li><i class="fas fa-check-circle"></i> Kitchen & Bathroom Cleaning</li>
                            <li><i class="fas fa-check-circle"></i> Post-Construction Cleaning</li>
                        </ul>
                        <div class="service-footer">
                            <div class="price">₹499 onwards</div>
                            <button class="book-btn" data-service="Home Cleaning">Book Now</button>
                        </div>
                    </div>
                </div>
                <!-- Painter Service -->
                <div class="service-card painter">
                    <div class="service-icon">
                        <!-- Replace icon with image -->
        <img src="painting.jpg" alt="Painting" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="service-content">
                        <h3>Painter</h3>
                        <p>Transform your space with our professional painting services.</p>
                        <ul class="service-features">
                            <li><i class="fas fa-check-circle"></i> Interior Painting</li>
                            <li><i class="fas fa-check-circle"></i> Exterior Painting</li>
                            <li><i class="fas fa-check-circle"></i> Texture Painting</li>
                        </ul>
                        <div class="service-footer">
                            <div class="price">₹15/sq.ft onwards</div>
                            <button class="book-btn" data-service="Painter">Book Now</button>
                        </div>
                    </div>
                </div>
                <!-- Carpenter Service -->
                <div class="service-card carpenter">
                    <div class="service-icon">
 <!-- Replace icon with image -->
        <img src="carpenter.png" alt="Carpenter" style="width:100%; height:100%; object-fit:cover;">
                                           </div>
                    <div class="service-content">
                        <h3>Carpenter</h3>
                        <p>Custom woodwork and furniture repair services.</p>
                        <ul class="service-features">
                            <li><i class="fas fa-check-circle"></i> Furniture Repair</li>
                            <li><i class="fas fa-check-circle"></i> Custom Cabinets</li>
                            <li><i class="fas fa-check-circle"></i> Door & Window Repair</li>
                        </ul>
                        <div class="service-footer">
                            <div class="price">₹349 onwards</div>
                            <button class="book-btn" data-service="Carpenter">Book Now</button>
                        </div>
                    </div>
                </div>
                <!-- Beauty Service -->
                <div class="service-card beauty">
                    <div class="service-icon">
                       <!-- Replace icon with image -->
        <img src="beautty.png" alt="Beauty and Wellness" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="service-content">
                        <h3>Beauty & Wellness</h3>
                        <p>Professional beauty services at your home.</p>
                        <ul class="service-features">
                            <li><i class="fas fa-check-circle"></i> Hair Styling</li>
                            <li><i class="fas fa-check-circle"></i> Facials & Skin Care</li>
                            <li><i class="fas fa-check-circle"></i> Massage Therapy</li>
                        </ul>
                        <div class="service-footer">
                            <div class="price">₹599 onwards</div>
                            <button class="book-btn" data-service="Beauty & Wellness">Book Now</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="no-results" id="no-results">
                <h3>No services found</h3>
                <p>Try searching with different keywords</p>
                <button id="reset-search">View All Services</button>
            </div>
        </div>
    </section>
    <!-- Why Choose Us Section -->
    <section class="why-choose">
        <div class="container">
            <h2>So, why choose us?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <h3>Fair prices</h3>
                    <p>Choose the best offer at your price</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3>Verified professionals</h3>
                    <p>Choose from trusted experts based on reviews and ratings</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3>Doorstep service</h3>
                    <p>Our professionals reach your home directly, delivering services at your preferred time and location.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Safety Section -->
    <section class="safety-section">
        <div class="container">
            <h1>We care about <span class="highlight">safety</span></h1>
            <div class="safety-content">
                <div class="safety-image">
                    <img src="download.jpeg" alt="Smiling person with shield" />
                </div>
                <div class="safety-text">
                    <div class="safety-item">
                        <h2><i class="fas fa-star"></i> Rating system</h2>
                        <p>We ask users to give us their honest feedback after each service. You can choose your service - providers based on the experience of previous services</p>
                    </div>
                    <div class="safety-item">
                        <h2><i class="fas fa-shield-alt"></i> Mandatory checks</h2>
                        <p>All service - providers must pass background check before working with Smart Point Hub</p>
                    </div>
                    <div class="safety-item">
                        <h2><i class="fas fa-exclamation-triangle"></i> Safety button</h2>
                        <p>Tap it to quickly contact the police or emergency services</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Booking Steps Section -->
    <section class="how-it-works">
    <h1 class="section-heading">How does <span class="highlight">it work?</span></h1>
    <div class="content">
      <div class="image-box">
        <img src="rating.png" alt="Process illustration">
      </div>
      <div class="steps">
        <div class="step">
          <div class="step-number">1</div>
          <p><strong>Login/Signup (Google/ Phone OTP)</strong></p>
        </div>
        <div class="step">
          <div class="step-number">2</div>
          <p><strong>Select services + Sub services</strong></p>
        </div>
        <div class="step">
          <div class="step-number">3</div>
          <p><strong>Upload Photos</strong></p>
        </div>
        <div class="step">
          <div class="step-number">4</div>
          <p><strong>Choose time slot</strong></p>
</div>
 <div class="step">
          <div class="step-number">5</div>
          <p><strong>Payment Option (UPI,card,Cash-on-services)</strong></p>
        </div>
<div class="step">
          <div class="step-number">6</div>
          <p><strong>After Payment : Booking Successfull</strong></p>
        </div>
      </div>
    </div>
  </section>
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <div class="logo">
                
                    SpitiCare
                </div>
            </div>
            <div class="footer-sections">
                <div class="footer-column">
                    <h2>Company</h2>
                    <ul>
                        <li><a href="about.html">About us</a></li>
                                         <li><a href="t&c.html">Terms & conditions</a></li>
                        <li><a href="privacy-policy.html">Privacy policy</a></li>
                        <li><a href="#">Anti-discrimination policy</a></li>
                        <li><a href="#">ESG Impact</a></li>
                                           </ul>
                </div>
                <div class="footer-column">
                    <h2>For customers</h2>
                    <ul>
                                                <li><a href="welcome.php">Categories near you</a></li>
                        <li><a href="contact-us.html">Contact us</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h2>For professionals</h2>
                    <ul>
                        <li><a href="serviceproviderhome.html">Register as a professional</a></li>
                    </ul>
                </div>
                <div class="footer-column social">
                    <h2>Social links</h2>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    <div class="app-links">
                        <a href="#"><img src="appStore.png" alt="App Store" /></a>
                        <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" /></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>* As on December 31, 2024</p>
            <p>© Copyright 2025 SpitiCare Solution . (formerly known as SpitiCare Technologies India Limited and SpitiCare Technologies India Limited) All rights reserved. | CIN: U74140DL24PTC274413</p>
        </div>
    </footer>
    <div class="notification" id="notification">
        <i class="fas fa-check-circle"></i> <span id="notification-message"></span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user is logged in (from localStorage)
            const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
            const userName = localStorage.getItem('userName') || 'John Doe';
            const userEmail = localStorage.getItem('userEmail') || 'john@example.com';
            
            // Update UI based on login status
            updateAuthUI(isLoggedIn, userName, userEmail);
            
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mainNav = document.getElementById('main-nav');
            
            mobileMenuBtn.addEventListener('click', function() {
                mainNav.classList.toggle('active');
                
                // Change icon based on menu state
                const icon = mobileMenuBtn.querySelector('i');
                if (mainNav.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
            
            // Close mobile menu when clicking on a link
            const navLinks = mainNav.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mainNav.classList.remove('active');
                    const icon = mobileMenuBtn.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                });
            });
            
            // Service booking functionality
            const bookButtons = document.querySelectorAll('.book-btn');
            const notification = document.getElementById('notification');
            const notificationMessage = document.getElementById('notification-message');
            
            bookButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const serviceName = this.getAttribute('data-service');
                    notificationMessage.textContent = `Booking request for ${serviceName} `;
                    
                    // Show notification
                    notification.classList.add('show');
                    
                    // Hide notification after 5 seconds
                    setTimeout(() => {
                        notification.classList.remove('show');
                    }, 5000);
                });
            });
            
            // Electrician specific redirect
            document.querySelector(".electrician .book-btn").addEventListener("click", function () {
    window.location.href = "rough.html?service=Electrician";
});
// Carpenter specific redirect
document.querySelector(".carpenter .book-btn").addEventListener("click", function () {
    window.location.href = "rough1.html?service=Carpenter";
});

            
            // Search functionality
            const searchInput = document.getElementById('search-input');
            const searchButton = document.getElementById('search-button');
            const serviceCards = document.querySelectorAll('.service-card');
            const servicesGrid = document.getElementById('services-grid');
            const noResults = document.getElementById('no-results');
            const resetButton = document.getElementById('reset-search');
            
            // Function to filter services based on search term
            function filterServices() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let hasResults = false;
                
                serviceCards.forEach(card => {
                    const serviceName = card.querySelector('h3').textContent.toLowerCase();
                    const serviceDescription = card.querySelector('p').textContent.toLowerCase();
                    
                    if (serviceName.includes(searchTerm) || serviceDescription.includes(searchTerm)) {
                        card.style.display = 'block';
                        hasResults = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Show or hide the "no results" message
                if (hasResults) {
                    servicesGrid.style.display = 'grid';
                    noResults.style.display = 'none';
                } else {
                    servicesGrid.style.display = 'none';
                    noResults.style.display = 'block';
                }
            }
            
            // Add event listeners for search
            searchInput.addEventListener('input', filterServices);
            searchButton.addEventListener('click', filterServices);
            
            // Allow pressing Enter key to trigger search
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    filterServices();
                }
            });
            
            // Reset search when clicking the reset button
            resetButton.addEventListener('click', function() {
                searchInput.value = '';
                filterServices();
            });
            
            // Profile dropdown functionality
            const profileIcon = document.getElementById('profile-icon');
            const profileDropdown = document.getElementById('profile-dropdown');
            
            // Toggle dropdown on icon click
            profileIcon.addEventListener('click', function (event) {
                event.stopPropagation(); // Prevent click from propagating to document
                profileDropdown.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function (event) {
                if (!profileIcon.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.classList.remove('show');
                }
            });
            
            // Logout functionality
            const logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    
                    // Show confirmation dialog
                    if (confirm("Are you sure you want to logout?")) {
                        // Make AJAX request to logout
                        fetch('logout.php', {
                            method: 'POST',
                            credentials: 'same-origin'
                        })
                        .then(response => {
                            if (response.ok) {
                                // Clear localStorage
                                localStorage.removeItem('isLoggedIn');
                                localStorage.removeItem('userName');
                                localStorage.removeItem('userEmail');
                                
                                // Show logout notification
                                notificationMessage.textContent = 'You have been successfully logged out!';
                                notification.classList.add('show');
                                
                                // Hide notification after 3 seconds
                                setTimeout(() => {
                                    notification.classList.remove('show');
                                }, 3000);
                                
                                // Update UI to show login link
                                const userProfile = document.querySelector('.user-profile');
                                const nav = document.querySelector('nav ul');
                                
                                // Remove the user profile li
                                userProfile.remove();
                                
                                // Create a new login link
                                const loginLi = document.createElement('li');
                                loginLi.id = 'login-link';
                                loginLi.innerHTML = '<a href="user-login1.php">Login</a>';
                                nav.appendChild(loginLi);
                            } else {
                                // Show error notification
                                notificationMessage.textContent = 'Logout failed. Please try again.';
                                notification.classList.add('show');
                                
                                // Hide notification after 3 seconds
                                setTimeout(() => {
                                    notification.classList.remove('show');
                                }, 3000);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Show error notification
                            notificationMessage.textContent = 'Logout failed. Please try again.';
                            notification.classList.add('show');
                            
                            // Hide notification after 3 seconds
                            setTimeout(() => {
                                notification.classList.remove('show');
                            }, 3000);
                        });
                    }
                });
            }
            
            // Function to update authentication UI
            function updateAuthUI(loggedIn, name = 'John Doe', email = 'john@example.com') {
                const loginLink = document.getElementById('login-link');
                const userProfile = document.getElementById('user-profile');
                const userNameElement = document.getElementById('user-name');
                const userEmailElement = document.getElementById('user-email');
                
                if (loggedIn) {
                    // Hide login link and show user profile
                    if (loginLink) loginLink.style.display = 'none';
                    if (userProfile) userProfile.style.display = 'block';
                    
                    // Update user info
                    if (userNameElement) userNameElement.textContent = name;
                    if (userEmailElement) userEmailElement.textContent = email;
                } else {
                    // Show login link and hide user profile
                    if (loginLink) loginLink.style.display = 'block';
                    if (userProfile) userProfile.style.display = 'none';
                }
            }
            
            // Check if redirected from login page with success parameter
            const urlParams = new URLSearchParams(window.location.search);
            const loginSuccess = urlParams.get('login');
            
            if (loginSuccess === 'success') {
                // Set login status in localStorage
                localStorage.setItem('isLoggedIn', 'true');
                
                // Get user info from URL parameters or use defaults
                const name = urlParams.get('name') || 'John Doe';
                const email = urlParams.get('email') || 'john@example.com';
                
                localStorage.setItem('userName', name);
                localStorage.setItem('userEmail', email);
                
                // Update UI
                updateAuthUI(true, name, email);
                
                // Show login success notification
                const loginNotification = document.createElement('div');
                loginNotification.className = 'notification show';
                loginNotification.innerHTML = '<i class="fas fa-check-circle"></i> Login successful! Welcome back, ' + name + '!';
                document.body.appendChild(loginNotification);
                
                // Hide notification after 3 seconds
                setTimeout(() => {
                    loginNotification.remove();
                }, 3000);
                
                // Clean URL parameters
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
        
        // Location detection
        function detectLocation() {
          const locationSpan = document.getElementById("location-name");
          locationSpan.textContent = "Detecting..."; 
          locationSpan.style.color = "black"; // ensure text visible
          if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
              async function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                try {
                  const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&zoom=10&addressdetails=1`
                  );
                  const data = await response.json();
                  const { city, town, village, state, country } = data.address;
                  const location = city || town || village || state || country || "Location unavailable";
                  locationSpan.textContent = location;
                  locationSpan.style.color = "black";
                } catch (error) {
                  locationSpan.textContent = "Unable to fetch location";
                  locationSpan.style.color = "black";
                }
              },
              function (error) {
                switch (error.code) {
                  case error.PERMISSION_DENIED:
                    locationSpan.textContent = "Permission denied";
                    break;
                  case error.POSITION_UNAVAILABLE:
                    locationSpan.textContent = "Location unavailable";
                    break;
                  case error.TIMEOUT:
                    locationSpan.textContent = "Request timed out";
                    break;
                  default:
                    locationSpan.textContent = "Unknown error";
                }
                locationSpan.style.color = "black";
              }
            );
          } else {
            locationSpan.textContent = "Geolocation not supported";
            locationSpan.style.color = "black";
          }
        }

const searchInput = document.getElementById("search-input");

    const fixedText = "Search for ";
    const suggestions = ["'electrician'", "'plumber'", "'Home Cleaning'", "'Painter'", "'Carpenter'","'Beauty & Wellness'"];
    let index = 0;       // current word index
    let charIndex = 0;   // character index in that word

    function typeWord() {
      if (charIndex < suggestions[index].length) {
        searchInput.setAttribute(
          "placeholder",
          fixedText + suggestions[index].substring(0, charIndex + 1)
        );
        charIndex++;
        setTimeout(typeWord, 120); // typing speed
      } else {
        // wait 2 sec then move to next word
        setTimeout(() => {
          charIndex = 0;
          index = (index + 1) % suggestions.length;
          typeWord();
        }, 2000);
      }
    }

    typeWord(); // start animation

document.addEventListener('DOMContentLoaded', function() {
    // Handle Book Now button clicks
    const bookButtons = document.querySelectorAll('.book-btn');
    
    bookButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            const serviceName = this.getAttribute('data-service');
            
            // Set session variable via AJAX
            fetch('set_service_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `service_type=${encodeURIComponent(serviceName)}`
            })
            .then(response => {
                if (response.ok) {
                    // Redirect to booking form
                    window.location.href = 'booking_form.php';
                } else {
                    alert('Failed to set service type. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});
    </script>
</body>
</html>
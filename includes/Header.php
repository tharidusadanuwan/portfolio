<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tharidu - Freelancer Portfolio</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="por_index.css">
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="container">
        <div class="logo">
            <a href="#"><span class="highlight">T</span>haridu</a>
        </div>
        <nav class="nav">
            <ul class="nav-list">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Skills and Education</a></li>
                <li><a href="#portfolio">Portfolio</a></li>
                <!--<li><a href="#testimonials">Testimonials</a></li>-->
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
        <div class="header-buttons">
            <a href="#contact" class="btn btn-primary">Contact Me</a>
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
                <!-- Add this inside the header-buttons div -->
<button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
    <i class="fas fa-moon"></i>
    <i class="fas fa-sun"></i>
</button>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <div class="container">
        <div class="mobile-menu-header">
            <div class="logo">
                <a href="#"><span class="highlight">John</span>Doe</a>
            </div>
            <button class="menu-close" id="menuClose">
                <i class="fas fa-times"></i>
            </button>
            <!-- Add this inside the mobile-menu-header div -->
<button class="theme-toggle mobile-theme-toggle" id="mobileThemeToggle" aria-label="Toggle dark mode">
    <i class="fas fa-moon"></i>
    <i class="fas fa-sun"></i>
</button>
        </div>
        <ul class="mobile-nav-list">
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#portfolio">Portfolio</a></li>
            <!--<li><a href="#testimonials">Testimonials</a></li> -->
            <li><a href="#contact">Contact</a></li>
        </ul>
        <a href="#contact" class="btn btn-primary mobile-cta">Hire Me</a>
    </div>
</div>
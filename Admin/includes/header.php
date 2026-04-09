<?php
// includes/header.php

// This file contains the HTML start, CSS, and opens the main-content wrapper.

// Configuration for colors (using your suggestions)
$primary_dark = '#03396c'; // Dark Blue
$primary_light = '#005b96'; // Medium Blue
$action_color = '#6F9F9C'; // Teal/Seafoam

// Determine the active link
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ====== VARIABLES & BASIC RESET ====== */
        :root {
            --primary-dark: <?php echo $primary_dark; ?>; 
            --primary-light: <?php echo $primary_light; ?>; 
            --action-color: <?php echo $action_color; ?>; 
            --action-hover: #5d8c89;
            --bg-main: #f4f6f8;
            --shadow-light: 0 4px 10px rgba(0,0,0,0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: var(--bg-main); }

        /* ====== SIDEBAR (RETAINED) ====== */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 30px 0;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            box-shadow: 2px 0 15px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        .sidebar h2 {
            text-align: center; font-weight: 700; font-size: 1.8rem; margin-bottom: 40px;
            letter-spacing: 1px; border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px; margin-inline: 20px;
            color: #fff;
        }
        .menu { list-style: none; padding: 0; }
        .menu a {
            display: flex; align-items: center; color: #fff; text-decoration: none;
            padding: 15px 25px; margin: 0 15px; border-radius: 8px; font-weight: 500;
            transition: all 0.3s ease;
        }
        .menu a:hover, .menu a.active { /* Active state handling */
            background: var(--action-color);
            transform: translateX(5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .menu a i { margin-right: 15px; font-size: 1.2rem; }
        .logout { text-align: center; margin-top: auto; padding: 20px; }
        .logout a {
            color: white; text-decoration: none; background: var(--action-color);
            padding: 10px 25px; border-radius: 8px; font-weight: 500;
            display: inline-block; transition: all 0.3s ease;
        }
        .logout a:hover { background: var(--action-hover); transform: scale(1.05); }

        /* ====== MAIN CONTENT & TOPBAR CSS ====== */
        .main-content { margin-left: 260px; padding: 30px; width: 100%; }
        .topbar {
            background: #fff; border-radius: 10px; padding: 15px 25px;
            box-shadow: var(--shadow-light); display: flex;
            justify-content: space-between; align-items: center;
        }
        .topbar h1 { color: #333; font-size: 24px; font-weight: 500; }
        .topbar span { color: var(--action-color); font-weight: 700; }
        
        /* Dashboard content specific styles */
        .dashboard-content, .project-table-container, .content-area {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow-light);
            margin-top: 30px;
        }
        /* ... (Include all other styles from your original dashboard.php) ... */
        .info-cards { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { padding: 30px; border-radius: 10px; box-shadow: var(--shadow-light); flex: 1; text-align: left; transition: transform 0.3s; border-left: 5px solid var(--primary-light); }
        .card:hover { transform: translateY(-5px); }
        .card h3 { font-size: 1rem; color: #777; text-transform: uppercase; margin-bottom: 10px; font-weight: 400; }
        .card .count { font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); }
        .card .icon { float: right; font-size: 3rem; color: var(--action-color); opacity: 0.5; }
        /* Project Table Styles */
        .project-table-container table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .project-table-container th, .project-table-container td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .project-table-container th { background-color: var(--primary-light); color: white; font-weight: 500; text-transform: uppercase; font-size: 0.9rem; }
        .project-table-container tr:nth-child(even) { background-color: #f8f8f8; }

        /* Responsive adjustments */
        @media (max-width: 992px) { .sidebar { width: 200px; } .main-content { margin-left: 200px; padding: 20px; } .info-cards { flex-wrap: wrap; } .card { flex: 1 1 calc(50% - 10px); } }
        @media (max-width: 768px) { .sidebar { display: none; } .main-content { margin-left: 0; padding: 20px; } .info-cards { flex-direction: column; } .card { flex: 1 1 100%; } }
    </style>
</head>
<body>



<div class="main-content">
    <div class="topbar">
        <h1><?php echo ucwords(str_replace(".php", "", $current_page)); ?></h1>
        <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
    </div>


<?php
// includes/sidebar.php

// Determine the active link based on the current page file name
$current_page = basename($_SERVER['PHP_SELF']); 

// Map file names to links for setting the 'active' class
$links = [
    'dashboard.php' => 'Dashboard',
    'Admin.php'     => 'Admin Management',
    'category.php'  => 'Category',
    'project.php'   => 'Project',
];
?>

<div class="sidebar">
    <div>
        <h2><i class="fas fa-cube"></i> MyProfile Admin</h2>
        <ul class="menu">
            <!-- Home Button -->
            <li>
                <a href="../index.php">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>

            <!-- Existing Admin Links -->
            <?php foreach ($links as $file => $label): 
                $active_class = ($current_page == $file) ? 'active' : '';
                $icon = '';
                if ($file == 'dashboard.php') $icon = 'fas fa-tachometer-alt';
                if ($file == 'Admin.php') $icon = 'fas fa-users-cog';
                if ($file == 'category.php') $icon = 'fas fa-tags';
                if ($file == 'project.php') $icon = 'fas fa-project-diagram';
            ?>
                <li>
                    <a href="<?php echo $file; ?>" class="<?php echo $active_class; ?>">
                        <i class="<?php echo $icon; ?>"></i> <?php echo $label; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="logout">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

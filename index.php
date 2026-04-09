<?php
// --- Define a base path for includes ---
$basePath = __DIR__ . '/includes/'; 

// --- Function to safely include a file ---
function safeInclude($file) {
    if (file_exists($file)) {
        include $file;
    } else {
        echo "<!-- File not found: $file -->";
    }
}
?>

<?php safeInclude($basePath . 'header.php'); ?>

<?php safeInclude($basePath . 'hero.php'); ?>
<?php safeInclude($basePath . 'services.php'); ?>
<?php safeInclude($basePath . 'portfolio.php'); ?>
<?php safeInclude($basePath . 'contact.php'); ?>
<?php safeInclude($basePath . 'footer.php'); ?>

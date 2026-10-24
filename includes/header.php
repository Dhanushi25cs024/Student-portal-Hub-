<?php
/**
 * StudentHub Portal - Reusable Header
 */
if (!isset($pageTitle)) {
    $pageTitle = 'StudentHub Portal';
}
if (!isset($pageSubtitle)) {
    $pageSubtitle = 'Welcome to your Student Information Portal';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - StudentHub</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Project Custom CSS (Identical Style) -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Core JavaScript (Theme switcher, slider, modals, hamburger) -->
    <script src="js/script.js" defer></script>
</head>
<body class="d-flex flex-column min-vh-100">

<!-- ==================================================
     HEADER
================================================== -->
<header>
    <div class="container text-center py-3">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTo5SFHu6xPFG4GVAS_RSeQOdaARiaRjeaWNJAgi7gLgQ&s=10"
             width="100"
             height="100"
             alt="StudentHub Logo">
        <h1>StudentHub Portal</h1>
        <p><?= htmlspecialchars($pageSubtitle) ?></p>
    </div>
</header>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">
    <title><?php echo $page_title ?? 'ConsignX - Courier Management'; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Global CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <!-- Feather Icons or similar could be added here -->
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body class="<?php echo $body_class ?? ''; ?>">
    <div id="toast-container"></div>
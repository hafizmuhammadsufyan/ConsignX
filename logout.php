<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';

session_unset();
session_destroy();

redirect('login.php');
?>
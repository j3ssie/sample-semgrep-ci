<?php
// This code snippet is for educational purposes only and should NOT be used in production.

if (isset($_GET['code'])) {
    $user_code = $_GET['code'];
    eval($user_code); // Simulated vulnerability - executing user-provided code
}
?>

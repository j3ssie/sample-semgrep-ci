<?php
$file = $_GET['page']; // Vulnerable code, using user input directly
include($file . '.php'); // Including a file based on user input
?>

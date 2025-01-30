<?php
session_start();
session_unset();
session_destroy();
header("Location: ../features/homepage.php");
exit();
?>
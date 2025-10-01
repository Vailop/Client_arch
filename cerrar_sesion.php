<?php
    // Cerrar la sesión y redirigir al formulario de login
    session_start();
    session_destroy();
    header("Location: login.php");
    exit();
?>

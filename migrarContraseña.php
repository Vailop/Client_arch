<?php
    // migrar_passwords.php
    require 'config.php';

    $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    $sql = "SELECT IdUsuario, Contrasena FROM tbp_usuarios WHERE Contrasena NOT LIKE '$2y$%'";
    $resultado = $conexion->query($sql);

    while ($usuario = $resultado->fetch_assoc()) {
        $passwordHash = password_hash($usuario['Contrasena'], PASSWORD_DEFAULT);
        
        $update = "UPDATE tbp_usuarios SET Contrasena = ? WHERE IdUsuario = ?";
        $stmt = $conexion->prepare($update);
        $stmt->bind_param('si', $passwordHash, $usuario['IdUsuario']);
        $stmt->execute();
    }

    echo "Contraseñas migradas exitosamente";
    $conexion->close();
?>
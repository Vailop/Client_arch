<?php
// En config.php o archivo de helpers

function crearCarpetaUsuario($idUsuario) {
    $rutaBase = UPLOADS_PATH . '/usuarios/' . $idUsuario;
    
    if (!file_exists($rutaBase)) {
        mkdir($rutaBase, 0755, true);
        mkdir($rutaBase . '/documentos', 0755, true);
        
        // Archivo .htaccess para protección
        $htaccess = "Options -Indexes\n";
        $htaccess .= "Order Deny,Allow\n";
        $htaccess .= "Allow from all\n";
        file_put_contents($rutaBase . '/.htaccess', $htaccess);
        
        return true;
    }
    return false;
}

function crearCarpetaDesarrollo($idUsuario, $idDesarrollo, $dpto) {
    $rutaBase = UPLOADS_PATH . '/usuarios/' . $idUsuario . '/desarrollos/' . $idDesarrollo . '/' . $dpto;
    
    $carpetas = [
        $rutaBase . '/comprobantes',
        $rutaBase . '/planos',
        $rutaBase . '/documentos'
    ];
    
    foreach ($carpetas as $carpeta) {
        if (!file_exists($carpeta)) {
            if (!mkdir($carpeta, 0755, true)) {
                error_log("Error al crear carpeta: $carpeta");
                return false;
            }
        }
    }
    
    return true;
}

function guardarAvatar($idUsuario, $archivo) {
    $rutaUsuario = UPLOADS_PATH . '/usuarios/' . $idUsuario;
    
    // Validar archivo
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensionesPermitidas)) {
        return ['success' => false, 'message' => 'Formato no permitido'];
    }
    
    if ($archivo['size'] > 2 * 1024 * 1024) { // 2MB
        return ['success' => false, 'message' => 'Archivo muy grande'];
    }
    
    // Eliminar avatar anterior si existe
    $archivosAntiguos = glob($rutaUsuario . '/avatar_*');
    foreach ($archivosAntiguos as $archivoAntiguo) {
        unlink($archivoAntiguo);
    }
    
    // Guardar nuevo avatar
    $timestamp = time();
    $nombreFinal = "avatar_{$timestamp}.{$extension}";
    $rutaFinal = $rutaUsuario . '/' . $nombreFinal;
    
    if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
        // Ruta relativa para BD
        $rutaBD = "usuarios/{$idUsuario}/{$nombreFinal}";
        return ['success' => true, 'ruta' => $rutaBD];
    }
    
    return ['success' => false, 'message' => 'Error al guardar'];
}

function guardarComprobanteInicial($idUsuario, $idDesarrollo, $dpto, $archivo) {
    // Validar que el archivo fue subido
    if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
        error_log("Error: No se recibió el archivo correctamente");
        return ['success' => false, 'message' => 'No se recibió el archivo'];
    }
    
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensionesPermitidas)) {
        return ['success' => false, 'message' => 'Solo se permiten archivos JPG, PNG o PDF'];
    }
    
    if ($archivo['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'El archivo no debe superar 5MB'];
    }
    
    // Asegurar que la carpeta existe
    $rutaCarpeta = UPLOADS_PATH . '/usuarios/' . $idUsuario . '/desarrollos/' . $idDesarrollo . '/' . $dpto . '/comprobantes';
    if (!file_exists($rutaCarpeta)) {
        if (!mkdir($rutaCarpeta, 0755, true)) {
            error_log("Error: No se pudo crear la carpeta $rutaCarpeta");
            return ['success' => false, 'message' => 'Error al crear la carpeta'];
        }
    }
    
    $nombreArchivo = "comprobante_inicial." . $extension;
    $rutaRelativa = "usuarios/{$idUsuario}/desarrollos/{$idDesarrollo}/{$dpto}/comprobantes/{$nombreArchivo}";
    $rutaCompleta = UPLOADS_PATH . '/' . $rutaRelativa;
    
    error_log("Intentando guardar archivo en: $rutaCompleta");
    
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        error_log("Archivo guardado exitosamente: $rutaCompleta");
        return ['success' => true, 'ruta_relativa' => $rutaRelativa];
    }
    
    error_log("Error al mover archivo");
    return ['success' => false, 'message' => 'Error al guardar el archivo'];
}

function guardarPlano($idUsuario, $idDesarrollo, $dpto, $archivo) {
    // Validar que el archivo fue subido
    if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
        error_log("Error: No se recibió el plano correctamente");
        return ['success' => false, 'message' => 'No se recibió el archivo'];
    }
    
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensionesPermitidas)) {
        return ['success' => false, 'message' => 'Solo se permiten archivos JPG, PNG o PDF'];
    }
    
    if ($archivo['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'El archivo no debe superar 5MB'];
    }
    
    // Asegurar que la carpeta existe
    $rutaCarpeta = UPLOADS_PATH . '/usuarios/' . $idUsuario . '/desarrollos/' . $idDesarrollo . '/' . $dpto . '/planos';
    if (!file_exists($rutaCarpeta)) {
        if (!mkdir($rutaCarpeta, 0755, true)) {
            error_log("Error: No se pudo crear la carpeta $rutaCarpeta");
            return ['success' => false, 'message' => 'Error al crear la carpeta'];
        }
    }
    
    $nombreArchivo = "plano_departamento." . $extension;
    $rutaRelativa = "usuarios/{$idUsuario}/desarrollos/{$idDesarrollo}/{$dpto}/planos/{$nombreArchivo}";
    $rutaCompleta = UPLOADS_PATH . '/' . $rutaRelativa;
    
    error_log("Intentando guardar plano en: $rutaCompleta");
    
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        error_log("Plano guardado exitosamente: $rutaCompleta");
        return ['success' => true, 'ruta_relativa' => $rutaRelativa];
    }
    
    error_log("Error al mover plano");
    return ['success' => false, 'message' => 'Error al guardar el archivo'];
}
?>
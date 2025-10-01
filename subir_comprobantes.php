<?php
    require 'config.php';
    session_start();

    // Verificar autenticación
    if (!isset($_SESSION["idusuario"])) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit();
    }

    // Verificar que sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }

    try {
        $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        
        if ($conexion->connect_error) {
            throw new Exception('Error de conexión: ' . $conexion->connect_error);
        }
        
        // Obtener datos del formulario
        $idPago = isset($_POST['idPago']) ? (int)$_POST['idPago'] : 0;
        $montoTotal = isset($_POST['montoTotal']) ? (float)$_POST['montoTotal'] : 0;
        $montos = isset($_POST['montos']) ? $_POST['montos'] : [];
        $referencias = isset($_POST['referencias']) ? $_POST['referencias'] : [];
        $fechas = isset($_POST['fechas']) ? $_POST['fechas'] : [];
        $comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : [];
        
        // Validaciones básicas
        if ($idPago <= 0) {
            throw new Exception('ID de pago inválido');
        }
        
        if (empty($montos) || empty($_FILES['archivos']['name'][0])) {
            throw new Exception('Debe incluir al menos un comprobante con monto y archivo');
        }
        
        // Verificar que el pago pertenece al usuario
        $sqlVerificar = "SELECT p.IdPago, p.Monto, p.IdDesarrollo, p.Dpto, d.Nombre_Desarrollo, COALESCE(d.RutaImagenes, '') AS RutaImagenes
                        FROM tbr_pagos p 
                        INNER JOIN tbp_desarrollos d ON p.IdDesarrollo = d.IdDesarrollo
                        WHERE p.IdPago = ? AND p.IdUsuario = ? AND p.Estatus = 1";
        
        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bind_param('ii', $idPago, $_SESSION["idusuario"]);
        $stmtVerificar->execute();
        $resultado = $stmtVerificar->get_result();
        
        if ($resultado->num_rows === 0) {
            throw new Exception('Pago no encontrado o no autorizado');
        }
        
        $datoPago = $resultado->fetch_assoc();
        $stmtVerificar->close();
        
        // Validar suma de montos
        $sumaMontos = array_sum(array_map('floatval', $montos));
        if (abs($sumaMontos - $montoTotal) > 0.01) {
            throw new Exception('La suma de montos no coincide con el total del pago');
        }
        
        // Función para obtener carpeta del desarrollo
        function obtenerCarpetaDesarrollo($rutaImagenes, $nombreDesarrollo) {
            $ruta = trim($rutaImagenes);
            if ($ruta !== '') {
                $ruta = str_replace('\\', '/', $ruta);
                $ruta = trim($ruta, '/');
                $last = basename($ruta);
                if ($last !== '') return $last;
            }
            // Convertir nombre a slug
            $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $nombreDesarrollo);
            $slug = strtolower(trim($slug));
            $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
            return trim($slug, '_');
        }
        
        // Crear directorio de destino
        $carpetaDesarrollo = obtenerCarpetaDesarrollo($datoPago['RutaImagenes'], $datoPago['Nombre_Desarrollo']);
        $anoActual = date('Y');
        $mesActual = sprintf('COM%02d', (int)date('m'));
        
        $directorioBase = $_SERVER['DOCUMENT_ROOT'] . '/desarrollos/' . $carpetaDesarrollo . '/' . $anoActual . '/' . $mesActual . '/comprobantes/';
        
        if (!file_exists($directorioBase)) {
            if (!mkdir($directorioBase, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de destino');
            }
        }
        
        // Configuración de archivos permitidos
        $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        $tamanoMaximo = 5 * 1024 * 1024; // 5MB
        
        // Iniciar transacción
        $conexion->autocommit(false);
        
        try {
            // Procesar cada comprobante
            for ($i = 0; $i < count($montos); $i++) {
                $monto = (float)$montos[$i];
                $referencia = isset($referencias[$i]) ? trim($referencias[$i]) : '';
                $fechaReal = isset($fechas[$i]) && !empty($fechas[$i]) ? $fechas[$i] : null;
                $comentario = isset($comentarios[$i]) ? trim($comentarios[$i]) : '';
                
                // Validar monto
                if ($monto <= 0) {
                    throw new Exception('Monto inválido en comprobante ' . ($i + 1));
                }
                
                // Validar archivo
                if (!isset($_FILES['archivos']['error'][$i]) || $_FILES['archivos']['error'][$i] !== UPLOAD_ERR_OK) {
                    throw new Exception('Error al subir archivo del comprobante ' . ($i + 1));
                }
                
                $nombreArchivo = $_FILES['archivos']['name'][$i];
                $rutaTemporal = $_FILES['archivos']['tmp_name'][$i];
                $tamanoArchivo = $_FILES['archivos']['size'][$i];
                
                // Validar tamaño
                if ($tamanoArchivo > $tamanoMaximo) {
                    throw new Exception('El archivo del comprobante ' . ($i + 1) . ' excede el tamaño máximo (5MB)');
                }
                
                // Validar extensión
                $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
                if (!in_array($extension, $extensionesPermitidas)) {
                    throw new Exception('Formato de archivo no permitido en comprobante ' . ($i + 1) . '. Use: PDF, JPG, PNG');
                }
                
                // Generar nombre único
                $timestamp = time();
                $random = mt_rand(1000, 9999);
                $nombreFinal = "comp_{$idPago}_{$timestamp}_{$random}.{$extension}";
                $rutaFinal = $directorioBase . $nombreFinal;
                
                // Mover archivo
                if (!move_uploaded_file($rutaTemporal, $rutaFinal)) {
                    throw new Exception('Error al guardar archivo del comprobante ' . ($i + 1));
                }
                
                $sqlComprobante = "INSERT INTO tbr_comprobantes_pago (IdPago, IdUsuario, IdDesarrollo, Dpto, NumeroComprobante, MontoComprobante, ArchivoComprobante, Referencia, FechaPagoReal, Estatus, FechaSubida, ObservacionesUsuario) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', NOW(), ?)";

                $stmtComprobante = $conexion->prepare($sqlComprobante);
                $numeroComprobante = $i + 1;
                $stmtComprobante->bind_param('iiisidssss', 
                    $idPago, 
                    $_SESSION["idusuario"],
                    $datoPago['IdDesarrollo'],
                    $datoPago['Dpto'],
                    $numeroComprobante,
                    $monto, 
                    $nombreFinal,
                    $referencia, 
                    $fechaReal, 
                    $comentario
                );
                
                if (!$stmtComprobante->execute()) {
                    throw new Exception('Error al guardar comprobante ' . ($i + 1) . ' en base de datos');
                }
                
                $stmtComprobante->close();
            }
            
            // Confirmar transacción
            $conexion->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Comprobantes subidos exitosamente',
                'comprobantes_procesados' => count($montos)
            ]);
            
        } catch (Exception $e) {
            // Revertir transacción
            $conexion->rollback();
            
            // Eliminar archivos subidos en caso de error
            for ($i = 0; $i < count($montos); $i++) {
                if (isset($_FILES['archivos']['name'][$i])) {
                    $timestamp = time();
                    $random = mt_rand(1000, 9999);
                    $extension = strtolower(pathinfo($_FILES['archivos']['name'][$i], PATHINFO_EXTENSION));
                    $nombreFinal = "comp_{$idPago}_{$timestamp}_{$random}.{$extension}";
                    $rutaFinal = $directorioBase . $nombreFinal;
                    
                    if (file_exists($rutaFinal)) {
                        unlink($rutaFinal);
                    }
                }
            }
            
            throw $e;
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    } finally {
        if (isset($conexion)) {
            $conexion->close();
        }
    }
?>
<?php
    require 'config.php';

    // Función para generar pagos mensuales
    function generarPagosMensuales($conexion, $datosContrato) {
        try {
            // Parámetros del contrato
            $idUsuario = $datosContrato['IdUsuario'];
            $idDesarrollo = $datosContrato['IdDesarrollo'];
            $depto = $datosContrato['Depto'];
            $idCliente = $datosContrato['IdCliente'];
            $m2inicial = $datosContrato['m2inicial'];
            $m2actual = $datosContrato['m2actual'];
            $precioCompraventa = $datosContrato['Precio_Compraventa'];
            $fechaInicio = $datosContrato['FechaInicio']; // Formato: 'YYYY-MM-DD'
            $montoMensual = $datosContrato['MontoMensual'];
            $numeroMensualidades = $datosContrato['NumeroMensualidades'];

            echo "Generando $numeroMensualidades pagos para el cliente $idCliente...\n";
            echo "Monto mensual: $" . number_format($montoMensual, 2) . "\n";
            echo "Fecha inicio: $fechaInicio\n\n";

            // Preparar la consulta para insertar pagos
            $sql = "INSERT INTO tbr_pagos (
                IdUsuario, IdDesarrollo, Dpto, IdCliente, 
                m2inicial, m2actual, Precio_Compraventa, Precio_Actual,
                FechaPago, Monto, Estatus, Concepto, CreatedAt
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conexion->prepare($sql);

            // Generar cada mensualidad
            for($i = 0; $i < $numeroMensualidades; $i++) {
                // Calcular fecha de cada mensualidad (empezando desde la fecha inicial)
                $fechaPago = date('Y-m-d', strtotime($fechaInicio . " +{$i} months"));
                $numeroMensualidad = $i + 1;
                
                // Datos del pago
                $precioActual = 0; // Se llenará cuando se haga el pago
                $estatus = 1; // 1 = Pendiente, 0 = Pagado
                $concepto = "Mensualidad $numeroMensualidad";

                // Ejecutar inserción
                $stmt->bind_param('iisisdddsids', 
                    $idUsuario, $idDesarrollo, $depto, $idCliente,
                    $m2inicial, $m2actual, $precioCompraventa, $precioActual,
                    $fechaPago, $montoMensual, $estatus, $concepto
                );

                if($stmt->execute()) {
                    echo "✓ Mensualidad $numeroMensualidad programada para: $fechaPago\n";
                } else {
                    echo "✗ Error en mensualidad $numeroMensualidad: " . $stmt->error . "\n";
                }
            }

            $stmt->close();
            echo "\n¡Pagos generados exitosamente!\n";
            return true;

        } catch(Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Crear conexión
    $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // EJEMPLO PRÁCTICO - Datos del contrato HAL200706VN2
    $datosContrato = [
        'IdUsuario' => 1,
        'IdDesarrollo' => 1,
        'Depto' => '1001',
        'IdCliente' => 1,
        'm2inicial' => 50472.84,
        'm2actual' => 58920.03,
        'Precio_Compraventa' => 5501539.64,
        'FechaInicio' => '2024-11-14', // Fecha de firma del contrato
        'MontoMensual' => 20000.00,
        'NumeroMensualidades' => 30
    ];

    // Ejecutar generación
    echo "=== GENERADOR DE PAGOS MENSUALES ===\n\n";
    generarPagosMensuales($conexion, $datosContrato);

    $conexion->close();
?>
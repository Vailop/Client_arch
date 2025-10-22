<?php
/**
 * Genera plan de pagos mensuales para un usuario
 * NO incluir config.php aquí porque ya se incluye en el archivo que llama a esta función
 */

function generarPagosMensuales($conexion, $datosContrato) {
    try {
        $idUsuario = $datosContrato['IdUsuario'];
        $idDesarrollo = $datosContrato['IdDesarrollo'];
        $depto = $datosContrato['Depto'];
        $m2inicial = $datosContrato['m2inicial'];
        $precioCompraventa = $datosContrato['Precio_Compraventa'];
        $fechaInicio = $datosContrato['FechaInicio'];
        $montoMensual = $datosContrato['MontoMensual'];
        $numeroMensualidades = $datosContrato['NumeroMensualidades'];

        $sql = "INSERT INTO tbr_pagos (
            IdUsuario, IdDesarrollo, Dpto, IdCliente, 
            m2inicial, m2actual, Precio_Compraventa, Precio_Actual,
            FechaPago, Monto, Estatus, Concepto, CreatedAt
        ) VALUES (?, ?, ?, NULL, ?, NULL, ?, NULL, ?, ?, 1, ?, NOW())";

        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return false;
        }

        for($i = 0; $i < $numeroMensualidades; $i++) {
            $fechaPago = date('Y-m-d', strtotime($fechaInicio . " +{$i} months"));
            $numeroMensualidad = $i + 1;
            $concepto = "Mensualidad {$numeroMensualidad}";

            $stmt->bind_param('iisddsds', 
                $idUsuario, $idDesarrollo, $depto,
                $m2inicial, $precioCompraventa,
                $fechaPago, $montoMensual, $concepto
            );

            if(!$stmt->execute()) {
                $stmt->close();
                return false;
            }
        }

        $stmt->close();
        return true;

    } catch(Exception $e) {
        return false;
    }
}
?>
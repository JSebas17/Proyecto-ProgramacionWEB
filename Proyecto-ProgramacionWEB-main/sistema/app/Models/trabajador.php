

<?php

// Cargar la conexión con MariaDB
require_once __DIR__ . '/../../../ayni/conexion.php';


function todos_los_trabajadores() {

    global $conexion;

    $sql = "
        SELECT
            u.id_usuario AS id,
            CONCAT(u.nombre, ' ', u.apellidos) AS nombre,
            o.nombre AS oficio,
            d.nombre AS distrito,
            u.verificado AS verificado,
            COALESCE(c.nota, 0) AS nota,
            COALESCE(c.resenas, 0) AS resenas,
            pt.tarifa_referencial AS precio,
            u.telefono AS tel

        FROM usuarios u

        INNER JOIN perfiles_trabajador pt
            ON u.id_usuario = pt.id_usuario

        LEFT JOIN oficios o
            ON pt.id_oficio = o.id_oficio

        LEFT JOIN distritos d
            ON u.id_distrito = d.id_distrito

        LEFT JOIN (
            SELECT
                s.id_trabajador,
                AVG(ca.puntuacion) AS nota,
                COUNT(ca.id_calificacion) AS resenas

            FROM servicios s

            LEFT JOIN calificaciones ca
                ON s.id_servicio = ca.id_servicio

            GROUP BY s.id_trabajador
        ) c
            ON pt.id_usuario = c.id_trabajador

        WHERE u.rol = 'trabajador'
    ";

    $resultado = $conexion->query($sql);

    if (!$resultado) {
        die("Error al consultar trabajadores: " . $conexion->error);
    }

    $trabajadores = [];

    while ($fila = $resultado->fetch_assoc()) {

        $fila['verificado'] = (bool)$fila['verificado'];
        $fila['nota'] = (float)$fila['nota'];
        $fila['resenas'] = (int)$fila['resenas'];
        $fila['precio'] = (float)($fila['precio'] ?? 0);

        $trabajadores[] = $fila;
    }

    return $trabajadores;
}


function buscar_trabajador($id) {

    $trabajadores = todos_los_trabajadores();

    foreach ($trabajadores as $trabajador) {
        if ((int)$trabajador['id'] === $id) {
            return $trabajador;
        }
    }

    return null;
}


?>

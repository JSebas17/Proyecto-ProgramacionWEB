
<?php

$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "bd_tablas";

$conexion = new mysqli(
    $servidor,
    $usuario,
    $password,
    $base_datos
);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

echo "Conexion exitosa con MariaDB";

?>

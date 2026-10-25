<?php
header("Content-Type: application/json; charset=utf-8");
require_once "conexion.php";

// Obtener los datos enviados por POST desde JavaScript
$data = json_decode(file_get_contents("php://input"), true);


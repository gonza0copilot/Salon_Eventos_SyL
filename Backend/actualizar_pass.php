<?php
require_once 'conexion.php';

$password_plana = 'admin123';
$hash_correcto = password_hash($password_plana, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE usuario SET contrasena = ? WHERE email = 'adminchad@gmail.com'");
    $stmt->execute([$hash_correcto]);
    
    echo "<h3>¡Contraseña actualizada y hasheada correctamente con PHP!</h3>";
    echo "<p>Ya puedes iniciar sesión con <b>adminchad@gmail.com</b> y <b>admin123</b>.</p>";
    echo "<p><i>No olvides borrar este archivo (actualizar_pass.php) cuando termines por cuestiones de seguridad.</i></p>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
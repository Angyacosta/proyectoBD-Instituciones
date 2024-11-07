<?php
$host = "localhost";
$dbname = "instituciones";
$user = "user_inst";
$password = "instituciones";

//el usario deberia ser potsgres y su contrseña postgres eso luego lo cambiamos
try {
$conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
echo "Error: " . $e->getMessage();
exit;
}

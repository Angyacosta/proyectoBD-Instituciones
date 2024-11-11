<?php
$host = "localhost";
$dbname = "instituciones";
$user = "postgres";
$password = "postgres";
try {
$conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
echo "Error: " . $e->getMessage();
exit;
}
?>    

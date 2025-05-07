<?php
//test connessione al db 
$host = 'aa_db'; // nome container db nel network Docker
$user = 'root';
$pass = 'rootpassword';
$db = 'AstroAllies_DB';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
echo "✅ Connessione riuscita a MariaDB!";
?>
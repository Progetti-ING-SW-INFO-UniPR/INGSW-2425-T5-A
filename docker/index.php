<!-- Test -->
<?php
$mysqli = new mysqli("db", "user", "userpass", "appdb");

if ($mysqli->connect_error) {
    die("Connessione fallita: " . $mysqli->connect_error);
}
echo "<h1> Connesso a MariaDB con successo! </h1>";
?>

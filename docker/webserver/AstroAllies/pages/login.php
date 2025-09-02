<?php
session_start();
require_once __DIR__ . "/./../includes/Database.php";
require_once __DIR__ . "/./../includes/credentialChecks.php";
?>
<html lang="it">
<head>
	<meta charset="UTF-8">
	<title>Astro Allies - Accesso</title>
	<link href='https://fonts.googleapis.com/css?family=Comfortaa' rel='stylesheet'>
	<link rel="stylesheet" href="../css/main.css">
	<link rel="stylesheet" href="../css/login.css">
	<script src="../js/main.js"></script>
	<script src="../js/login.js"></script>
</head>
<body>
	<div class="bg">
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
		<div class="ast"></div>
	</div>

	<a class="home box top-left clickable" href="/pages/index.php"></a>

	<form class="menu" action="" method="POST">
		<div class="subtitle">Accedi</div>
		<div class="row">
			<div class="element">Email:</div>
			<div class="element"><input type="text" name="email" id="email" /></div>
		</div>
		<div class="row">
			<div class="element">Password:</div>
			<div class="element"><input type="password" name="password" id="password" /></div>
		</div>
		<div class="row">
			<div class="element clickable"><button class="btn" id="log">Accedi</button></div>
			<div class="element clickable"><a href="/pages/signup.php" class="btn">Registrati</a></div>
		</div>
		<input type="submit" id="logSubmit" hidden>
	</div>
</body>
</html>
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$email = $_POST["email"];
	$password = $_POST["password"];
	$sqlMail = "SELECT * FROM Utente WHERE Email = ?;";

	$db = new Database();

	$result = $db->query($sqlMail, [$email]);
	
	if ($result && $result->num_rows > 0) {
		$row = $result->fetch_row();
		if (password_verify($password . $db->pepe, $row["Password"])) {
			$_SESSION["USERNAME"] = $row["Username"];
			echo "<script>window.location.href = './index.php'</script>";
		} else {
			echo "<script>newAlert('Password errata', 'La password inserita non è corretta per l\'account dell\'email sopra inserita', 5, 'err')</script>";
		}
	} else {
		echo "<script>newAlert('Email non registrata', 'Quest\'email non è associata ad alcun account', 5, 'err')</script>";
	}

	$db->close();
}
?>
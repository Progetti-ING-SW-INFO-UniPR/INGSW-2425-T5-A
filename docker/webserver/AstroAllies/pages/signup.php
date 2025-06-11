<?php
session_start();
require_once __DIR__ . "/./../includes/Database.php";
require_once __DIR__ . "/./../includes/credentialChecks.php";
?>
<html lang="it">
<head>
	<meta charset="UTF-8">
	<title>Astro Allies - Registrazione</title>
	<link href='https://fonts.googleapis.com/css?family=Comfortaa' rel='stylesheet'>
	<link rel="stylesheet" href="../css/main.css">
	<link rel="stylesheet" href="../css/login.css">
	<script src="../js/main.js"></script>
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
	
	<a class="home box top-left clickable" href="./index.html"></a>
	
	<form class="menu" action="" method="POST">
		<div class="subtitle">Registrati</div>
		<div class="row">
			<div class="element">Email:</div>
			<div class="element"><input type="text" name="email" id="email" /></div>
		</div>
		<div class="row">
			<div class="element">Nome Utente:</div>
			<div class="element"><input type="text" name="username" id="username" /></div>
		</div>
		<div class="row">
			<div class="element">Password:</div>
			<div class="element"><input type="password" name="password" id="password" /></div>
		</div>
		<div class="row">
			<div class="element">Conferma:</div>
			<div class="element"><input type="password" name="confirm" id="confirm" /></div>
		</div>
		<div class="row">
			<div class="element clickable"><button class="btn" id="reg">Registrati</button></div>
			<div class="element clickable"><a href="./login.html" class="btn">Accedi</a></div>
		</div>
		<input type="submit" id="regSubmit" hidden>
	</form>
</body>
<footer>
	<script src="../js/signup.js"></script>
</footer>
</html>
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$email = $_POST["email"];
	$username = $_POST["username"];
	$password = $_POST["password"];
	$sqlMail = "SELECT * FROM Utente WHERE Email = ?;";
	$sqlUser = "SELECT * FROM Utente WHERE Username = ?;";
	// $pepe = getenv('PSWD_PEPE');
	$pepe = "2ry89^";
	$sqlReg = "INSERT INTO Utente (Username, Email, Password) VALUES (?, ?, ?)";
	
	$db = new Database();

	$result = $db->query($sqlMail, [$email]);
	
	if ($result && $result->num_rows > 0) {
		echo "<script>newAlert('Email già in uso', 'L\'email fornita è già stata utilizzata per creare un account', 5, 'err')</script>";
	} else {
		$result = $db->query($sqlUser, [$username]);
		if ($result && $result->num_rows > 0) {
			echo "<script>newAlert('Nome Utente già in uso', 'Il Nome Utente fornito è già stato utilizzato per creare un account', 5, 'err')</script>";
		} else {
			if(isValidEmail($email) and isValidPassword($password) and isValidUsername($username)) {
				try {
					$result = $db->query($sqlReg, [$username, $email, password_hash($password . $pepe, PASSWORD_DEFAULT)]);
				} catch(Exception $err) {
					$e = $err->getMessage();
					// echo "<script>newAlert('Errore in data base', '" . $e . "', 5, 'err')</script>";
					echo $e;
				}
			} else {
				if(!isValidEmail($email))
					echo "<script>newAlert('Foramttazione errata', 'Email non riconosciuta', 5, 'err')</script>";
				if(!isValidUsername($username))
					echo "<script>newAlert('Foramttazione errata', 'Il Nome Utente deve contenere solo lettere e cifre', 5, 'err')</script>";
				if(!isValidPassword($password))
					echo "<script>newAlert('Foramttazione errata', 'La password deve avere: minimo 8 caratteri, una lettera maiuscoa, una minuscola, una cifra e un simbolo speciale', 5, 'err')</script>";
			}
		}
	}

	$db->close();
}
?>
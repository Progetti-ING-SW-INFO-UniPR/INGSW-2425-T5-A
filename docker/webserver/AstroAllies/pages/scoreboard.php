<?php
require_once __DIR__ . "/./../includes/Database.php";
require_once __DIR__ . "/./../includes/credentialChecks.php";

session_start();

if (!isset($_SESSION["USERNAME"])) {
	header("Location: ./index.php");
	die();
}
?>
<html lang="it">
<head>
	<meta charset="UTF-8">
	<title>Astro Allies - Classifica</title>
	<link href='https://fonts.googleapis.com/css?family=Comfortaa' rel='stylesheet'>
	<link rel="stylesheet" href="../css/main.css">
	<link rel="stylesheet" href="../css/scoreboard.css">
	<script src="../js/main.js"></script>
	<script src="../js/scoreboard.js"></script>
</head>
<!-- <body onload="test(randInt(10, 51));"> -->
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

	<div class="menu nosel">
		<div class="subtitle">Classifica Galattica</div>
		<div class="record highlight" id="user"><div>1°</div><div>Username</div><div>0000000</div></div>
		<div class="div"></div>
		<div class="list" id="scoreboard">
		</div>
	</div>
	<script> let records = [], username = ""; </script>
	<?php
		$queryUser = "SELECT *
					  FROM (
						SELECT ROW_NUMBER() OVER (ORDER BY Punteggio DESC, Username ASC) AS Pos, Username, Punteggio
						FROM Utente
						ORDER BY Punteggio DESC, Username ASC
					  ) AS Scoreboard
					  WHERE Username = ?;";
		$queryList = "SELECT Username, Punteggio
					  FROM Utente
					  ORDER BY Punteggio DESC
					  LIMIT 50;";
		$db = new Database();

		$result = $db->query($queryUser, [$_SESSION["USERNAME"]]);
		if(!($result && $result->num_rows > 0)){
			echo '<script>newAlert("Utente non trovato", "Il tuo Username non è stato trovato all\'interno del database.", 5, "err")</scritp>';
			die();
		}
		$row = $result->fetch_row();
		echo "<script>updateUser({$row['Pos']}, '{$row['Username']}', {$row['Punteggio']})</script>\n";

		$result = $db->query($queryList, []);
		echo "<script>records = [";
		$row = $result->fetch_row();
		while($row) {
			$obj = '{';
			$obj = $obj."'name':'{$row['Username']}', 'points':{$row['Punteggio']}";
			$obj = $obj.'},'."\n";
			echo $obj;
			$row = $result->fetch_row();
		}
		echo "]</script>\n";
	?>
	<script>
		for (let user of records) {
			appendRecord(user.name, user.points, user.user);
		}
	</script>
</html>
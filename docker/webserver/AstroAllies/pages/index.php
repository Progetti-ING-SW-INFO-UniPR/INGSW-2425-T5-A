<?php
session_start()
?>

<html lang="it">
<head>
	<meta charset="UTF-8">
	<title>Astro Allies</title>
	<link href='https://fonts.googleapis.com/css?family=Comfortaa' rel='stylesheet'>
	<link rel="stylesheet" href="../css/main.css">
	<link rel="stylesheet" href="../css/index.css">
	<script src="../js/main.js"></script>
	<script src="../js/index.js"></script>
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
	<div class="box top-right">
		<?php
		if(isset($_SESSION["USERNAME"])) {
			echo $_SESSION["USERNAME"];
		}
		else {
			echo "Guest";
		}
		?></div>
	<div class="menu nosel">
		<div class="title">Astro Allies</div>
		<!--
		<div class="element"><a href="">Esci</a></div>
		-->
		<div class="element clickable"><a href="/pages/login.php">Accedi/Registrati</a></div>
		<div class="element clickable" onclick="toggle('create')">Crea una Partita</div>
		<div class="element clickable" onclick="toggle('join')">Unisciti ad una Partita</div>
		<div class="element clickable"><a href="/pages/scoreboard.php">Classifica Globale</a></div>
	</div>
	<div class="shadow hidden" id="create" onclick="toggle('create');">
		<div class="menu nosel" onmouseenter="stopProp(this)">
			<div class="subtitle">Scegli la tua navicella</div>
			<div class="grid">
				<div class="grid-el clickable" onclick="creaPartita(2)">
					<div class="el-img"><img src="../src/sprite/torpedo.png" /></div>
					<div class="el-name">Torpedo</div>
					<div class="el-desc">2 giocatori</div>
				</div>
				<div class="grid-el clickable" onclick="creaPartita(3)">
					<div class="el-img"><img src="../src/sprite/incrociatore.png" /></div>
					<div class="el-name">Incrociatore</div>
					<div class="el-desc">3 giocatori</div>
				</div>
				<div class="grid-el clickable" onclick="creaPartita(4)">
					<div class="el-img"><img src="../src/sprite/corazzata.png" /></div>
					<div class="el-name">Corazzata</div>
					<div class="el-desc">4 giocatori</div>
				</div>
			</div>
		</div>
	</div>
	<div class="shadow hidden" id="join" onclick="toggle('join');">
		<div class="menu" onmouseenter="stopProp(this)">
			<div class="subtitle">Inserisci Codice</div>
			<div class="element clickable"><input type="text" name="roomid" id="roomid" maxlength="20" autocomplete="off"/></div>
			<div class="element clickable"><button class="btn" onclick="join();">Unisciti</button></div>
		</div>
	</div>
</body>
</html>
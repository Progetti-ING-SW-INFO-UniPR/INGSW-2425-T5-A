<?php
session_start();
if (!isset($_SESSION["USERNAME"])) {
	header("Location: ./index.php");
	die();
}
?>
<html lang="it">
<head>
	<meta charset="UTF-8">
	<title>Astro Allies - Stanza</title>
	<link href='https://fonts.googleapis.com/css?family=Comfortaa' rel='stylesheet'>
	<link rel="stylesheet" href="../css/main.css">
	<link rel="stylesheet" href="../css/room.css">
	<link rel="stylesheet" href="../css/game.css">
	<script src="../js/main.js"></script>
	<script src="../js/room.js"></script>
</head>
<body>
	<div id="room">
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
		<?php 
		echo '<input id="username" type="text" value="'.$_SESSION["USERNAME"].'" hidden>';
		?>
		<div class="menu nosel">
			<div class="subtitle">Stanza " <div id="code">Test</div> " <div class="copy clickable" onclick="copy('code');"></div></div>
			<div class="element">Equipaggio:</div>
			<div class="grid" id="players">
				<div class="grid-el">
					<div class="el-title" id="player0">Nome Utente</div>
					<div class="el-name">Capitano</div>
				</div>
			</div>
			<div class="row">
				<div class="element clickable"><button class="btn" onclick="cancel();">Annulla</button></div>
				<div class="element clickable"><button id="start" class="btn" onclick="start();" disabled>Inizia</button></div>
			</div>
		</div>
	</div>
	<div id="game" hidden>
		<div class="bg"></div>
		<div class="game">
			<canvas id="field"></canvas>
		</div>
	</div>

	<footer>
		<script src="../js/game.js"></script>
		<script src="../js/socketHandler.js"></script>
	</footer>
</body>
</html>

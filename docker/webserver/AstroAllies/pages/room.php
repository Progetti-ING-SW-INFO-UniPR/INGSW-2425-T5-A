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
	<script src="../js/main.js"></script>
	<script src="../js/room.js"></script>
</head>
<body onload="initNames()">
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
			<div class="element clickable"><button class="btn" onclick="start();" disabled>Inizia</button></div>
		</div>
	</div>

	<footer>
		<script>
			let idDiv = document.getElementById("code");

			let websocket = new WebSocket("ws://"+window.location.hostname+":8000/");

			/**
			 * Invia un messaggio tramite socket al server.
			 * @param {string} code tipo di messaggio
			 * @param {object} data contenuto del messaggio
			 */
			function send2server(code, data) {
				let msg = {code: code, data: data};
				websocket.send(JSON.stringify(msg));
			}

			websocket.onopen = () => {
				// let msg = {code: "test", data: "Hello there!"};
				// websocket.send(JSON.stringify(msg));
				let msg = {code: "connect", data: document.getElementById("username").value};
				websocket.send(JSON.stringify(msg));
				if (id == null) {
					msg.code = "create";
					msg.data = "";
					websocket.send(JSON.stringify(msg));
				} else {
					websocket.send(`{"code": "join", "data":"${id}"}`);
				}
			}
			

			websocket.onmessage = (ev) => {
				let msg = JSON.parse(ev.data);
				console.log(msg);
				switch (msg.code) {
					case "room":
						idDiv.innerHTML = msg.data;
						id = msg.data;
						websocket.send(`{"code": "join", "data":"${id}"}`);
						break;
					case "connected":
						connect(msg.data);
						break;
					case "disconnected":
						disconnect(msg.data);
						break;
				}
			};
		</script>
		<script src="../js/game.js"></script>
	</footer>
</body>
</html>

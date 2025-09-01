<?php
session_start();
if (isset($_SESSION[""]))
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

	<div class="menu nosel">
		<div class="subtitle">Stanza " <div id="code">Test</div> " <div class="copy clickable" onclick="copy('code');"></div></div>
		<div class="element">Equipaggio:</div>
		<div class="grid" id="players">
			<div class="grid-el">
				<div class="el-title">Nome Utente</div>
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
			// console.log(location.hostname);
			let websocket = new WebSocket("ws://localhost:8000/");

			websocket.onmessage = (ev) => {
				let msg = JSON.parse(ev.data);
				console.log("Message Received!");
				console.log(msg);
			};

			websocket.onopen = () => {
				let msg = {code: "username", data: "Hello there!"};
				websocket.send(JSON.stringify(msg));
				console.log("Message Sent!");
			}
			
		</script>
	</footer>
</body>
</html>

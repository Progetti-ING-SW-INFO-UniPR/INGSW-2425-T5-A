let idDiv = document.getElementById("code");

let socketUrl = `wss://${window.location.host}/ws`;

let websocket = new WebSocket(socketUrl);

let username = document.getElementById("username").value;

let captainUsername = "";

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
	send2server("connect", username);
	if (id == null) {
		send2server("create", maxPlayers);
	} else {
		websocket.send(`{"code": "join", "data":"${id}"}`);
	}
}


websocket.onmessage = (ev) => {
	// console.log(ev.data);
	let msg = JSON.parse(ev.data);
	switch (msg.code) {
		case "error":
			newAlert(msg.data.name, msg.data.desc, 5, "err");
			break;
		case "room":
			idDiv.innerHTML = msg.data;
			id = msg.data;
			websocket.send(`{"code": "join", "data":"${id}"}`);
			break;
		case "maxplayers":
			if(msg.data > 1 && msg.data < 5) {
				maxPlayers = msg.data;
				initNames();
			}
			break;
		case "connected":
			connect(msg.data);
			break;
		case "disconnected":
			disconnect(msg.data);
			break;
		case "captain":
			captainUsername = msg.data;
			if(msg.data == username) {
				isCaptain = true;
				bindCommands();
				newAlert("Capitano!", "Ora sei tu a pilotare la nave!", 3,  "green");
			}
			break;
		case "start":
			document.getElementById("room").hidden = true;
			document.getElementById("game").hidden = false;
			bindCommands();
			break;
		case "game":
			update(msg.data);
			break;
		case "gameover":
			setTimeout(()=>{
				if (msg.data.status == "won") {
					newAlert("Missione completata!", "Siete riusciti a portare a casa tutti i "+msg.data.score+" punti che avete raccolto!\n", 10, "green");
				}
				else if (msg.data.status == "lost") {
					newAlert("Missione fallita!", "Dopo la collisione avete recuperato solo "+msg.data.score+" punti dalle macerie\n", 10, "err");
				}
			}, 1000);
			setTimeout(()=>{
				document.getElementById("room").hidden = false;
				document.getElementById("game").hidden = true;
			}, 5000);
			break;
	}
};
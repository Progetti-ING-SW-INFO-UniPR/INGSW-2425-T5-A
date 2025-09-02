let idDiv = document.getElementById("code");

let websocket = new WebSocket("ws://"+window.location.hostname+":8000/");

let username = document.getElementById("username").value;

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
	console.log(ev.data);
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
		case "connected":
			connect(msg.data);
			break;
		case "disconnected":
			disconnect(msg.data);
			break;
		case "captain":
			if(msg.data == username)
				isCaptain = true;
			break;
	}
};
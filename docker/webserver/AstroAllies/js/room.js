let connected = 0;
let maxPlayers = parseInt(urlParams.get("players"));
let id = urlParams.get("id");

if (id == "") id = null;
// if (maxPlayers == null) maxPlayers = 4;

function getPlayer(n, name) {
	let divEl = document.createElement("div");
	let divTitle = document.createElement("div");
	let divName = document.createElement("div");

	divEl.classList.add("grid-el");
	divTitle.classList.add("el-title");
	divName.classList.add("el-name");

	divTitle.id = "player"+n;

	divTitle.appendChild(document.createTextNode(name));
	divName.appendChild(document.createTextNode(n+"° Cannoniere"));

	divEl.appendChild(divTitle);
	divEl.appendChild(divName);
	return divEl;
}

function initNames() {
	/* 
	 * 	<div class="grid-el">
	 * 		<div class="el-title">Reclutamento...</div>
	 * 		<div class="el-name">1° Cannoniere</div>
	 * 	</div>
	 */
	let grid = document.getElementById("players");

	for (let i = 1; i < maxPlayers; i++) {
		let divEl = getPlayer(i, "Reclutamento...");

		grid.appendChild(divEl);
	}

	document.getElementById("code").innerHTML = id;
}

function connect(username) {
	let div = document.getElementById("player"+connected);
	if (div == null) {
		let grid = document.getElementById("players");
		grid.appendChild(getPlayer(connected, username));
	} else {
		div.innerHTML = "";
		div.appendChild(document.createTextNode(username));
	}
	connected++;
}

function disconnect(username) {
	let div = document.getElementById("player"+connected);
	if (div == null) {
		let grid = document.getElementById("players");
		grid.appendChild(getPlayer(connected, username));
	} else {
		div.innerHTML = "";
		div.appendChild(document.createTextNode(username));
	}
	connected++;
}

function cancel() {
	TODO();
}

function start() {
	TODO();
}
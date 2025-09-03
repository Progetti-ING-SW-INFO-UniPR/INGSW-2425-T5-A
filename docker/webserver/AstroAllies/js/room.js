let connected = 0;
let maxPlayers = parseInt(urlParams.get("players"));
let id = urlParams.get("id");
let isCaptain = false;

if (id == "") id = null;
// if (maxPlayers == null) maxPlayers = 4;

function getPlayer(n, name, title) {
	let divEl = document.createElement("div");
	let divTitle = document.createElement("div");
	let divName = document.createElement("div");

	divEl.classList.add("grid-el");
	divTitle.classList.add("el-title");
	divName.classList.add("el-name");

	divTitle.id = "player"+n;

	divTitle.appendChild(document.createTextNode(name));
	divName.appendChild(document.createTextNode(title));

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


	for (let i = connected; i < maxPlayers; i++) {
		let title = i == 0 ? "Capitano" : `${i}° Cannoniere`;
		let divEl = getPlayer(i, "Reclutamento...", title);

		grid.appendChild(divEl);
	}

	document.getElementById("code").innerHTML = id;
}

function connect(username) {
	let div = document.getElementById("player"+connected);
	if (div == null) {
		let grid = document.getElementById("players");
		let title = connected == 0 ? "Capitano" : `${connected}° Cannoniere`;
		grid.appendChild(getPlayer(connected, username, title));
	} else {
		div.innerHTML = "";
		div.appendChild(document.createTextNode(username));
	}
	connected++;
	if(connected >= maxPlayers && isCaptain) {
		document.getElementById("start").disabled = false;
	}
}

function disconnect(username) {
	let i = 0;
	for(i = 0; i < connected; i++) {
		let div = document.getElementById("player"+i);
		if(div.innerHTML == username) {
			break;
		}
	}
	connected--;
	for(; i < connected; i++) {
		let div = document.getElementById("player"+i);
		let div2 = document.getElementById("player"+(i+1));
		div.innerHTML = div2.innerHTML;
	}
	document.getElementById("player"+i).innerHTML = "Reclutamento...";

	if(connected < maxPlayers || !isCaptain) {
		document.getElementById("start").disabled = true;
	}
	newAlert(username+" è uscito", username+" ha abbandonato la nave", 2);
}

function cancel() {
	window.location.href = "./index.php";
}

function start() {
	send2server("start", id);
}
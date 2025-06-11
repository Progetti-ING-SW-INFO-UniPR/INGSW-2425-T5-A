function initNames() {
	/* 
	 * 	<div class="grid-el">
	 * 		<div class="el-title">Reclutamento...</div>
	 * 		<div class="el-name">1° Cannoniere</div>
	 * 	</div>
	 */
	let n = parseInt(urlParams.get("players"));
	let grid = document.getElementById("players");

	for (let i = 1; i < n; i++) {
		let divEl = document.createElement("div");
		let divTitle = document.createElement("div");
		let divName = document.createElement("div");

		divEl.classList.add("grid-el");
		divTitle.classList.add("el-title");
		divName.classList.add("el-name");

		divTitle.appendChild(document.createTextNode("Reclutamento..."));
		divName.appendChild(document.createTextNode(i+"° Cannoniere"));

		divEl.appendChild(divTitle);
		divEl.appendChild(divName);

		grid.appendChild(divEl);
	}
}

function cancel() {
	TODO();
}

function start() {
	TODO();
}
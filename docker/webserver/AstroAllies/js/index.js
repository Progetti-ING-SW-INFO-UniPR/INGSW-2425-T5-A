function toggle(id) {
	let el = document.getElementById(id);
	el.hidden = !el.hidden;
}

function stopProp(el) {
	el.onclick = (ev) => {
		ev.stopPropagation();
	};
}

function creaPartita(nGiocatori) {
	// TODO
}
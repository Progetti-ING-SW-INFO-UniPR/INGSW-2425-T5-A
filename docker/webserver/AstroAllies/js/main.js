function toggle(id) {
	let cl = document.getElementById(id).classList;
	if (cl.contains("hidden")) {
		cl.remove("hidden");
	}
	else {
		cl.add("hidden");
	}
}

function stopProp(el) {
	el.onclick = (ev) => {
		ev.stopPropagation();
	};
}
function creaPartita(nGiocatori) {
	window.location.href = "/pages/room.php?players="+nGiocatori;
}

function join() {
	let id = document.getElementById("roomid").value;
	window.location.href = "/pages/room.php?id="+id;
}

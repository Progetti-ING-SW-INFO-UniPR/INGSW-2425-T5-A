function signup() {
	if(document.getElementById("password").value != document.getElementById("confirm").value) {
		newAlert("Errore Password", "Conferma e Password non coincidono", 5, "err");
		return;
	}
	document.getElementById("regSubmit").click();
}

document.getElementById("reg").addEventListener("click", (ev) => {
	signup();
	ev.preventDefault();
});
function signup() {
	if(document.getElementById("password").value != document.getElementById("confirm")) {
		newAlert("Errore Password", "Conferma e Password non coincidono", 5, "err");
		return;
	}
	document.getElementById("regSubmit").click();
}
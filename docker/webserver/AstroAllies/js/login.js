function login() {
	document.getElementById("logSubmit").click();
}

document.getElementById("log").addEventListener("click", (ev) => {
	login();
	ev.preventDefault();
});
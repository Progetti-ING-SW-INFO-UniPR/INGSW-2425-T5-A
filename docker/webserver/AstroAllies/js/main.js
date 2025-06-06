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

function createAlert(name, desc) {
	// <div class="alert">
	// 	<div>Name</div>
	// 	<div>Description</div>
	// </div>
	let alertDiv = document.createElement("div");
	let nameDiv = document.createElement("div");
	let descDiv = document.createElement("div");
	alertDiv.classList.add("alert");
	nameDiv.appendChild(document.createTextNode(name));
	descDiv.appendChild(document.createTextNode(desc));
	alertDiv.appendChild(nameDiv);
	alertDiv.appendChild(descDiv);
	return alertDiv;
}

/**
 * @param {string} name The name for the Alert, shows like a title
 * @param {string} desc The description listed below the name
 * @param {number} time The amout of seconds to show the allert for
 * @param {string[]} classes Any amount of css-classes to give to the alert div element, like "err"
 */
function newAlert(name, desc, time=5, ...classes) {
	let al = createAlert(name, desc);
	for (let c of classes) {
		al.classList.add(c);
	}
	document.body.appendChild(al);
	setTimeout(() => {
		al.classList.add("al-show");
		setTimeout(() => {
			al.classList.remove("al-show");
			setTimeout(() => {
				document.removeChild(al);
			}, 1000);
		}, time*1000);
	},1)
}

function TOOD() {
	newAlert("Undefined Action", "The requested action is not yet available", 5, "err");
}
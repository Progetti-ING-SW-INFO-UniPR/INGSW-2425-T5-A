/*********** GRAFICA ***********/
let field = document.getElementById("field"); // canvas
let g = field.getContext("2d"); //context
let bg_color = "rgb(0,0,21)";
const ASSETS = {"asteroid":"/src/sprite/asteroid.png",
                "bullet":"/src/sprite/bullet.png",
                "corazzata":"/src/sprite/corazzata.png",
                "incrociatore":"/src/sprite/incrociatore.png",
                "torpedo":"/src/sprite/torpedo.png",
                "points":"/src/sprite/points.png",
                "powerup_vel":"/src/sprite/powerup_blue.png",
                "powerup_rank":"/src/sprite/powerup_red.png",
                "powerup_size":"/src/sprite/powerup_green.png"
            };
let imageCache = {};
let cannon_dir = 0;
let cannon_vel = 0.2; //rad

let intRotateLeft;
let intRotateRight;

loadAssets(ASSETS);

/**
 * 
 * Pulizia canvas e redraw oggetti
 * @param {object} data = { asteroids: [{x, y, w, h, a}, ...],
 *                          items: [{x, y, w, h, type}, ...],
 *                          bullets: [{x, y, w, h}, ...],
 *                          ship: {x, y, w, h, a},
 *                          energy: int,
 *                          maxenergy: int,
 *                          score: int,
 *                          comms: {string:int , ...},
 *                          status: string
 *                          };    
 */
function update(data) {

    g.clearRect(0, 0, field.width, field.height);
    g.fillStyle = bg_color;

    data.asteroids.forEach(asteroid => {
        drawAsset("asteroid", asteroid.x, asteroid.y, asteroid.w, asteroid.h, asteroid.a);
    });
	
    data.items.forEach(item => {
        if(item.type < 1);    
        else if(item.type == 1)
            drawAsset("points", item.x, item.y, item.w, item.h);
        else if(item.type == 2)
            drawAsset("powerup_vel", item.x, item.y, item.w, item.h);
        else if(item.type == 3)
            drawAsset("powerup_rank", item.x, item.y, item.w, item.h);
        else if(item.type == 4)
            drawAsset("powerup_size", item.x, item.y, item.w, item.h);
    });

	if(!isCaptain) {
		drawCannon(data.ship.x, data.ship.y, data.ship.w, data.ship.h, cannon_dir);
	}

    let ship_type = "";
    switch(maxPlayers){
        case 2:
            ship_type = "torpedo";
            break;
        case 3:
            ship_type = "incrociatore";
            break;
        case 4:
            ship_type = "corazzata";
            break;
    }
    drawAsset(ship_type, data.ship.x, data.ship.y, data.ship.w, data.ship.h, data.ship.a);

    data.bullets.forEach(bullet => {
        drawAsset("bullet", bullet.x, bullet.y, bullet.w, bullet.h);
    });

	drawEnergy(data.energy, data.maxenergy);
    drawScore(data.score);
	let i = 0;
    for(let name in data.comms){
        drawComms(name, data.comms[name], i);
		i++;
    }

    if(data.status != "running"){
        drawStatus(data.status); 
    }

}

/**
 * Caricamento in cache assets @var imageCache
 * @param {object} assets {"nome": "/url", "altronome": "/altrourl"} 
 */
function loadAssets(assets){
    console.log("Caricamento Assets...");
    for (let nome in assets) {
        const img = new Image();
        // img.src = window.location.protocol + "//" + window.location.hostname + assets[nome];
        img.src = ".." + assets[nome];
        img.onload = () => {
            imageCache[nome] = img;
        };
    }
    console.log("Assets Caricati!");
}
/**
 * Disegna l'asset, riceca nella cache @var imageCache 
 * @param {string} nome nome dell'assets
 * @param {float} x posizione dell'oggetto (angolo alto sinistra)
 * @param {float} y posizione dell'oggetto (angolo alto sinistra)
 * @param {float} w larghezza oggetto
 * @param {float} h larghezza oggetto
 * @param {float} a angolo alfa rad della direzione dell'oggetto
 * 
 */
function drawAsset(nome, x, y, w, h, a=0){
    const img = imageCache[nome];
    if(!img) return; //immagine non ancora caricata;

    g.save();
    g.save();
    g.translate(x+w/2, y+h/2); // riferimento il centro dell'immagine
    g.rotate(a);
    g.drawImage(img, -w/2, -h/2, w, h);
    g.restore();
    g.restore();
}

/**
 * Disegna la linea di tiro del cannone 
 * @param {float} x posizione della navicella (angolo alto sinistra)
 * @param {float} y posizione della navicella (angolo alto sinistra)
 * @param {float} w larghezza navicella
 * @param {float} h larghezza navicella
 * @param {float} a angolo alfa rad della direzione del cannone
 * @param {string} color colore linea default white
 * @param {float} length lunghezza linea default 60
 * 
 * default lengh = 60 per non essere nascosta dalla navicella (75)
 */
function drawCannon(x, y, w, h, a, color="white", length=40) {

  x = x+w/2;
  y = y+h/2;
  const xEnd = x + length * Math.cos(a);
  const yEnd = y + length * Math.sin(a);

  g.save();
  g.lineWidth = 3; //spessore in pixel 
  g.beginPath();
  g.moveTo(x, y);       // punto iniziale
  g.lineTo(xEnd, yEnd); // punto finale
  g.strokeStyle = color;
  g.stroke();           // disegna la linea
  g.restore();

}

/**
 * Disegna il punteggio della partita
 * @param {int} score punteggio corrente
 */
function drawScore(score) { 
    g.save();
    g.font = "30px Arial";
    g.fillStyle = "white";
    g.textAlign = "middle";
    g.textBaseline = "top";
    g.fillText("Score: " + score, field.width / 2, 10); //10 px di margine
    g.restore();
}

/**
 * Disegna l'energia corrente
 * @param {int} energy energia navicella
 */
function drawEnergy(energy, maxenergy) {
    g.save();
    g.font = "20px Arial";
    g.fillStyle = "white";
    g.textAlign = "left";
    g.textBaseline = "bottom";
    g.fillText("Energy: " + energy + "/" + maxenergy, 10, field.height - 10); //10 px di margine
    g.restore();
}

/**
 * Disegna le comunicazioni ricevute
 * @param {int} type da 1 a 7 default ""
 */
function drawComms(user, type, pos) {
    g.save();
    g.font = "20px Arial";
    g.textAlign = "right";
    g.textBaseline = "top";

    let  comm;

    switch(type){
        case 1:
            comm = "Exfil";
            break;
        case 2:
            comm = "Yes";
            break;
        case 3:
            comm = "No";
            break;
        case 4:
            comm = "Left";
            break;
        case 5:
            comm = "Right";
            break;
        case 6:
            comm = "Front";
            break;
        case 7:
            comm = "Rear";
            break;
        default:
            comm = "";
    }
	g.fillStyle = "green";
	let txt = " | " + user;
	let x = field.width - 10;
    g.fillText(comm, x - g.measureText(txt).width , 25*pos+10); //10 px di margine
    if(captainUsername == user)
		g.fillStyle = "yellow";
	else
		g.fillStyle = "white";
    g.fillText(txt, x , 25*pos+10); //10 px di margine
    g.restore();
}  

// TODO
function drawStatus(status){
    g.font = "40px Arial";
    g.fillStyle = "white";
    g.textAlign = "center";
    g.textBaseline = "middle";
    g.fillText(status, field.width/2, field.height/2);
}

/*********** CONTROLLER ***********/

//attaccare add event listener window 
function captainKeyDown(KeyboardEvent){
    const commands = ["KeyW", "KeyA","KeyS","KeyD",
                      "ArrowUp","ArrowLeft","ArrowDown","ArrowRight","Space",
                      "Digit0","Digit1","Digit2","Digit3","Digit4","Digit5","Digit6","Digit7","Digit8","Digit9"];
    let keycode = KeyboardEvent.code;
    let sendKeyDown = (key) => send2server("keydown", key);
    switch(keycode){
        case "KeyW":
            keycode = "ArrowUp";
            break;
        case "KeyA":
            keycode = "ArrowLeft";
            break;
        case "KeyS":
            keycode = "ArrowDown";
            break;
        case "KeyD":
            keycode = "ArrowRight";
            break;
    }   
    if(commands.includes(keycode))
        sendKeyDown(keycode);
 }

function captainKeyUp(KeyboardEvent){
    const commands = ["KeyW", "KeyA","KeyS","KeyD",
                      "ArrowUp","ArrowLeft","ArrowDown","ArrowRight"];
    let keycode = KeyboardEvent.code;
    let sendKeyUp = (key) => send2server("keyup", key);
	if(KeyboardEvent.repeat) return;
    switch(keycode){
        case "KeyW":
            keycode = "ArrowUp";
            break;
        case "KeyA":
            keycode = "ArrowLeft";
            break;
        case "KeyS":
            keycode = "ArrowDown";
            break;
        case "KeyD":
            keycode = "ArrowRight";
            break;
    }   
    if(commands.includes(keycode))    
        sendKeyUp(keycode);
 }

function cannonKeyDown(KeyboardEvent){
    const commands = ["Digit0","Digit1","Digit2","Digit3","Digit4","Digit5","Digit6","Digit7","Digit8","Digit9"];
    let keycode = KeyboardEvent.code;
    let sendKeyDown = (key) => send2server("keydown", key);
	if(KeyboardEvent.repeat) return;
    switch(keycode){
        case "Space":
        case "KeyW":
        case "ArrowUp":
            send2server("shoot", cannon_dir);
            break;
        case "KeyA":
        case "ArrowLeft":
			if(!intRotateLeft)
            	intRotateLeft = setInterval(rotateCannonLeft,1000/30);
            break;
        case "KeyD":
        case "ArrowRight":
			if(!intRotateRight)
            	intRotateRight = setInterval(rotateCannonRight,1000/30);
            break;
    } 
    if(commands.includes(keycode))    
        sendKeyDown(keycode);
 }

function rotateCannonLeft(){
    cannon_dir -= cannon_vel;
}
function rotateCannonRight(){
    cannon_dir += cannon_vel;
}

function cannonKeyUp(KeyboardEvent){
    let keycode = KeyboardEvent.code;
    switch(keycode){
        case "KeyA":
        case "ArrowLeft":
            clearInterval(intRotateLeft);
			intRotateLeft = null;
            break;
		case "KeyD":
		case "ArrowRight":
			clearInterval(intRotateRight);
			intRotateRight = null;
		break;
    } 
}

function bindCommands() {
	if(isCaptain) {
		document.onkeydown = captainKeyDown;
		document.onkeyup = captainKeyUp;
	} else {
		document.onkeydown = cannonKeyDown;
		document.onkeyup = cannonKeyUp;
	}
}
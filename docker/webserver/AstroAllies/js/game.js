/*********** GRAFICA ***********/
let field = document.getElementById("field"); // canvas
let g = field.getContext("2d"); //context

let imageCache = {};
/**
 * Caricamento in cache assets @var imageCache
 * @param {object} assets {"nome": "url", "altronome": "altrourl"} 
 */
function loadAssets(assets){
    console.log("Caricamento Assets...");
    for (let nome in assets) {
        const img = new Image();
        img.src = assets[nome];
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
function drawAsset(nome, x, y, w, h, a){
    const img = imageCache[nome];
    if(!img) return; //immagine non ancora caricata;

    g.save();
    g.translate(x+w/2, y+h/2); // riferimento il centro dell'immagine
    g.rotate(a);
    g.drawImage(img, -w/2, -h/2, w, h);
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
function drawCannon(x, y, w, h, a, color="white", length=60) { 

  x = x+w/2;
  y = y+h/2;
  const xEnd = x + length * Math.cos(a);
  const yEnd = y + length * Math.sin(a);

  g.save();
  g.lineWidth = 3; //spessore in pixel 
  g.strokeStyle = color;
  g.beginPath();
  g.moveTo(x, y);       // punto iniziale
  g.lineTo(xEnd, yEnd); // punto finale
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
    g.filStyle = "white";
    g.textAlign = "center";
    g.textBaseline = "top";
    g.fillText("Score: " + score, field.width / 2, 10); //10 px di margine
    g.restore();
}

/**
 * Disegna l'energia corrente
 * @param {int} energy energia navicella
 */
function drawEnergy(energy) {
    g.save();
    g.font = "20px Arial";
    g.filStyle = "white";
    g.textAlign = "left";
    g.textBaseline = "bottom";
    g.fillText("Energy: " + energy, field.height - 10); //10 px di margine
    g.restore();
}

/**
 * Disegna le comunicazioni ricevute
 * @param {int} type da 1 a 7 default ""
 */
function drawComms(type) {
    g.save();
    g.font = "20px Arial";
    g.filStyle = "white";
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

    g.fillText(comm, 10 , 10); //10 px di margine
    g.restore();
}   

/*********** CONTROLLER ***********/

//attaccarre add event listener window 
function captainKeyDown(KeyboardEvent){
    const commands = ["KeyW", "KeyA","KeyS","KeyD",
                      "ArrowUP","ArrowLeft","ArrowDown","ArrowRight","Space",
                      "Digit0","Digit1","Digit2","Digit3","Digit4","Digit5","Digit6","Digit7","Digit8","Digit9"];
    let keycode = KeyboardEvent.code;
    let sendKeyDown = (key) => send2server("keydown", key);
    switch(keycode){
        case "KeyW":
            keycode = "ArrowUP";
            break;
        case "KeyA":
            keycode = "ArrowLeft";
            break;
        case "KeyS":
            keycode = "ArrowDown";
            break;
        case "KeyD":
            keycode = "Arrowright";
            break;
    }   
    if(commands.findIndex(keycode) > -1)    
        sendKeyDown(keycode);
 }

function captainKeyUp(KeyboardEvent){
    const commands = ["KeyW", "KeyA","KeyS","KeyD",
                      "ArrowUP","ArrowLeft","ArrowDown","ArrowRight"];
    let keycode = KeyboardEvent.code;
    let sendKeyUp = (key) => send2server("keyup", key);
    switch(keycode){
        case "KeyW":
            keycode = "ArrowUP";
            break;
        case "KeyA":
            keycode = "ArrowLeft";
            break;
        case "KeyS":
            keycode = "ArrowDown";
            break;
        case "KeyD":
            keycode = "Arrowright";
            break;
    }   
    if(commands.findIndex(keycode) > -1)    
        sendKeyUp(keycode);
 }

function cannonKeyDown(KeyboardEvent){
    const commands = ["Digit0","Digit1","Digit2","Digit3","Digit4","Digit5","Digit6","Digit7","Digit8","Digit9"];
    let keycode = KeyboardEvent.code;
    let sendKeyDown = (key) => send2server("keydown", key);
    switch(keycode){
        case "Space":
        case "KeyW":
        case "ArrowUP":
            send2server("shoot", a);send2server();
            break;
        case "KeyA":
        case "ArrowLeft":
            //setinterval
            break;
        case "KeyD":
        case "ArrowRight":
            //setinterval
            break;
    } 
    if(commands.findIndex(keycode) > -1)    
        sendKeyDown(keycode);
 }

 function cannonKeyUp(KeyboardEvent){
    let keycode = KeyboardEvent.code;
    switch(keycode){
        case "KeyA":
        case "ArrowLeft":
            //clear interval
            break;
        case "KeyD":
        case "ArrowRight":
            //clear interval
            break;
    } 
 }



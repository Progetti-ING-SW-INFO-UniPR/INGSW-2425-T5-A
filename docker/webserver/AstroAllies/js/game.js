let field = document.getElementById("field"); // canvas
let g = field.getContext("2d"); //context

let imageCache = {};
/* ricevuto l'elenco dei percorsi delle immagini li carica in imageCache */
/**
 * @param {*} assets {"nome": "url", "altronome": "altrourl"} 
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
/* src stringa dell'assets (ship, asteroid...) 
    a in radianti
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

/* 
lenght=60px poichè la navicella è 75x75 -> dal centro in diagonale serve > 53
a in rad
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

// disegna il punteggio in alto al centro
function drawScore(score) { 
    g.save();
    g.font = "30px Arial";
    g.filStyle = "white";
    g.textAlign = "center";
    g.textBaseline = "top";
    g.fillText("Score: " + score, field.width / 2, 10); //10 px di margine
    g.restore();
}

//disegna l'energia in basso a sinistra
function drawEnergy(energy) {
    g.save();
    g.font = "20px Arial";
    g.filStyle = "white";
    g.textAlign = "left";
    g.textBaseline = "bottom";
    g.fillText("Energy: " + energy, field.height - 10); //10 px di margine
    g.restore();
}

// comm è un intero positivo
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
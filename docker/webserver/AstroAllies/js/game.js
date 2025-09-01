let field = document.getElementById("field"); // canvas
let g = document.getElementById("field").getContext("2d"); //context

const imageCache = {};
/* ricevuto l'elenco dei percorsi delle immagini li carica in imageCache */
function loadAssets(assets){
    console.log("Caricamento Assets...");
    assets.array.forEach(src => {
        const img = new Image();
        img.src = src;
        img.onload = () => {
            imageCache[src] = img;
        }
    });
    console.log("Assets Caricati!");
}
/* src stringa dell'assets (ship, asteroid...) */
function drawAsset(src, x, y, w, h, a){
    const img = imageCache[src];
    if(!img) return; //immagine non ancora caricata;

    g.save();
    g.translate(x+w/2, y+h/2); // riferimento il centro dell'immagine
    g.rotate(a * Math.PI /180); // converte in radianti, solo a se già in rad
    g.drawImage(img, -w/2, -h/2, w, h);
    g.restore();
}

/* 
x,y devono essere il centro della navicella
lenght=60px poichè la navicella è 75x75 -> dal centro in diagonale serve > 53
*/
function drawCannon(x, y, a, color="white", length=60) { 

  const xEnd = x + length * Math.cos(a * Math.PI / 180); // solo a se già in radianti
  const yEnd = y + length * Math.sin(a * Math.PI / 180);

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
    g.textAlign = "left";
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

// quelli attivi o i drop?
function drawPowerUps() {
	
}
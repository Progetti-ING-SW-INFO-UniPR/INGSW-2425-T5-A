<?php
#da copiare nella cartella /includes
session_start();
require_once('Game.php');

// Se la sessione non contiene già il gioco, lo crea e lo inizializza
if (!isset($_SESSION['gioco'])) {
    $_SESSION['gioco'] = new Game("gioco1");
    $_SESSION['gioco']->init();
}

$gioco = $_SESSION['gioco'];

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ob_start(); // Start output buffering

    $action = $_GET['action'] ?? '';

    if ($action === 'shoot') {
        $gioco->get_ship()->shoot(rand(1,360));
    } elseif ($action === 'update') {
        $gioco->update();
    } elseif ($action === 'print') {
        $asteroids = array_map('strval', $gioco->get_asteroids());
        $bullets   = array_map('strval', $gioco->get_bullets());
        
        $output = "Asteroids:\n" . implode("\n", $asteroids) . "\n\nBullets:\n" . implode("\n", $bullets);
    
    // Invia al browser
    echo $output;

        ob_end_clean(); // Pulisce tutto ciò che è stato "echoed" finora
        echo json_encode([
            'asteroids' => $asteroids ?: [],
            'bullets'   => $bullets   ?: []
        ]);
        exit;
    }

    $_SESSION['gioco'] = $gioco;
    ob_end_clean(); // Evita output extra
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <style>
    #history { margin-top: 20px; }
    #history li { margin-bottom: 5px; }
  </style>
</head>
<body>
  <button id="updateBtn">Update</button>
  <button id="shootBtn">Shoot</button>
  <button id="printBtn">Print</button>

  <ul id="history"></ul>

  <script>
    function ajaxAction(action) {
        fetch(`?ajax=1&action=${action}`)
            .then(response => {
                if (action === 'print') return response.json();
            })
            .then(data => {
                if (!data) return; // Shoot e Update non stampano nulla

                const history = document.getElementById('history');
                history.innerHTML = ''; // svuota la lista
                data.asteroids.forEach(a => {
                    const li = document.createElement('li');
                    li.textContent = "Asteroide: " + a;
                    history.appendChild(li);
                });
                data.bullets.forEach(b => {
                    const li = document.createElement('li');
                    li.textContent = "Bullet: " + b;
                    history.appendChild(li);
                });
            })
            .catch(err => console.error('Errore:', err));
    }

    document.getElementById('updateBtn').addEventListener('click', () => ajaxAction('update'));
    document.getElementById('shootBtn').addEventListener('click', () => ajaxAction('shoot'));
    document.getElementById('printBtn').addEventListener('click', () => ajaxAction('print'));
  </script>
</body>
</html>
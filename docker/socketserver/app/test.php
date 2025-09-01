<?php
require __DIR__ . '/vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class MyServer implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
		echo "Connesso";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
		echo $msg;
		$from->send($msg);
    }

    public function onClose(ConnectionInterface $conn) {
		echo "Disconnesso";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
		$conn->close();
    }
}

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new MyServer()
		)
	),
	8000
);

$server->run();
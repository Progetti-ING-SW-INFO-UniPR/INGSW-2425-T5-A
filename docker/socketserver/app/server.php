<?php
require __DIR__ . '/vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
require_once './includes/Room.php';
require_once './includes/Client.php';

function formatData($code, $data) {
	return '{"code":"'.$code.'", "data": '.$data.'}';
}

function formatStr($code, $str) {
	return '{"code":"'.$code.'", "data": "'.$str.'"}';
}

class MyServer implements MessageComponentInterface {
	protected array $rooms;
	protected SplObjectStorage $clients;

	public function __construct() {
		$this->rooms = [];
		$this->clients = new SplObjectStorage();
	}

	public function onOpen(ConnectionInterface $conn) {

	}

	public function onMessage(ConnectionInterface $from, $msg) {
		$msg = json_decode($msg);
		switch ($msg->code) {
			case "connect":
				$this->clients->attach($from);
				$this->clients[$from] = $msg->data;
				break;
			case "create":
				$id = uniqid();
				$this->rooms[$id] = new Room($id);
				$from->send(formatStr("room", $id));
				$this->rooms[$id]->addClient($from, $this->clients[$from]);
				break;
			case "join":
				$id = $msg->data;
				$room = $this->rooms[$id];
				if(!$room->isStarted()) {
					foreach ($room->getClients() as $user) {
						$from->send(formatStr("user", $user));
					}
					$room->addClient($from, $this->clients[$from]);
					$room->send(formatStr("user", $this->clients[$from]));
				}
				break;
			case "start":
				break;
			case "gas":
				break;
			case "sprint":
				break;
			case "turn":
				break;
			case "shoot":
				break;
			case "comm":
				break;
			case "sync":
				break;
			case "test":
				$from->send(formatData("test", json_encode($msg->data)));
				break;
		}
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
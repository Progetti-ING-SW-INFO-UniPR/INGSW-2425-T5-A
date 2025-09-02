<?php
require __DIR__ . '/vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
require_once './includes/Room.php';

function formatData($code, $data) {
	return '{"code":"'.$code.'", "data": '.json_encode($data).'}';
}

function formatStr($code, $str) {
	return '{"code":"'.$code.'", "data": "'.$str.'"}';
}

function isValidUsername($username) : bool {
	return preg_match("/^[A-Za-z0-9]+$/", $username) == 1;
	// return true;
}

function attach($socket, $username, $list) {
	$oldSocket = null;
	if(!isset($list[$socket])){
		foreach ($list as $client) {
			if ($list[$client] == $username){
				$list->detach($client);
				$oldSocket = $client;		
				break;
			}
		}
	}
	return $oldSocket;
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
		echo $msg."\n";
		$msg = json_decode($msg);
		switch ($msg->code) {
			case "connect":
				if(isValidUsername($msg->data)) {			
					$this->clients->attach($from);
					$this->clients[$from] = $msg->data;
				}
				break;
			case "create":
				$id = uniqid();
				try {
					$maxPlayers = intval($msg->data);
					if($maxPlayers > 5 || $maxPlayers < 1) break;
					$this->rooms[$id] = new Room($id, $maxPlayers);
					$from->send(formatStr("room", $id));
				} catch (Error $err) {
					echo $err;
				}
				break;
			case "join":
				$id = $msg->data;
				try {
					$room = $this->rooms[$id];
					if(!$room->isStarted()) {
						if(!$room->connect($from, $this->clients[$from])) {
							$err = ["name" => "Stanza Piena",
									"desc" => "La Stanza a cui hai provato a connetterti è già al completo"];
							$from->send(formatData("error", $err));
						}
					} else {
						$err = ["name" => "Partita già in corso",
								"desc" => "La Partita a cui hai provato a connetterti è già cominciata"];
						$from->send(formatData("error", $err));
					}
				} catch (Error $err) {
					echo $err;
				}
				break;
			case "start":
				break;
			case "keydown":
				break;
			case "keyup":
				break;
			case "shoot":
				break;
			case "sync":
				break;
			case "test":
				$from->send(formatData("test", json_encode($msg->data)));
				break;
		}
	}

	public function onClose(ConnectionInterface $conn) {
		$this->clients->detach($conn);
		foreach ($this->rooms as $room) {
			if($room->getClients()->contains($conn)) {
				$room->disconnect($conn);
			}
		}
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
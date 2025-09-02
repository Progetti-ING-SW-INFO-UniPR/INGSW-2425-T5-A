<?php
require __DIR__ . '/vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
require_once './includes/Room.php';

function formatData($code, $data) {
	return '{"code":"'.$code.'", "data": '.$data.'}';
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
				$this->rooms[$id] = new Room($id);
				$from->send(formatStr("room", $id));
				break;
			case "join":
				$id = $msg->data;
				try {
					$room = $this->rooms[$id];
					if(!$room->isStarted()) {
						$room->connect($from, $this->clients[$from]);
					}
				} catch (Exception $err) {
					echo $err;
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
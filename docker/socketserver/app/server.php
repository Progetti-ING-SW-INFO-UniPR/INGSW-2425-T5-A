<?php
require __DIR__ . '/vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
require_once './includes/Room.php';

function formatJson($code, $json) {
	return '{"code":"'.$code.'", "data": '.$json.'}';
}

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
	protected SplObjectStorage $clientsRoom;

	public function __construct() {
		$this->rooms = [];
		$this->clients = new SplObjectStorage();
		$this->clientsRoom = new SplObjectStorage();
	}

	private static function random_id($length=6) : string {
		$random = '';
		for ($i = 0; $i < $length; $i++) {
			$random .= chr(rand(ord('A'), ord('Z')));
		}
		return $random;
	}

	private function uniq_room_id() : string {
		$id = $this->random_id();
		while(isset($this->rooms[$id]))
			$id = $this->random_id();
		return $id;
	}

	public function onOpen(ConnectionInterface $conn) {
		$this->clients->attach($conn);
	}

	public function onMessage(ConnectionInterface $from, $msg) {
		// echo $msg."\n";
		$msg = json_decode($msg);
		switch ($msg->code) {
			case "connect":
				if(isValidUsername($msg->data)) {
					$this->clients[$from] = $msg->data;
					echo "Client\t| $msg->data | Connected\n";
				}
				break;
			case "create":
				$id = $this->uniq_room_id();
				try {
					$maxPlayers = intval($msg->data);
					if($maxPlayers > 5 || $maxPlayers < 1) break;
					$this->rooms[$id] = new Room($id, $maxPlayers);
					$from->send(formatStr("room", $id));
					echo "Room\t| $id | Created\n";
				} catch (Error $err) {
					echo $err."\n";
				}
				break;
			case "join":
				$id = $msg->data;
				try {
					$room = $this->rooms[$id];
					if(!$room->isStarted()) {
						if($room->connect($from, $this->clients[$from])) {
							$this->clientsRoom->attach($from);
							$this->clientsRoom[$from] = $id;
						} else {
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
					echo $err."\n";
				}
				break;
			case "start":
				$id = $msg->data;
				if(array_key_exists($id, $this->rooms)) {
					$room = $this->rooms[$id];
					if($room->getCaptain() == $from && $room->isFull()) {
						echo "Room\t| $room->id Starting\n";
						$room->start();
					}
				}
				break;
			case "keydown":
				$id = $this->clientsRoom[$from];
				if(!isset($this->rooms[$id])) break;
				$room = $this->rooms[$id];
				if(!$room->isStarted()) break;
				$game = $room->getGame();
				$ship = $game->get_ship();

				// Comunicazioni
				if(str_starts_with($msg->data, "Digit")) {
					$digit = ltrim($msg->data, "Digit");
					try {
						$digit = intval($digit);
						if($digit < 10 && $digit >-1) {
							$game->set_communication($this->clients[$from], $digit);
							break;
						}
					} catch(Error $err) {
						echo $err."\n";
					}
				}

				// Altri comandi del Capitano
				if(!$room->getCaptain() == $from) break;
				switch($msg->data) {
					case "ArrowUp":
						$ship->go_foward();
						break;
					case "ArrowLeft":
						$ship->rotate_left();
						break;
					case "ArrowRight":
						$ship->rotate_rigth();
						break;
					case "Space":
						$ship->boost();
						break;
				}
				break;
			case "keyup":
				$id = $this->clientsRoom[$from];
				if(!isset($this->rooms[$id])) break;
				$room = $this->rooms[$id];
				if(!$room->isStarted()) break;
				$game = $room->getGame();
				$ship = $game->get_ship();

				// Comandi del Capitano
				if(!$room->getCaptain() == $from) break;
				switch($msg->data) {
					case "ArrowUp":
						$ship->stop();
						break;
					case "ArrowLeft":
					case "ArrowRight":
						$ship->rotate_stop();
						break;
				}
				break;
			case "shoot":
				$id = $this->clientsRoom[$from];
				if(!isset($this->rooms[$id])) break;
				$room = $this->rooms[$id];
				if(!$room->isStarted()) break;
				if($room->getCaptain() == $from) break;
				try {
					$room->getGame()->get_ship()->shoot($msg->data);
				} catch(Error $err) {
					echo $err."\n";
				}
				break;
			case "sync":
				break;
			case "test":
				$from->send(formatData("test", json_encode($msg->data)));
				break;
		}
	}

	public function onClose(ConnectionInterface $conn) {
		if (!$this->clients->contains($conn)) return;
		
		$username = $this->clients[$conn];

		if ($this->clientsRoom->contains($conn)) {
			$id = $this->clientsRoom[$conn];
			$this->clientsRoom->detach($conn);
			if(isset($this->rooms[$id])) {
				$room = $this->rooms[$id];
				$room->disconnect($conn);
				if($room->isEmpty()) {
					if($room->isStarted())
						$room->stop();
					unset($this->rooms[$id]);
					echo "Room\t| $room->id | Dropped\n";
				}
			}
		}
		$this->clients->detach($conn);
		
		echo "Client\t| $username | Disonnected\n";
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
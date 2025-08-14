<?php
/**
 * arr = [.10, .65, .05, .05, .05, .05, .05]
 * rand = rand(100)
 * perc = arr[0]
 * i = 0
 * while perc <= rand
 * 	perc += arr[i]
 * 	i++
 */
class ClientSocket
{
	public readonly string $id;
	private Socket $socket;
	private RoomSocket $room;
	private string $username;

	public function __construct(Socket $client, string $id = "") {
		$this->socket = $client;
		if($id == "") $id = uniqid();
		$this->id = $id;
		$this->username = "";
	}

	public function isInRoom() {
		return $this->room != "";
	}

	public function setRoom(RoomSocket $room) {
		$this->room = $room;
	}

	public function getRoom() {
		return $this->room;
	}
	
	public function setUsername(string $username) {
		$this->username = $username;
	}

	public function getUsername() {
		return $this->username;
	}

	public function getSocket() : Socket {
		return $this->socket;
	}
}

class RoomSocket
{
	public readonly string $id;
	/* @var ClientSocket[] $players */
	private array $players;

	public function __construct(string $id = "") {
		if($id == "") $id = uniqid("room_");
		$this->id = $id;
		$this->players = [];
	}

	// public function isSocketPresent($socket) {
	// 	if(array_find($this->players, function($v, $k) {return $v->socket == $socket;}))
	// 		return true;
	// }

	public function getPlayers(): array {
		return $this->players;
	}

	public function addPlayer(ClientSocket $player) {
		$this->players[$player->id] = $player;
	}
}

class SocketServer
{
	private $ip;
	private $port;
	private $masterSocket;
	private $clients = [];
	private $rooms = []; // roomid: {Game: Game, PlayersSockets: Socket[], PlayerStatus: string[]}

	public function __construct($ip, $port)
	{
		$this->ip = $ip;
		$this->port = $port;
		$this->createSocket();
		$this->bindAndListen();
		$this->acceptConnections();
	}

	private function createSocket()
	{
		$this->masterSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		if ($this->masterSocket === false) {
			die('Error: ' . socket_strerror(socket_last_error()));
		}
	}

	private function bindAndListen()
	{
		if (!socket_bind($this->masterSocket, $this->ip, $this->port)) {
			die('Error: ' . socket_strerror(socket_last_error()));
		}

		if (!socket_listen($this->masterSocket)) {
			die('Error: ' . socket_strerror(socket_last_error()));
		}

		echo "Listening for connections on $this->ip:$this->port.\n";
	}

	private function acceptConnections()
	{
		while (true) {
			$readSockets = $this->getReadSockets();
			usleep(1000);

			$write = $except = [];
			if (socket_select($readSockets, $write, $except, 0) > 0) {
				foreach ($readSockets as $socket) {
					usleep(10000);

					if ($socket === $this->masterSocket) {
						$clientSocket = socket_accept($this->masterSocket);
						$this->handleHandshake($clientSocket);
						$this->handleNewConnection($clientSocket);
					} else {
						$this->handleData($socket);
					}
				}
			}
		}
	}

	private function getReadSockets()
	{
		$readSockets = [$this->masterSocket];

		foreach ($this->clients as $client) {
			$readSockets[] = $client;
		}

		return $readSockets;
	}

	private function handleNewConnection($clientSocket)
	{
		$clientId = uniqid();
		$this->clients[$clientId] = new ClientSocket($clientSocket, $clientId);
		$this->sendResponse($clientSocket, "{id: \"" . $clientId . "\"}");
		echo "New Client Connected with ID: $clientId\n";
	}

	private function handleData($clientSocket)
	{
		$data = socket_read($clientSocket, 1024);
		$decodedData = $this->decodeMessage($data);

		if ($decodedData === false || $decodedData === '') {
			$this->disconnectClient($clientSocket);
		} else {
			$jsonData = json_decode($decodedData);
			switch($jsonData->type) {
				case "username":
					$this->clients[$jsonData->id]->setUsername($jsonData->username);
					break;
				case "join":
					if(!isset($this->clients[$jsonData->id])) break;
					$socket = $this->clients[$jsonData->id];
					if($clientSocket != $socket->getSocket()) break; // Check se i socket coincidono. Contrallare che funzioni
					if(!isset($this->rooms[$jsonData->roomid])) break;
					$room = $this->rooms[$jsonData->roomid];

					if(isset($room->getPlayers()[$jsonData->id])) break;
					foreach($room->getPlayers() as $s) {
						$this->sendResponse($clientSocket, "{type: \"join\", username: \"" . $s->getUsername() . "\"}");
					}
					$room->addPlayer($socket);
					$socket->setRoom($room);
					foreach($room->getPlayers() as $s) {
						$this->sendResponse($s->getSocket(), "{type: \"join\", username: \"" . $socket->getUsername() . "\"}");
					}
					break;
				case "create":
					if(!isset($this->clients[$jsonData->id])) break;
					$socket = $this->clients[$jsonData->id];
					if($clientSocket != $socket->getSocket()) break; // Check se i socket coincidono. Contrallare che funzioni
					$room = new RoomSocket();
					$room->addPlayer($socket);
					$socket->setRoom($room);

			}
		}
	}

	private function disconnectClient($clientSocket)
	{
		$clientId = array_search($clientSocket, $this->clients, true);
		unset($this->clients[$clientId]);
		socket_close($clientSocket);
		echo "Cliend disconnected, ID: $clientId\n";
	}

	private function sendResponse($clientSocket, $message)
	{
		$encodedMessage = $this->encodeMessage($message);
		socket_write($clientSocket, $encodedMessage, strlen($encodedMessage));
	}

	private function encodeMessage($message)
	{
		$firstByte = 0x81;
		$length = strlen($message);

		if ($length <= 125) {
			$encodedData = chr($firstByte) . chr($length) . $message;
		} elseif ($length <= 65535) {
			$encodedData = chr($firstByte) . chr(126) . pack('n', $length) . $message;
		} else {
			$encodedData = chr($firstByte) . chr(127) . pack('NN', 0, $length) . $message;
		}

		return $encodedData;
	}

	private function decodeMessage($data)
	{
		$opcode = ord($data[0]) & 0x0F;

		if ($opcode !== 0x01) {
			return ''; // Sadece metin verilerini destekliyoruz, farklı bir opcode geldiyse boş bir dize döndürün.
		}

		$payloadLength = ord($data[1]) & 127;

		if ($payloadLength === 126) {
			$masks = substr($data, 4, 4);
			$payload = substr($data, 8);
		} elseif ($payloadLength === 127) {
			$masks = substr($data, 10, 4);
			$payload = substr($data, 14);
		} else {
			$masks = substr($data, 2, 4);
			$payload = substr($data, 6);
		}

		$decodedData = '';
		for ($i = 0; $i < strlen($payload); ++$i) {
			$decodedData .= $payload[$i] ^ $masks[$i % 4];
		}

		return $decodedData;
	}

	private function handleHandshake($clientSocket)
	{
		$headers = [];
		$lines = preg_split("/\r\n/", socket_read($clientSocket, 4096), -1, PREG_SPLIT_NO_EMPTY);
		foreach ($lines as $line) {
			$line = rtrim($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$headers[$matches[1]] = $matches[2];
			}
		}

		$this->sendHandshakeResponse($clientSocket, $headers);
		echo "Handshake Completed.\n";
	}

	private function sendHandshakeResponse($clientSocket, $headers)
	{
		$key = $headers['Sec-WebSocket-Key'];
		$acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

		$response = "HTTP/1.1 101 Switching Protocols\r\n";
		$response .= "Upgrade: websocket\r\n";
		$response .= "Connection: Upgrade\r\n";
		$response .= "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";

		socket_write($clientSocket, $response, strlen($response));
	}
}

$ss = new SocketServer("0.0.0.0", "9001");
?>
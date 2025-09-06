<?php
require 'vendor/autoload.php';
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
require_once 'Game.php';
require_once 'Database.php';

//VECTOR 
$VECTOR = new Vector(0,0);
//GAME
$GAME_NULL = Game::null_game();
//ITEM
$ITEM_SIZE = 10;
$POINT = 15;
//BULLET
$BULLET_VEL = 10;
$BULLET_SIZE = 8;
$BULLET_VECTOR = new Vector(0, $BULLET_VEL);
$BULLET_HITBOX = new Box(600,400,$BULLET_SIZE,$BULLET_SIZE);
$BULLET = new Bullet($BULLET_VECTOR,$BULLET_HITBOX, 1, $GAME_NULL);
// SPACESHIP 
$MAX_ENERGY = 20;
$ACCELERATION = 0.50;
$MAX_VEL = 10;
$FRICTION = 1.05;
$BOOST = 8;
$SHIP_HITBOX = new Box(600,400,40,40);
$ROTATION_VEL = 0.2;
$SPACESHIP = new Spaceship($VECTOR, $SHIP_HITBOX, $BULLET, $MAX_ENERGY, 0, $ROTATION_VEL, $ACCELERATION, $MAX_VEL, $BOOST, $GAME_NULL);
// ASTEROID
$ASTEROID_SIZE = 30; 
// GAME
$SPAWNING_FIELD = new Box(-100,-100,1400,1000);
$PLAYING_FIELD = new Box(0,0,1200,800);
$EXFIL_AREA = new Box(0,800+$SHIP_HITBOX->get_height(),1200,100);

class Room {
	public readonly string $id;
	private SplObjectStorage $clients;
	private ConnectionInterface $captain;
	private Game $game;
	private int $maxPlayers;
	private TimerInterface $loop;
	

	public function __construct($id, $maxPlayers)
	{
		$this->clients = new SplObjectStorage();
		$this->id = $id;
		$this->maxPlayers = $maxPlayers;
	}

	public function addClient($socket, $username) {
		$this->clients->attach($socket);
		$this->clients[$socket] = $username;
		if($this->clients->count() == 1) {
			$this->captain = $socket;
		}
	}

	public function removeClient($socket) {
		$this->clients->detach($socket);
	}

	public function connect($socket, $username) {
		if($this->clients->count() >= $this->maxPlayers) return false;
		foreach ($this->clients as $client) {
			$socket->send(formatStr("connected", $this->clients[$client]));
		}
		$this->addClient($socket, $username);
		$this->send(formatStr("connected", $username));
		$socket->send(formatStr("captain", $this->clients[$this->captain]));
		$socket->send(formatData("maxplayers", $this->maxPlayers));
		
		echo "Room\t| $this->id | $username connected, {$this->clients->count()} inside\n";
		return true;
	}

	public function disconnect($socket) {
		$username = $this->clients[$socket];
		$this->clients->detach($socket);
		$this->send(formatStr("disconnected", $username));
		if($this->captain == $socket && $this->clients->count() > 0) {
			foreach($this->clients as $client) {
				$this->captain = $client;
				break;
			}
			$this->send(formatStr("captain", $this->clients[$this->captain]));
		}
		echo "Room\t| $this->id | $username disconnected, {$this->clients->count()} remaining\n";
	}

	public function setGame($game) {
		$this->game = $game;
	}
	
	public function getGame() : Game{
		return $this->game;
	}

	public function getClients() : SplObjectStorage {
		return $this->clients;
	}

	public function getCaptain() : ConnectionInterface {
		return $this->captain;
	}

	public function mainloop() {
		$this->game->update();
		switch ($this->game->get_status()) {
			case Status::Running:
				break;
			case Status::Lost:
			case Status::Won:
				$data = [
					"status" => $this->game->get_status()->value,
					"score" => $this->game->get_score()
				];
				$this->send(formatData("gameover", $data));
				$this->updateScores();
				$this->stop();
				return;
			case Status::Pause:
				break;
		}
		$json = $this->game->get_json();
		$this->send(formatJson("game", $json));
	}

	private function updateScores() {
		$db = new Database();
		$query = "UPDATE Utente SET Punteggio = Punteggio + ? WHERE Username = ?";
		foreach ($this->clients as $client) {
			$db->query($query, [$this->game->get_score(), $this->clients[$client]]);
		}
	}

	public function start() {
		global $PLAYING_FIELD, $SPAWNING_FIELD, $EXFIL_AREA, $FRICTION, $SPACESHIP, $ASTEROID_SIZE, $POINT;
		if($this->isStarted()) {
			if($this->game->get_status() == Status::Running) {
				return;
			}
			if($this->game->get_status() == Status::Pause) {
				$this->game->toggle_pause();
				return;
			}
		}
		$this->game = new Game($this->id, $PLAYING_FIELD->deep_copy(), $SPAWNING_FIELD->deep_copy(), $EXFIL_AREA->deep_copy(), $FRICTION, $SPACESHIP->deep_copy(), $ASTEROID_SIZE, $POINT);
		foreach ($this->clients as $sock) {
			$this->game->set_communication($this->clients[$sock], 0);
		}
		echo "Room\t| $this->id | Started\n";

		$this->send(formatStr("start", ""));
		$room = $this;
		$this->loop = Loop::addPeriodicTimer(1/30, function () use ($room) {$room->mainloop();});
	}

	public function stop() {
		Loop::cancelTimer($this->loop);
		unset($this->game);
		echo "Room\t| $this->id | Stopped\n";
	}

	public function isStarted() {
		return isset($this->game);
	}

	public function send($message) {
		foreach ($this->clients as $socket) {
			$socket->send($message);
		}
	}

	public function isEmpty() : bool{
		return $this->clients->count() == 0;
	}

	public function isFull() : bool{
		return $this->clients->count() >= $this->maxPlayers;
	}
}
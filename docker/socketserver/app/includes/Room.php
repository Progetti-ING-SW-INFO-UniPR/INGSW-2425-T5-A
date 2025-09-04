<?php
require 'vendor/autoload.php';
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
require_once 'Game.php';

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
// GAME
$SPAWNING_FIELD = new Box(0,0,1400,1000);
$PLAYING_FIELD = new Box(200,200,1200,800);
$EXFIL_AREA = new Box(0,750,1200,750);
// SPACESHIP 
$MAX_ENERGY = 20;
$ACCELERATION = 1;
$MAX_VEL = 10;
$FRICTION = 1.05;
$BOOST = 7;
$SHIP_HITBOX = new Box(600,400,40,40);
$ROTATION_VEL = 0.2;
$SPACESHIP = new Spaceship($VECTOR, $SHIP_HITBOX, $BULLET, $MAX_ENERGY, 0, $ROTATION_VEL, $ACCELERATION, $MAX_VEL, $BOOST, $GAME_NULL);
// ASTEROID
$ASTEROID_SIZE = 30; 

class Room {
	public readonly string $id;
	private SplObjectStorage $clients;
	private ConnectionInterface $captain;
	private Game $game;
	private bool $started;
	private int $maxPlayers;
	private TimerInterface $loop;
	

	public function __construct($id, $maxPlayers)
	{
		$this->clients = new SplObjectStorage();
		$this->started = false;
		$this->id = $id;
		$this->maxPlayers = $maxPlayers;
	}

	public function addClient($socket, $username) {
		$this->clients->attach($socket);
		$this->clients[$socket] = $username;
		if($this->clients->count() == 1) {
			$this->captain = $socket;
			$this->send('{"code": "captain", "data":"'.$this->clients[$this->captain].'"}');
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
		return true;
	}

	public function disconnect($socket) {
		$username = $this->clients[$socket];
		$this->clients->detach($socket);
		$this->send(formatStr("disconnected", $username));
		if($this->captain == $socket) {
			foreach($this->clients as $client) {
				$this->captain = $client;
				break;
			}
			$this->send(formatStr("captain", $this->clients[$this->captain]));
		}
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

	public function start() {
		global $PLAYING_FIELD, $SPAWNING_FIELD, $EXFIL_AREA, $FRICTION, $SPACESHIP, $ASTEROID_SIZE, $POINT;
		$this->game = new Game($this->id, $PLAYING_FIELD->deep_copy(), $SPAWNING_FIELD->deep_copy(), $EXFIL_AREA->deep_copy(), $FRICTION, $SPACESHIP->deep_copy(), $ASTEROID_SIZE, $POINT);
		$this->started = true;
		$room = $this;
		$game = $this->game;
		echo 'Room:'.$room->id.' Started'."\n";
		$this->send(formatStr("start", ""));
		$this->loop = Loop::addPeriodicTimer(1/30, function () use ($room, $game){
			$game->update();
			$json = $game->get_json();
			$room->send(formatJson("game", $json));
			// if($room->getGame()->get_tick() == 30*60) {
			// 	echo 'Closing room:'.$room->id.' after ~60 seconds'."\n";
			// 	$room->stop();
			// }
		});
	}

	public function stop() {
		Loop::cancelTimer($this->loop);
	}

	public function isStarted() {
		return $this->started;
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
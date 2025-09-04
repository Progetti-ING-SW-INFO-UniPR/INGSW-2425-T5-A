<?php
require 'vendor/autoload.php';
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
require_once 'Game.php';

class Room {
	public readonly string $id;
	private SplObjectStorage $clients;
	private ConnectionInterface $captain;
	private Game $game;
	private bool $started;
	private int $maxPlayers;
	private array $comms;
	private TimerInterface $loop;
	private int $tick;
	

	public function __construct($id, $maxPlayers)
	{
		$this->clients = new SplObjectStorage();
		$this->started = false;
		$this->id = $id;
		$this->maxPlayers = $maxPlayers;
		$this->tick = 0;
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

	public function start() {
		$this->game = new Game($this->id);
		$this->started = true;
		$room = $this;
		$game = $this->game;
		echo 'Room:'.$room->id.' Started'."\n";
		$this->send(formatStr("start", ""));
		$this->loop = Loop::addPeriodicTimer(1/20, function () use ($room, $game){
			$game->update();
			$json = $game->get_json();
			$room->send(formatStr("game", $json));
			$room->nextTick();
			if($room->getTick() == 20*5) {
				echo 'Closing room:'.$room->id.' after ~5 seconds'."\n";
				$room->stop();
			}
		});
	}

	public function getTick() {
		return $this->tick;
	}

	public function nextTick() {
		$this->tick++;
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
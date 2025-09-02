<?php
require 'vendor/autoload.php';
use Ratchet\ConnectionInterface;
require_once 'Game.php';

class Room {
	public readonly string $id;
	private SplObjectStorage $clients;
	private ConnectionInterface $captain;
	private Game $game;
	private bool $started;
	private int $maxPlayers;
	private array $comms;

	public function __construct($id, $maxPlayers)
	{
		$this->clients = new SplObjectStorage();
		$this->started = false;
		$this->id = $id;
		$this->maxPlayers = $maxPlayers;;
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
		$this->started = true;
		$this->game = new Game($this->id);
	}

	public function isStarted() {
		return $this->started;
	}

	public function send($message) {
		foreach ($this->clients as $socket) {
			$socket->send($message);
		}
	}
}
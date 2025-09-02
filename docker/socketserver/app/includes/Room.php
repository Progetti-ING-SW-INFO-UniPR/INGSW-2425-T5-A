<?php
require 'vendor/autoload.php';
require_once 'Game.php';

class Room {
	public readonly string $id;
	private SplObjectStorage $clients;
	private Game $game;
	private bool $started;

	public function __construct($id)
	{
		$this->clients = new SplObjectStorage();
		$this->started = false;
		$this->id = $id;
	}

	public function addClient($socket, $username) {
		$this->clients->attach($socket);
		$this->clients[$socket] = $username;
	}

	public function removeClient($socket) {
		$this->clients->detach($socket);
	}

	public function connect($socket, $username) {
		foreach ($this->clients as $client) {
			$socket->send(formatStr("connected", $this->clients[$client]));
		}
		$this->addClient($socket, $username);
		$this->send(formatStr("connected", $username));
	}

	public function disconnect($socket) {
		$username = $this->clients[$socket];
		$this->clients->detach($socket);
		$this->send(formatStr("disconnected", $username));
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
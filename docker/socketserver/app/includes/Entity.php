<?php
require_once("Vector.php");
require_once("Box.php");
require_once("Game.php");

abstract class Entity
{
    protected Vector $velocity; // vettore velocità angolo=direzione norma=velocità
    protected Box $hitbox;
    protected Game $game; //default null così da creare game 

    public function __construct(Vector $velocity, Box $hitbox, Game $game){
        $this->velocity = $velocity;
        $this->hitbox = $hitbox;
		$this->game = $game;
    }
    public function deep_copy():Entity{
	    return unserialize(serialize($this));
    }
    public function get_velocity():Vector{
        return $this->velocity;
    }
    public function set_velocity(Vector $v){
        $this->velocity = $v;
    }
    public function set_velocity_comps(float $a, float $n){
        $v = new Vector($a,$n);
        $this->set_velocity($v);
    }
    public function get_hitbox():Box{
        return $this->hitbox;
    }
    public function set_hitbox(Box $b){
        $this->hitbox = $b;
    }
    public function set_hitbox_comps(float $x, float $y, float $w, float $h){
        $b = new Box($x, $y, $w, $h);
        $this->set_hitbox($b);
    }
    public function get_game():Game{
        return $this->game;
    }
    public function set_game(Game $g){
        $this->game = $g;
    }
    public function update(): void{
        $this->hitbox->move($this->velocity->get_dx(), $this->velocity->get_dy());
    }
    public function __toString(): string {
        $s = "";
        if(!is_null($this->game))
            $s = "Game:".$this->game->get_id();

        return "Dir:" . $this->velocity . " Hitbox:" . $this->hitbox . $s;  
    }
    /*
    public function move(float $new_dir): void{
        $this->velocity->sum_norm($new_dir);
    }
    */
    public function check_collision(Entity $entity): bool{
        return $this->hitbox->check_overlap($entity->hitbox);
    }
    abstract public function on_collision(Entity $entity);

}
?>

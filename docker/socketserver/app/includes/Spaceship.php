<?php 
require_once("Entity.php");
require_once("Bullet.php");

class Spaceship extends Entity{
    protected int $energy; 
    protected int $max_energy;
    protected Bullet $ammo_type; // creare default bullet.
    protected float $roto_dir;
    protected float $roto_vel;
    protected Vector $current_acceleration;
	protected float $acceleration;
    protected float $boost;

    public function __construct(Vector $dir, Box $box, Bullet $b, int $e, float $ad, float $av, float $acc, float $boost ,Game $g){
        parent::__construct($dir,$box, $g);
        $this->max_energy = $e;
        $this->energy = $e;
        $this->ammo_type = $b->deep_copy(); 
        $this->roto_dir = $ad;
        $this->roto_vel = $av;
        $this->current_acceleration = new Vector(0, 0);
        $this->acceleration = $acc;
        $this->boost = $boost;
    }
    function deep_copy():Spaceship{
	    return unserialize(serialize($this));
    }
    public function get_max_energy():int {
        return $this->max_energy;
    }
    public function set_max_energy(int $e){
        $this->max_energy = $e;
    }
    public function get_energy(): int{
        return $this->energy;
    }
    public function set_energy(int $e){
        $this->energy = $e;
    }
    public function get_ammo(): Bullet{
        return $this->ammo_type;
    }
    public function set_ammo(Bullet $b){
        $this->ammo_type = $b;
    }
    public function get_roto_dir(): float{
        return $this->roto_dir;
    }
    public function set_roto_dir(float $ad){
        $this->roto_dir = $ad;
    }
    public function get_roto_vel(): float{
        return $this->roto_vel;
    }
    public function set_roto_vel(int $av){
        $this->roto_vel = $av;
    }
    public function get_acceleration(): float{
        return $this->acceleration;
    }
    public function set_acceleration(float $a){
        $this->acceleration = $a;
    }
    public function get_current_acceleration(): Vector{
        return $this->current_acceleration;
    }
    public function set_current_acceleration(Vector $a){
        $this->current_acceleration = $a;
    }
    public function __toString():string{
        $e = parent::__toString($this);
        return $e . "MaxEnergy:" . $this->max_energy . "Energy:" . $this->energy . "Ammo:" . $this->ammo_type . "RDir:" . $this->roto_dir . "RVel:" . $this->roto_vel . "Acc:" . $this->acceleration;
    }

    /**
     * Crea e aggiunge al relativo array un proiettile.
     * 
     * @param alfa angolo del cannone 
     * 
     * Se la navicella ha abbastanza energia ne consuma per
     * creare e aggiungere al relativo array un proiettile @see add_bullet().
     */
    public function shoot(float $alfa){ //alfa è l'angolo del cannone
        if($this->energy > 0){
            $this->energy -= 1;
            $dir = new Vector($alfa,$this->ammo_type->get_velocity()->get_norm());

            $offset_x = ($this->hitbox->get_width() - $this->ammo_type->get_hitbox()->get_width())/2; //posizione proiettile centrata alla navicella
            $offset_y = ($this->hitbox->get_height() - $this->ammo_type->get_hitbox()->get_height())/2;
            $this->ammo_type->get_hitbox()->set_x($this->hitbox->get_x() + $offset_x);
            $this->ammo_type->get_hitbox()->set_y($this->hitbox->get_y() + $offset_y);

            $b = new Bullet($dir, clone $this->ammo_type->get_hitbox(), $this->ammo_type->get_rank());
            $this->get_game()->add_bullet($b);
        }
    }
    /**
     * Aumenta la velocità della navicella. 
     * 
     * Se la navicella ha abbastanza energia ne consuma per
     * aumentare la norma della velocità della navicella @see sum_norm().
     */
    public function boost(){
        if($this->energy > 0){
            $this->energy -= 1;
            $this->velocity->sum_norm($this->boost);
        }
    }

    /**
     * Aggiorna la posizione della navicella
     * 
     * Somma alla velocità della navicella la sua accelerazione e l'attrito
     * per impedire che la nacivella vada all'indietro in caso di somma negativa 
     * forza la velocità a zero.
     */
    public function update(): void
    {
		$this->current_acceleration->sum_alfa($this->roto_dir);
        $this->velocity->sum_vector($this->current_acceleration);
        if($this->velocity->get_norm()-$this->game->get_friction() >= 0){
            $this->velocity->sum_norm(-$this->game->get_friction());
        } else {
            $this->velocity->set_norm(0); 
        }
        parent::update();
    }

	public function rotate_left():void{
		$this->roto_dir = $this->roto_vel;
	}
	public function rotate_rigth():void{
		$this->roto_dir = -$this->roto_vel;
	}
	public function rotate_stop():void{
		$this->roto_dir = 0;
	}

	public function foward():void{
		$this->current_acceleration->set_norm($this->acceleration);
	}
	public function stop():void{
		$this->current_acceleration->set_norm(0);
	}

    /**
     * Gestisce la reazione alla collisione 
     * 
     * @param e entità a cui reagire in base al tipo
     * 
     * In caso di collisione con Asteroid chiama @see game_over()
     * Se collide con Item ne attiva l'effetto sulla navicella.
     */
    public function on_collision(Entity $e)
    {
        if(is_a($e, 'Asteroid'))
            $this->game->game_over();
        else if(is_a($e, 'Item')){
            switch($e->get_type()){
                case 2: // bullet vel upgrade
                    $this->ammo_type->get_velocity()->sum_norm(5);
                    break;
                case 3: // bullet rank upgrade
                    if($this->ammo_type->get_rank() < 3)
                        $this->ammo_type->set_rank($this->ammo_type->get_rank()+1);
                    break;
                case 4: // bullet size upgrade
                    if($this->ammo_type->get_hitbox()->get_width()+5 < $this->hitbox->get_width())
                        $this->ammo_type->set_hitbox_comps(0,0,$this->ammo_type->get_hitbox()->get_width()+5,$this->ammo_type->get_hitbox()->get_height()+5);
                    break;
                default: // non fa nulla
                    break;
            }
        }
    }

	public function get_json():string{
		return str_replace('}', 
						   ', "a":'.$this->current_acceleration->get_alfa().'}',
						   $this->hitbox->get_json());
	}

}

?>
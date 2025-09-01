<?php
require_once("Vector.php");
require_once("Box.php");
require_once("Entity.php");
require_once("Item.php");
require_once("Asteroid.php");
require_once("Bullet.php");
require_once("Spaceship.php");

//VECTOR 
const DEFAULT_VECTOR = new Vector(0,0);
//ITEM
const ITEM_SIZE = 10;
const DEFAULT_PNT = 15;
//BULLET
const DEFAULT_BULLET_VEL = 1;
const BULLET_SIZE = 4;
const DEFAULT_BULLET_HITBOX = new Box(600,400,BULLET_SIZE,BULLET_SIZE);
const DEFAULT_BULLET = new Bullet(DEFAULT_VECTOR,DEFAULT_BULLET_HITBOX, 1);
// GAME
const SPAWNING_FIELD = new Box(0,0,1400,1000);
const PLAYING_FIELD = new Box(200,200,1200,800);
const EXFIL_AREA = new Box(0,750,1200,750);
// SPACESHIP 
const DEFAULT_MAX_ENERGY = 20;
const DEFAULT_ACCELERATION = 10;
const DEFAULT_FRICTION = 5;
const DEFAULT_BOOST = 5;
const SHIP_HITBOX = new Box(600,400,40,40);
const DEFAULT_SHIP = new Spaceship(DEFAULT_VECTOR,SHIP_HITBOX,DEFAULT_BULLET, DEFAULT_MAX_ENERGY, 0, 10, DEFAULT_ACCELERATION, DEFAULT_BOOST);
// ASTEROID
const ASTEROID_SIZE = 30; 

// RNG DROP RATE
//   0           1       2            3          4
// no drop | punti | upgrade 1 | upgrade 2 | upgrade 3 
const DROP_RATE = array(5, 70, 15, 5, 5); 
/**
 * Estrae un indice randomico usando DROP_RATE come pesi
 * 
 * Genera un valore soglia tra 1 e 100 compresi da ragiungere sommando sequenzialmente i pesi,
 * estrae l'indice del peso che consente di raggiunge/superare la soglia.
 * @see DROP_RATE 
 * @return int indice che identifica il tipo di drop
 */
function rng_drop() : int{
    $ratio = DROP_RATE[0];
    $treshold = rand(1,100);
    $i = 0;
    while ($ratio < $treshold) {
        ++$i;
        $ratio += DROP_RATE[$i];
    } 
    return $i;
}

enum Status{
    case Running;
    case Won;
    case Lost;
    case Pause;
}

class Game {
    protected string $id;
    protected int $score;
    protected Status $status; 
    protected Box $playing_field;
    protected Box $spawning_field;
    protected Box $exfil_area; 
    protected Spaceship $ship;
    protected $bullets = array();
    protected $asteroids = array(); 
    protected $items = array();
    protected $friction;

    public function __construct(String $id, Box $pf=PLAYING_FIELD, Box $sf=SPAWNING_FIELD, Box $ea=EXFIL_AREA){
        $this->id = $id;
        $this->score = 0;
        $this->status = Status::Running;
        $this->playing_field = $pf;
        $this->spawning_field = $sf;
        $this->exfil_area = $ea;
        $this->ship = DEFAULT_SHIP->deep_copy();
        $this->ship->set_game($this);
        $this->friction = DEFAULT_FRICTION;
    }

    public function get_id():string{
        return $this->id;
    }
    public function set_id(string $id){
        $this->id = $id;
    }
    public function get_score():string{
        return $this->score;
    }
    public function set_score(int $s){
        $this->score = $s;
    }
    public function add_score(int $s){
        $this->score += $s;
    }
    public function get_status():Status{
        return $this->status;
    }
    public function set_status(Status $p){
        $this->status = $p;
    }
    public function get_playing_field() : Box {
        return $this->playing_field;
    }
    public function set_playing_field(Box $pf){
        $this->playing_field = $pf;
    }
    public function get_spawning_field():Box{
        return $this->spawning_field;
    }
    public function set_spawning_field(Box $sf){
        $this->spawning_field = $sf;
    }
    public function get_exfil_area():Box{
        return $this->exfil_area;
    }
    public function set_exfil_area(Box $ea){
        $this->exfil_area = $ea;
    }
    public function get_ship():Spaceship{
        return $this->ship;
    }
    public function  set_ship(Spaceship $s){
        $this->ship = $s;
    }

    public function get_friction(){
        return $this->friction;
    }
    public function set_friction(int $f){
        $this->friction = $f;
    }

    public function get_asteroids():array{
        return $this->asteroids;
    }
    public function get_items():array{
        return $this->items;
    }
    public function get_bullets():array{
        return $this->bullets;
    }
    public function add_bullet(Bullet $b){
        $this->bullets[] = $b;
    }
    public function add_item(Item $i){
        $this->items[] = $i;
    }
    public function add_asteroid(Asteroid $a){
        $this->asteroids[] = $a;
    }
/**
 * Rimuove un oggetto Entity dal corretto array.
 * 
 * Rimuove dal corretto array l'oggetto Entity controllandone il tipo e cercandolo per il valore chiave.
 *  
 * @param Entity $e entità da rimuovere
 * @var key valore chiave dell'elemento nell'array
 */
    public function remove(Entity $e){
        if(is_a($e,'Bullet')){
            if(($key = array_search($e, $this->bullets)) !== false )
                unset($this->bullets[$key]);
        } else if(is_a($e,"Asteroid")){
            if (($key = array_search($e, $this->asteroids)) !== false) {
            unset($this->asteroids[$key]);
        }
        }else if(is_a($e, 'Item')){
            if (($key = array_search($e, $this->items)) !== false) {
            unset($this->items[$key]);
        }
        }
    }
    
/**
 * Controllo collisioni di tutte le entità del gioco, chiama i rispettivi metodi @see on_collision()
 * 
 * Controllo tra navicella e EXFIL_AREA se avviene chiama @see game_win()
 * Controllo iterativo tra navicella e oggetti Item.
 * Controllo iterativo della collisione tra oggetti Bullet e Asteroid.
 * Controllo iterativo della collisione tra oggetti Asteorid e la navicella.
 * 
 */
    public function check_collisions(){
        if($this->ship->get_hitbox()->check_overlap($this->exfil_area))
            $this->game_win();
        else{
            foreach($this->items as $i){
                if($this->ship->check_collision($i)){
                    $this->ship->on_collision($i);
                    $i->on_collision($this->ship);
                }
            }
            foreach($this->bullets as $b){
                foreach($this->asteroids as $a){
                    if($b->check_collision($a)){
                        $bc = clone $b;
                        $b->on_collision($a);
                        $a->on_collision($bc);
                        if($b->get_rank() < 1) break;
                    }
                }
            }
            foreach($this->asteroids as $a){
                if($this->ship->check_collision($a))
                    $this->ship->on_collision($a);
            } 
        }
    }

/**
 * Aggiorna la posizione di tutte le entità del gioco e crea asteroidi.
 * 
 * Aggiorna la navicella poi iterativamente gli oggetti Bullet, Item, Asteroid.
 * Rimuove dai rispettivi array gli oggetti fuori dal campo di gioco e zona di spawn.
 * Chiama @see check_collisions()
 * Genera @var rn un numero randomico di asteroidi più alto tanto è più alto il punteggio.
 * @var min estremo inferiore range randomico 
 * @var max estremo range inferiore randomico
 * Impedisce di generare troppi asteroidi contemporaneamente. 
 */
    public function update(){
        if($this->ship->get_hitbox()->check_overlap(EXFIL_AREA)){ //navicella esfiltra
            $this->game_win();
            return;
        }
        $this->ship->update();
        foreach($this->bullets as $b){
            $b->update();
            if(!$b->get_hitbox()->check_overlap(PLAYING_FIELD)){ //proiettile fuori dal campo di gioco
                $this->remove($b);
            }
        }
        foreach($this->items as $i){
            $i->update();
        }
        foreach($this->asteroids as $a){
            $a->update();
            if(!$a->get_hitbox()->check_overlap(SPAWNING_FIELD)){ //asteroide fuori dal campo o da spawn
                $this->remove($a);
            }
        }
        $this->check_collisions();
        
        //spawn asteroidi in base alla fase di gioco
        if($this->score <= 1000){ //mid game
            $min = 2;
            $max = 5;
        }
        else if($this->score > 1000){ // end game
            $min = 4;
            $max = 8;
        }
        $rn = rand($min,$max);
        if(sizeof($this->asteroids) + $rn > 20);
            $rn = 20 - sizeof($this->asteroids);
        for($i = 0; $i < $rn; ++$i){
            $this->spawn_asteroid();
        }
    }
    /**
     * Crea e aggiunge al relativo array un asteroide.
     * 
     * Genera il rank @var rank randomicamente più alto tanto è più alto il punteggio.
     * Genera @var norm la norma randomica dell'asteroide
     * Utilizza @see rng_asteroid_spawn() per ottenere posizione e angolo della direzione.
     * Crea e @see add_asteroid() aggiunge all'array relativo l'asteroide creato.
     */
    public function spawn_asteroid(){
        $min=0;
        $max=0;
        if($this->score == 0){ //init
            $min = 1;
            $min = 2;
        }
        else if($this->score <= 1000){ //mid game
            $min = 1;
            $max = 3;
        }
        else if($this->score > 1000){ // end game
            $min = $this->score/1000 + 1;
            $max = $min + 3;
        }

        $rank = rand($min,$max);
        $norm = rand(1,5);
        $pos = $this->rng_asteroid_spawn();

        $a = new Asteroid(new Vector($pos[2],$norm),new Box($pos[0],$pos[1],ASTEROID_SIZE*$rank,ASTEROID_SIZE*$rank),$rank,$this);
        $this->add_asteroid($a);
    }
    /**
     * Genera @var rand_p punto randomico sul perimetro di SPAWNING_FIELD.
     * Trasforma rand_p in coordinate cartesiane.
     * Genera @var alfa angolo in gradi randomico verso il centro approssimativo del PLAYING_FIELD.
     * 
     * @return array (x,y,alfa) dell'asteroide
     */
    public function rng_asteroid_spawn():array{ 
        //origine fuori dalla schermata di gioco
        $w = SPAWNING_FIELD->get_width();
        $h = SPAWNING_FIELD->get_height();
        $perimeter = ($w + $h)*2;
        $rand_p = rand(0, $perimeter);
        if($rand_p < $w + $h){
            if($rand_p < $w){
                $x = $rand_p;
                $y = rand(0,ASTEROID_SIZE);
            } else{
                $x = rand($w-ASTEROID_SIZE, $w);
                $y = $rand_p - $w;
            }
        } else{
            $rand_p -= ($w + $h);
            if($rand_p < $w){
                $x = $w - $rand_p;
                $y = rand($h-ASTEROID_SIZE, $h);
            } else {
                $x = rand(0, ASTEROID_SIZE);
                $y = $h - ($rand_p - $w);
            }
        }
        //angolo verso il centro approssimativo
        $wp = PLAYING_FIELD->get_width();
        $hp =PLAYING_FIELD->get_height();
        $xp = rand($wp/2 - 2*ASTEROID_SIZE, $wp/2 + 3*ASTEROID_SIZE);
        $yp = rand($hp/2 - 2*ASTEROID_SIZE, $hp/2 + 3*ASTEROID_SIZE);

        $m = ($y - $yp) / ($x - $xp);
        $alfa = rad2deg(atan($m));

        return array($x,$y,$alfa);
    }

    /**
     * Popola il gioco di asteroidi.
     * 
     * Genera un numero casuale sufficiente di asteroidi per popolare il gioco.
     * Da usare solo all'inizio della partita.
     */
    public function init(){
        for($i=0; $i<rand(3,7); ++$i)
            $this->spawn_asteroid();  
    }

    public function game_over(){
        $this->status = Status::Lost;
        $this->score /= 3;
    }
    public function game_win(){
        $this->status = Status::Won;                    
    }
    public function toggle_pause(){
        if($this->status != Status::Lost ||$this->status != Status::Won)
            $this->status = $this->status == Status::Running ? Status::Pause : Status::Running;
    }

}

?>
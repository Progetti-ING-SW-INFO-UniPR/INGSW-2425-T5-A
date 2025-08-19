<?php 
require_once("Entity.php");

class Asteroid extends Entity{
    protected int $rank;

    public function __construct(Vector $dir, Box $box, int $rank, Game $g=null){
        parent::__construct($dir,$box,$g);
        $this->rank = $rank;
    }
    function deep_copy():Asteroid{
	    return unserialize(serialize($this));
    }
    public function get_rank(): int{
        return $this->rank;
    }
    public function set_rank(int $r){
        $this->rank = $r;
    }
    public function __toString():string{
        $e = parent::__toString($this);
        return $e . "Rank:" . $this->rank;
    }

    /**
     * Crea un oggetto di tipo Item.
     * 
     * @see rng_drop() determina il tipo di Item
     * assegna il punteggio in base al rank dell'asteroide, gli Item con upgrade hanno meno punti
     * usa la stessa posizione dell'asteroide.
     * @see add_item() aggiunge al relativo array l'Item creato.
     */
    public function drop(){
        $type = rng_drop();
        if ($type == 0) return;

        $points = DEFAULT_PNT*pow(2,$this->rank);
        if ($type > 1)
            $points = DEFAULT_PNT*$this->rank; 

        $i = new Item($this->velocity,$this->hitbox,$type,$points);
        $i->hitbox->set_width(ITEM_SIZE);
        $i->hitbox->set_heigth(ITEM_SIZE);
        $this->game->add_item($i);
    }
    /**
     * Divide un asteroide in due più piccoli
     * 
     * Crea due nuovi asteroidi con hitbox dimezzata nello stesso punto e con stessa norma di $this,
     * @var alfa cambia l'angolo del vettore dei nuovi asteroidi.
     * @see remove() rimuove l'asteroide dall'array.
     * @see add_asteroid() aggiunge gli asteroidi figli all'array.
     */
    public function split(){
        $alfa = $this->velocity->get_alfa_deg();
        $norm = $this->velocity->get_norm();

        $nb = new Box($this->hitbox->get_x(),$this->hitbox->get_y(),$this->hitbox->get_width()/2,$this->hitbox->get_height()/2);
        $first = new Asteroid(new Vector($alfa+30,$norm),clone $nb,$this->rank,$this->game);
        $second = new Asteroid(new Vector($alfa-30,$norm),clone $nb,$this->rank,$this->game);

        $this->game->remove($this);
        $this->game->add_asteroid($first);
        $this->game->add_asteroid($second);
    }
    /**
     * Gestisce la reazione alla collisione.
     * 
     * @param e entità a cui reagire in base al tipo. 
     * 
     * In caso di collisione con un oggetto di tipo Bullet, rimuove l'asteroide se ha rank minore di $e,
     * chiama @see drop() dopo la distruzione.
     * In caso l'asteroide non venga distrutto viene sottratto dal rank quello di $e poi chiama @see split().
     * 
     */
    public function on_collision($e)
    {
        if(is_a($e, 'Bullet')){
            if($e->get_rank() >= $this->rank){
                $this->rank = 0;
                $this->game->remove($this);    
                $this->drop();
            }
            else {
                $this->rank -= $e->get_rank();
                $this->split();
            }
        }
    }

}

?>
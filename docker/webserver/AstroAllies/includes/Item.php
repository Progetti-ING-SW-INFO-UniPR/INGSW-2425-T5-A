<?php 
require_once("includes/Entity");

class Item extends Entity{
    protected int $type;
    protected int $pnt_val;

    public function __construct(Vector $dir, Box $box, int $type, int $val, Game $g = null){
        parent::__construct($dir,$box,$g);
        $this->type = $type;
        $this->pnt_val = $val;
    }
    function deep_copy():Item{
	    return unserialize(serialize($this));
    }
    public function get_type(): int{
        return $this->type;
    }
    public function set_type(int $t){
        $this->type = $t;
    }
    public function get_val(): int{
        return $this->pnt_val;
    }
    public function set_val(int $v){
        $this->pnt_val = $v;
    }
    public function __toString():string{
        $e = parent::__toString($this);
        return $e . "Type:" . $this->type . "Points:" . $this->pnt_val;
    }
    /**
     * Gestisce la reazione alla collisione 
     * 
     * @param e entità a cui reagire in base al tipo
     * 
     * In caso di collisione con un oggetto di tipo Spaceship chiama @see add_score() per aggiungere il punteggio,
     * rimuove l'Item stesso dall'array.
     */
    public function on_collision(Entity $e)
    {   
        if(is_a($e, 'Spaceship')){
            $this->game->add_score($this->pnt_val);
            $this->game->remove($this);
        }
    }
    
}

?>
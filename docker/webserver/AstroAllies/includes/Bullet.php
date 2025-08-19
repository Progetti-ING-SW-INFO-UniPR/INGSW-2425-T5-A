<?php 
require_once("Entity.php");

class Bullet extends Entity{
    protected int $rank;

    public function __construct(Vector $dir, Box $box, int $rank,Game $g=null){
        parent::__construct($dir,$box,$g);
        $this->rank = $rank;
    }
    function deep_copy():Bullet{
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
     * Gestisce la reazione alla collisione. 
     * 
     * @param e entità a cui reagire in base al tipo
     * 
     * In caso di collisione con un oggetto di tipo Asteroid rimuove il proiettile se ha rank minore di $e,
     * altrimenti ne effettua la sottrazione.
     */
    public function on_collision(Entity $e){
        if(is_a($e, 'Asteroid')){
            if($e->get_rank() >= $this->rank){
                $this->rank = 0;
                $this->game->remove($this);  
            }
            else $this->rank -= $e->get_rank();
        }
    }

}

?>
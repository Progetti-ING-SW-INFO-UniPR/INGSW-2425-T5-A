<?php 
require_once("includes/Entity");

class Bullet extends Entity{
    protected int $rank;
    protected int $perforation = 0;

    public function __construct(Vector $dir, Box $box, int $rank){
        parent::__construct($dir,$box);
        $this->rank = $rank;
    }

    public function get_rank(): int{
        return $this->rank;
    }
    public function get_perf(): int{
        return $this->perforation;
    }
    public function set_rank(int $r){
        $this->rank = $r;
    }
    public function set_perf(int $p){
        $this->perforation = $p;
    }



}

?>
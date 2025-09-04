<?php
class Vector
{
    protected float $alfa; //radianti
    protected float $norm;

    public function __construct(float $alfa, float $norm){
        $this->alfa = deg2rad($alfa); //input in gradi convertito in radianti 
        $this->norm = $norm;
    }
    function deep_copy():Vector{
	    return unserialize(serialize($this));
    }
    public function get_alfa(): float{
        return $this->alfa; 
    }
    public function get_alfa_deg(): float{
        return rad2deg($this->alfa);
    }
    public function get_norm():float{
        return $this->norm;
    }

    public function set_alfa(float $a){
        $this->alfa = deg2rad($a);//input in gradi convertito in radianti
    }
    public function set_norm(float $n){
        $this->norm = $n;
    }

    public function __toString(): string{
        $s = "Alfa:" . $this->get_alfa_deg() . " Norma:" . $this->norm;
        return $s;
    }
    public function get_dx(): float{
        return $this->norm * cos($this->alfa);
    }

    public function get_dy(): float{
        return $this->norm * sin($this->alfa);
    }

    public function sum_vector(Vector $v): void{
        $somma_x = $this->get_dx() + $v->get_dx();
        $somma_y = $this->get_dy() + $v->get_dy();
        
        $this->norm = sqrt( $somma_x*$somma_x + $somma_y*$somma_y);
        $this->alfa = atan( $somma_y/$somma_x);    
    }
    public function sum_norm($n) {
        $this->norm += $n;
    }
    public function sum_alfa($a) {
        $this->alfa += $a;
    }
    /*
    public function sum_float($a, $n){
        //stessa di sum_vector ma con le componenti di v
    }
    */

}
?>

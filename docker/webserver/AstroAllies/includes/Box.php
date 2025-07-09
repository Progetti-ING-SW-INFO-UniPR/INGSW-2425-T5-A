<?php
class Box
{
    protected float $x;
    protected float $y; // (x,y) sono lo spigolo in alto a sinistra della box
    protected float $width;
    protected float $height;

    public function __construct(float $x,float $y,float $width,float $height){
        $this->x = $x;
        $this->y = $y;
        $this->width = abs($width);
        $this->height = abs($height);
    }
    public function get_x(): float{
        return $this->x;
    }
    public function get_y(): float{
        return $this->y;
    }
    public function get_width():float{
        return $this->width;
    }
    public function get_height():float{
        return $this->height;
    }
    public function set_x(float $x){
        $this->x = $x;
    }
    public function set_y(float $y){
        $this->y = $y;
    }
    public function set_width(float $width){
        $this->width = abs($width);
    }
    public function set_heigth(float $height){
        $this->height = abs($height);
    }
    public function __toString(): string{
        $s = "X:" . $this->x ." Y:".$this->y." W:".$this->width." H:".$this->height;    
        return $s;
    }
    public function check_overlap(Box $box): bool{
        $x2 = $box->get_x();
        $w2 = $box->get_width();
        $y2 = $box->get_y();
        $h2 = $box->get_height();
        if( ($this->x + $this->width >= $x2 and $x2 + $w2 >= $this->x) and
            ($this->y + $this->height >= $y2 and $y2 + $h2 >= $this->y)){
            return true;
        }
        return false;
    }

    public function move(float $dx, float $dy){
        $this->x += $dx;
        $this->y += $dy;
    }

}
?>
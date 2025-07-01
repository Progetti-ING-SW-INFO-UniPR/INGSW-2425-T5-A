<?php
require_once("includes/Vector.php");
require_once("includes/Box.php");
abstract class Entity
{
    protected Vector $direction;
    protected Box $hitbox;

    public function __construct(Vector $direction, Box $hitbox){
        $this->direction = $direction;
        $this->hitbox = $hitbox;
    }
    public function update(): void{
        $this->hitbox->move($this->direction->get_dx(), $this->direction->get_dy());
    }

    /*
    public function move(float $new_dir): void{
        $this->direction->sum_norm($new_dir);
    }
    */
    
    public function check_collision(Entity $entity): bool{
        return $this->hitbox->check_overlap($entity->hitbox);
    }
    abstract public function on_collision();

}
?>

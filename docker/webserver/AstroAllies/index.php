<?php
require_once('includes/Box.php');


$b1 = new Box(0,0,3,5);
$b2 = new Box(1,0,1,10);

print($b2->check_overlap($b1));

?>
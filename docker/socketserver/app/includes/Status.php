<?php
enum Status : string{
    case Running = "running";
    case Won = "won";
    case Lost = "lost";
    case Pause = "pause";
}
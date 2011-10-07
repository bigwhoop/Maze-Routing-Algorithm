<?php
namespace BigWhoop\MazeRunningExample;
use BigWhoop\MazeRunning\Maze,
    BigWhoop\MazeRunning\Point;

require __DIR__ . '/BigWhoop/MazeRunning/Maze.php';
require __DIR__ . '/BigWhoop/MazeRunning/Point.php';

$maze = Maze::createFromImage(__DIR__ . '/maze3.png');

$routeKeys = array();
foreach ($maze->findRoute() as $point) {
    $routeKeys[] = (string)$point;
}

$o = '<div style="line-height: 20px; font-size: 10px;">';
foreach ($maze->getGrid() as $y => $xs) {
    foreach ($xs as $x => $point) {

        $o .= '<div style="float: left; width: 20px; height: 20px; text-align: center;';

        if (in_array((string)$point, $routeKeys)) {
            $o .= ' background-color: #bbb;';
        } else {
            switch ($point->getType())
            {
                case Point::TYPE_START:
                    $o .= ' background-color: blue;';
                    break;

                case Point::TYPE_FINISH:
                    $o .= ' background-color: yellow;';
                    break;

                case Point::TYPE_WALL:
                    $o .= ' background-color: black;';
                    break;
            }
        }

        $o .= '">';

        if ($point->isScored()) {
            $o .= $point->getScore();
        }

        $o .= '</div>';
    }

    $o .= '<br>';
}

$o .= '</div>';

echo $o;
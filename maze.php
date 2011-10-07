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

$step = 1;
if (isset($_GET['step'])) {
    if ($_GET['step'] <= 4 && $_GET['step'] >= 1) {
        $step = $_GET['step'];
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Maze-Running</title>

        <style>
            * {
                padding : 0;
                margin  : 0;
            }

            .maze {
                padding : 20px;
            }

            .row {
                line-height : 20px;
                font-size   : 10px;
                clear       : left;
            }

            .point {
                float       : left;
                text-align  : center;
                <?php if ($step == 1): ?>
                width       : 20px;
                height      : 20px;
                <?php else: ?>
                width       : 19px;
                height      : 19px;
                border-top  : 1px solid #ccc;
                border-left : 1px solid #ccc;
                <?php endif; ?>
            }

            <?php if ($step > 1): ?>
            .row .point:last-child {
                border-right : 1px solid #ccc;
            }

            .maze .row:last-child .point {
                border-bottom : 1px solid #ccc;
            }
            <?php endif; ?>

            <?php if ($step > 3): ?>
            .route {
                background-color : #bbb;
            }
            <?php endif; ?>

            .start {
                background-color : blue;
            }

            .finish {
                background-color : yellow;
            }

            .wall {
                background-color : black;
            }

            <?php if ($step <= 2): ?>
            .score {
                display: none;
            }
            <?php endif; ?>
        </style>
    </head>

    <body>
        <div id="controls">
            Step:
            <a href="?step=1">1</a>
            <a href="?step=2">2</a>
            <a href="?step=3">3</a>
            <a href="?step=4">4</a>
        </div>

        <div class="maze">
            <?php foreach ($maze->getGrid() as $xs): ?>
                <div class="row">
                    <?php foreach ($xs as $point): ?>
                        <div class="point <?php
                            if (in_array((string)$point, $routeKeys)) {
                                echo 'route';
                            } else {
                                echo $point->getType();
                            }
                        ?>">

                        <?php if ($point->isScored()): ?>
                            <div class="score"><?php echo $point->getScore(); ?></div>
                        <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </body>
</html>
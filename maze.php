<?php
class Path extends Point
{
    /**
     * @var Path
     */
    private $previous = null;


    /**
     * @param Path $prev
     * @return Path
     */
    public function setPrevious(Path $prev)
    {
        $this->previous = $prev;
        return $this;
    }


    /**
     * @return null|Path
     */
    public function getPrevious()
    {
        return $this->previous;
    }
}


class Point
{
    /**
     * @var int
     */
    private $x = 0;

    /**
     * @var int
     */
    private $y = 0;


    /**
     * @param int $x
     * @param int $y
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }


    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }


    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->getX()}-{$this->getY()}";
    }
}

class Maze
{
    const POINT_START = 'S';
    const POINT_END   = 'E';
    const POINT_PATH  = 'P';
    const POINT_WALL  = 'W';

    /**
     * @var array
     */
    private $grid = array();

    /**
     * @var int
     */
    private $width = 0;

    /**
     * @var int
     */
    private $height = 0;


    /**
     * @static
     * @param string $path
     * @return Maze
     */
    static public function createFromImage($path)
    {
        $img    = imagecreatefrompng($path);
        $width  = imagesx($img);
        $height = imagesy($img);

        $grid = array();

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($img, $x, $y);
                switch ($color)
                {
                    case 255      : $type = Maze::POINT_START; break; // Blue
                    case 16776960 : $type = Maze::POINT_END;   break; // Yellow
                    case 0        : $type = Maze::POINT_WALL;  break; // Black
                    case 16777215 :
                    default       : $type = Maze::POINT_PATH;  break; // White
                }

                if (!isset($grid[$y])) {
                    $grid[$y] = array();
                }

                $grid[$y][$x] = $type;
            }
        }

        return new Maze($grid);
    }


    /**
     * @param array $grid
     */
    private function __construct(array $grid)
    {
        $this->grid   = $grid;
        $this->width  = count($grid[0]);
        $this->height = count($grid);
    }


    /**
     * @return array
     */
    public function getGrid()
    {
        return $this->grid;
    }


    public function findRoute()
    {
        $scores = $this->getScores();
        $lastScore = array_pop($scores);
        $lastPoint = $lastScore[0];
        
        $route = array();
        while ($lastPoint) {
            $route[] = new Point($lastPoint->getX(), $lastPoint->getY());
            $lastPoint = $lastPoint->getPrevious();
        }

        // Remove start point
        unset($route[count($route) - 1]);

        $route = array_reverse($route);
        
        return $route;
    }


    /**
     * @return array
     */
    public function getScores()
    {
        $queue = $this->findNextPaths($this->getStartPoint());
        return $this->scorePath($queue, array(), 1, array());
    }


    /**
     * @param array $queue
     * @param array $scores
     * @param int $score
     * @param array $scored
     * @return array
     */
    private function scorePath(array $queue, array $scores, $score, array $scored)
    {
        if (empty($queue)) {
            return $scores;
        }

        $nextQueue = array();

        foreach ($queue as $p) {
            if (!isset($scores[$score])) {
                $scores[$score] = array();
            }

            $scores[$score][] = $p;
            $scored[] = (string)$p;

            foreach ($this->findNextPaths($p) as $nextP) {
                if (!in_array((string)$nextP, $scored)) {
                    $nextQueue[] = $nextP;
                }
            }
        }

        return $this->scorePath($nextQueue, $scores, $score + 1, $scored);
    }


    /**
     * @param Point $p
     * @return array
     */
    private function findNextPaths(Point $p)
    {
        $type = $this->getPointType($p);

        if ($type == self::POINT_WALL) {
            return array();
        }

        $paths = array();
        
        $offsets = array(
            new Point(-1, 0), // left of $p
            new Point(0, -1), // above of $p
            new Point(0, 1),  // below of $p
            new Point(1, 0),  // right of $p
        );

        foreach ($offsets as $offset) {
            try {
                $sibling = new Path($p->getX() + $offset->getX(), $p->getY() + $offset->getY());
                $type = $this->getPointType($sibling);

                if ($type == self::POINT_PATH) {
                    $sibling->setPrevious($p);
                    $paths[] = $sibling;
                }
            } catch (OutOfBoundsException $e) {}
        }
        
        return $paths;
    }


    /**
     * @param Point $p
     * @return string
     */
    private function getPointType(Point $p)
    {
        if (!isset($this->grid[$p->getY()])) {
            throw new OutOfBoundsException('No such Y: ' . $p->getY());
        }

        if (!isset($this->grid[$p->getY()][$p->getX()])) {
            throw new OutOfBoundsException('No such X/Y: ' . $p->getX() . '/' . $p->getY());
        }

        return $this->grid[$p->getY()][$p->getX()];
    }


    /**
     * @throws OutOfBoundsException
     * @return Path
     */
    private function getStartPoint()
    {
        foreach ($this->grid as $y => $xs) {
            foreach ($xs as $x => $type) {
                if ($type == self::POINT_START) {
                    return new Path($x, $y);
                }
            }
        }

        throw new OutOfBoundsException('No start point found');
    }


    /**
     * @throws OutOfBoundsException
     * @return Path
     */
    private function getEndPoint()
    {
        foreach ($this->grid as $y => $xs) {
            foreach ($xs as $x => $type) {
                if ($type == self::POINT_END) {
                    return new Path($x, $y);
                }
            }
        }
        
        throw new OutOfBoundsException('No end point found');
    }
}


$maze = Maze::createFromImage(__DIR__ . '/maze.png');

$grid = $maze->getGrid();

// Invert scores
$scores = array();
foreach ($maze->getScores() as $score => $ps) {
    foreach ($ps as $p) {
        $scores[(string)$p] = $score;
    }
}

$route = array();
foreach ($maze->findRoute() as $p) {
    $route[] = (string)$p;
}

$o = '<div style="line-height: 20px; font-size: 10px;">';
foreach ($grid as $y => $xs) {
    foreach ($xs as $x => $type) {
        $key = "$x-$y";

        $o .= '<div style="float: left; width: 20px; height: 20px; text-align: center;';

        if (in_array($key, $route)) {
            $o .= ' background-color: #bbb;';
        } else {
            switch ($type)
            {
                case Maze::POINT_START:
                    $o .= ' background-color: yellow;';
                    break;

                case Maze::POINT_END:
                    $o .= ' background-color: blue;';
                    break;

                case Maze::POINT_WALL:
                    $o .= ' background-color: black;';
                    break;
            }
        }

        $o .= '">';

        if (array_key_exists($key, $scores)) {
            $o .= $scores[$key];
        }

        $o .= '</div>';
    }

    $o .= '<br>';
}

$o .= '</div>';

echo $o;
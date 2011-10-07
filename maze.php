<?php
class Point
{
    const TYPE_START  = 'S';
    const TYPE_FINISH = 'F';
    const TYPE_PATH   = 'P';
    const TYPE_WALL   = 'W';
    

    /**
     * @var int
     */
    private $x = 0;

    /**
     * @var int
     */
    private $y = 0;

    /**
     * @var string
     */
    private $type = null;

    /**
     * @var int
     */
    private $score = null;


    /**
     * @param int $x
     * @param int $y
     * @param string $type
     */
    public function __construct($x, $y, $type)
    {
        $this->x = $x;
        $this->y = $y;
        $this->type = $type;
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
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return int|null
     */
    public function getScore()
    {
        return $this->score;
    }


    /**
     * @param int $score
     * @return Point
     */
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }


    /**
     * @return bool
     */
    public function isScored()
    {
        return $this->score !== null;
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
    /**
     * @var array
     */
    private $grid = array();

    /**
     * @var Point
     */
    private $startPoint = null;

    /**
     * @var Point
     */
    private $finishPoint = null;

    /**
     * @var bool
     */
    private $isScored = false;


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
                    case 255      : $type = Point::TYPE_START;  break; // Blue
                    case 16776960 : $type = Point::TYPE_FINISH; break; // Yellow
                    case 0        : $type = Point::TYPE_WALL;   break; // Black
                    case 16777215 :                                    // White
                    default       : $type = Point::TYPE_PATH;   break;
                }

                $point = new Point($x, $y, $type);

                if (!isset($grid[$y])) {
                    $grid[$y] = array();
                }

                $grid[$y][$x] = $point;
            }
        }

        return new Maze($grid);
    }


    /**
     * @param array $grid
     */
    private function __construct(array $grid)
    {
        foreach ($grid as $xs) {
            foreach ($xs as $point) {
                $type = $point->getType();

                if ($type == Point::TYPE_START) {
                    $this->startPoint = $point;
                } elseif ($type == Point::TYPE_FINISH) {
                    $this->finishPoint = $point;
                }
            }
        }

        if (!$this->startPoint) {
            throw new OutOfBoundsException('No start point found.');
        }

        if (!$this->finishPoint) {
            throw new OutOfBoundsException('No finish point found.');
        }

        $this->grid = $grid;
    }


    /**
     * @return array
     */
    public function getGrid()
    {
        return $this->grid;
    }


    /**
     * @return Maze
     */
    public function scoreGrid()
    {
        if (!$this->isScored) {
            $this->startPoint->setScore(0);
            $paths = $this->findConnectingPaths($this->startPoint);
            $this->scorePathsRecursively($paths, 1);

            $this->isScored = true;
        }

        return $this;
    }


    /**
     * @param array $paths
     * @param int $score
     */
    private function scorePathsRecursively(array $paths, $score)
    {
        if (empty($paths)) {
            return;
        }

        $upcomingPaths = array();

        foreach ($paths as $path) {
            $path->setScore($score);

            if ($path->getType() == Point::TYPE_FINISH) {
                return;
            }

            foreach ($this->findConnectingPaths($path) as $connectingPath) {
                if (!$connectingPath->isScored()) {
                    $upcomingPaths[] = $connectingPath;
                }
            }
        }

        $this->scorePathsRecursively($upcomingPaths, $score + 1);
    }


    /**
     * @return array
     */
    public function findRoute()
    {
        $route = array();

        // We didn't reach the finish point :/
        if (!$this->finishPoint->isScored()) {
            return $route;
        }

        return $this->findRouteRecursively($this->finishPoint);
    }


    /**
     * @param Point $point
     * @param array $route
     * @return array
     */
    private function findRouteRecursively(Point $point, array $route = array())
    {
        $nextPoint = null;
        
        foreach ($this->findConnectingPaths($point) as $nextPointCandidate) {
            // If possbile next point is the start, we found what we're looking for
            if ($nextPointCandidate->getType() == Point::TYPE_START) {
                return array_values($route);
            }

            // Possible next point must not already exist in the route
            if (array_key_exists((string)$nextPointCandidate, $route)) {
                continue;
            }

            // Possible next point must be scored
            if (!$nextPointCandidate->isScored()) {
                continue;
            }

            // Possible next point's score must be below the current point's score
            if (!$nextPointCandidate->getScore() >= $point->getScore()) {
                continue;
            }

            // Possible next point's score must be below the current possible next point's score
            if (null === $nextPoint || $nextPointCandidate->getScore() < $nextPoint->getScore()) {
                $nextPoint = $nextPointCandidate;
            }
        }

        // Seems like we could not find the start.
        // This means we could nto find the route.
        if (!$nextPoint) {
            return array();
        }

        // Add next point to the route
        $route[(string)$nextPoint] = $nextPoint;
        
        return $this->findRouteRecursively($nextPoint, $route);
    }


    /**
     * @param Point $point
     * @return array
     */
    private function findConnectingPaths(Point $point)
    {
        $possiblePointTypes = array(Point::TYPE_START, Point::TYPE_PATH, Point::TYPE_FINISH);

        if (!in_array($point->getType(), $possiblePointTypes)) {
            return array();
        }
        
        $offsets = array(
            array('x' => -1, 'y' =>  0), // left of $p
            array('x' =>  0, 'y' => -1), // above of $p
            array('x' =>  0, 'y' =>  1), // below of $p
            array('x' =>  1, 'y' =>  0), // right of $p
        );

        $connectingPaths = array();

        foreach ($offsets as $offset) {
            // Build x/y of connecting point
            $x = $point->getX() + $offset['x'];
            $y = $point->getY() + $offset['y'];

            if (!isset($this->grid[$y])) {
                continue;
            }

            if (!isset($this->grid[$y][$x])) {
                continue;
            }

            $connectingPoint = $this->grid[$y][$x];

            if (!in_array($connectingPoint->getType(), $possiblePointTypes)) {
                continue;
            }

            $connectingPaths[] = $connectingPoint;
        }

        return $connectingPaths;
    }
}


$maze = Maze::createFromImage(__DIR__ . '/maze3.png');
$maze->scoreGrid();

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
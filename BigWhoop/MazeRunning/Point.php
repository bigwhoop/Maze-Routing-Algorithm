<?php
namespace BigWhoop\MazeRunning;

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
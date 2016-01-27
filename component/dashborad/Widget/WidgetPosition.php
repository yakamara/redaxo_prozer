<?php

namespace FragSeb\Dashboard\Widget;

use FragSeb\Dashboard\Model\WidgetPositionInterface;
use FragSeb\Dashboard\Model\WidgetExposeInterface;
use FragSeb\Dashboard\ExposeTrait;

final class WidgetPosition implements WidgetPositionInterface, WidgetExposeInterface
{
    use ExposeTrait;
    /**
     * @var int|null
     */
    private $col = null;

    /**
     * @var int|null
     */

    private $row = null;

    /**
     * @var int
     */

    private $sizey = 1;

    /**
     * @var int
     */

    private $sizex = 1;

    /**
     * @return int|null
     */
    public function getCol()
    {
        return $this->col;
    }

    /**
     * @param int|null $col
     *
     * @return $this
     */
    public function setCol($col)
    {
        $this->col = $col;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @param int|null $row
     *
     * @return $this
     */
    public function setRow($row)
    {
        $this->row = $row;

        return $this;
    }

    /**
     * @return int
     */
    public function getSizey()
    {
        return $this->sizey;
    }

    /**
     * @param int $sizey
     *
     * @return $this
     */
    public function setSizey($sizey)
    {
        $this->sizey = $sizey;

        return $this;
    }

    /**
     * @return int
     */
    public function getSizex()
    {
        return $this->sizex;
    }

    /**
     * @param int $sizex
     *
     * @return $this
     */
    public function setSizex($sizex)
    {
        $this->sizex = $sizex;

        return $this;
    }
}

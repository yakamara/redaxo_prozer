<?php

class ApproachEntity
{
    private $event;

    private $from;

    private $to;

    private $block = 0;

    private $column = 1;

    private $columns = 0;

    public function __construct($event, $from, $to)
    {
        $this->event = $event;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * width = parseInt(max / columns);
     * left = parseInt(column * width) - width;.
     *
     * @return array
     */
    public function getStyle()
    {
        $width = (int) (ApproachItems::MAX_WIDTH / $this->columns);
        $left = (int) ($this->column * $width - $width);

        if (!$this->isColumn(1)) {
            $left = $left + 1;
        }

        return [
            'height' => $this->to.'px',
            'top' => $this->from.'px',
            'width' => $width - 1 .'px',
            'left' => $left.'px',
        ];
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    public function cal()
    {
        return $this->getFrom() + $this->getTo() - 1;
    }

    /**
     * @return mixed
     */
    public function getBlock()
    {
        return $this->block;
    }

    public function isBlock($block)
    {
        return ($this->getBlock() == $block);
    }

    /**
     * @param $pos
     */
    public function setBlock($pos)
    {
        $this->block = $pos;
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    public function isColumn($column)
    {
        return ($this->getColumn() == $column);
    }

    /**
     * @param mixed $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }
}

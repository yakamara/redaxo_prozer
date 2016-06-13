<?php


class ApproachItems
{
    const MAX_WIDTH = 236;

    /**
     * @var array|ApproachEntity[]
     */
    private $collection;

    private $current_block = 0;
    private $current_column = 1;
    private $columns = 0;

    /**
     * ApproachItems constructor.
     */
    public function __construct()
    {
        $this->collection = [];
    }

    public function add($event, $timeframe)
    {
        $from = $this->timeframe($timeframe->getFrom());
        $to = $this->timeframe($timeframe->getTo(), $from);

        $this->collection[] = new ApproachEntity($event, $from, $to);
    }

    /**
     *
     */
    public function execute()
    {
        usort($this->collection, function ($a, $b) {
                if ($a->getFrom() < $b->getFrom()) {
                    return -1;
                }
                if ($a->getFrom() > $b->getFrom()) {
                    return 1;
                }

                if ($a->getTo() > $b->getTo()) {
                    return -1;
                }
                if ($a->getTo()  < $b->getTo()) {
                    return 1;
                }

                return 0;
            }
        );

        foreach ($this->collection as $key => $item) {
            $this->doExecute($item);
        }
    }

    private function doExecute(ApproachEntity $item)
    {
        $start_position = $item->getFrom();

        if (!$this->createFirsEvent()) {
            $block_max_end_position = 0;
            $new_column = true;

            for ($i = 1; $i <= $this->columns; ++$i) {
                $column_max_end_position = $this->column_max_end_position($i, $item);
                $block_max_end_position = $this->setMaxEndPosition($column_max_end_position, $block_max_end_position);
                $new_column = $this->fitsUnderColumn($start_position, $block_max_end_position, $new_column, $i);
            }

            $this->generateItemsProperties($start_position, $block_max_end_position, $new_column);
        }

        $this->setItemsProperties($item);
    }

    private function setItemsProperties($item)
    {
        $item->setBlock($this->current_block);
        $item->setColumn($this->current_column);
        $item->setColumns($this->columns);

        $this->findAndSetColumns();
    }

    private function createFirsEvent()
    {
        if ($this->current_block == 0) {
            $this->current_block = 1;
            $this->current_column = 1;
            $this->columns = 1;

            return true;
        }

        return false;
    }

    /**
     *
     */
    private function findAndSetColumns()
    {
        foreach ($this->collection as $item) {
            if ($item->isBlock($this->current_block)) {
                $item->setColumns($this->columns);
            }
        }
    }

    /**
     * @param $block
     * @param $column
     *
     * @return ApproachEntity[]|array
     */
    private function find($block, $column)
    {
        $result = [];
        foreach ($this->collection as $item) {
            if ($item->isBlock($block) && $item->isColumn($column)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function get($id)
    {
        foreach ($this->collection as $entity) {
            if ($id == $entity->getEvent()->getId()) {
                return $entity;
            }
        }

        return false;
    }

    public function getEntity($id)
    {
        foreach ($this->collection as $entity) {
            if ($id == $entity->getEvent()->getId()) {
                return $entity;
            }
        }

        return [];
    }

    /**
     * @param $i
     *
     * @return int|mixed
     */
    private function column_max_end_position($i, $item)
    {
        $column_max_end_position = 0;
        $block_articles = $this->find($this->current_block, $i);

        foreach ($block_articles as $article) {
            $i_end = $article->cal();
            if ($column_max_end_position < $i_end) {
                $column_max_end_position = $i_end;
            }
        }

        return $column_max_end_position;
    }

    /**
     * @param $column_max_end_position
     * @param $block_max_end_position
     * @return mixed
     */
    private function setMaxEndPosition($column_max_end_position, $block_max_end_position)
    {
        if ($column_max_end_position > $block_max_end_position) {
            $block_max_end_position = $column_max_end_position;

            return $block_max_end_position;
        }

        return $block_max_end_position;
    }

    /**
     * @param $start_position
     * @param $block_max_end_position
     * @param $new_column
     * @param $i
     * @return bool
     */
    private function fitsUnderColumn($start_position, $block_max_end_position, $new_column, $i)
    {
        if ($start_position > $block_max_end_position && $new_column) {
            $this->current_column = $i;
            $new_column = false;

            return $new_column;
        }

        return $new_column;
    }

    /**
     * @param $start_position
     * @param $block_max_end_position
     * @param $new_column
     */
    private function generateItemsProperties($start_position, $block_max_end_position, $new_column)
    {
        if ($start_position > $block_max_end_position) {
            $this->current_block++;
            $this->current_column = 1;
            $this->columns = 1;
        } elseif ($new_column) {
            $this->columns++;
            $this->current_column = $this->columns;
        }
    }

    private function timeframe($datetime, $from = null)
    {
        $clone_datetime = clone $datetime;

        $hours = $clone_datetime->format('H');
        $sub = $clone_datetime->format('i');

        $pixel = $hours * 60;

        if(null === $from) {
            return $pixel + $sub;
        }

        return ($pixel + $sub)-$from;
    }
}

<?php

namespace FragSeb\Dashboard\Model;

interface WidgetPositionInterface
{
    /**
     * @return int
     */
    public function getCol();

    /**
     * @return int
     */
    public function getRow();

    /**
     * @return int
     */
    public function getSizey();


    /**
     * @return int
     */
    public function getSizex();

}
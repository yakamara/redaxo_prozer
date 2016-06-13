<?php

namespace FragSeb\Dashboard\Entity;

/**
 * Created by PhpStorm.
 * User: Jochen
 * Date: 08.11.15
 * Time: 16:24
 */
interface DashboardAdapterInterface
{
    /**
     * @return DashboardEntity
     */
    public function getEntity();
}
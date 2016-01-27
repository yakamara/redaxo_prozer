<?php

namespace FragSeb\Dashboard\Model;

/**
 * Interface DashboardModelInterface
 * @package FragSeb\Dashboard\Model
 */
interface DashboardModelInterface
{
    /**
     * @return mixed
     */
    public function create();

    /**
     * @return mixed
     */
    public function update();

    /**
     * @return mixed
     */
    public function remove();

    /**
     * @return FragSeb\Dashboard\Entity\DashboardEntity
     */
    public function getEntity();

}
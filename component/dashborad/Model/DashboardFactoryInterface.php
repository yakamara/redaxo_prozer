<?php

namespace FragSeb\Dashboard\Model;

use DashboardSettings;

/**
 * Interface DashboardFactoryInterface
 */
interface DashboardFactoryInterface
{
    /**
     * @param DashboardSettings $settings
     * @param DashboardModelInterface $model
     * @return mixed
     */
    public function create(DashboardSettings $settings, DashboardModelInterface $model);
}
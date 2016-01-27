<?php

namespace FragSeb\Dashboard\Model;

/**
 * Interface DashboardInterface
 */
interface DashboardInterface extends \Traversable, \Countable
{
    /**
     *
     */
    const NAME = self::class;

    /**
     * @param WidgetInterface $widget
     * @return mixed
     */
    public function add(WidgetInterface $widget);

    /**
     * @param WidgetInterface $widget
     * @return mixed
     */
    public function remove(WidgetInterface $widget);

    /**
     * @param WidgetInterface $widget
     * @return mixed
     */
    public function update(WidgetInterface $widget);

    /**
     * @return mixed
     */
    public function clean();

}
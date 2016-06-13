<?php

namespace FragSeb\Dashboard;

use FragSeb\Dashboard\Model\WidgetInterface;
use FragSeb\Dashboard\Model\DashboardInterface;
use FragSeb\Dashboard\Exception\WidgetException;

/**
 * Class Dashboard.
 */
final class Dashboard implements \IteratorAggregate, DashboardInterface
{
    /**
     * @var array
     */
    private $widgets = [];

    /**
     * @param WidgetInterface $widget
     *
     * @return $this
     *
     * @throws WidgetException
     */
    public function add(WidgetInterface $widget)
    {
        if (isset($this->widgets[$widget->getClassName()])) {
            throw new WidgetException('Double entry widget.');
        }

        $this->widgets[$widget->getClassName()] = $widget;

        return $this;
    }

    /**
     * @param WidgetInterface $widget
     *
     * @return $this
     *
     * @throws WidgetException
     */
    public function remove(WidgetInterface $widget)
    {
        if (empty($this->widgets[$widget->getClassName()])) {
            throw new WidgetException('Widget entry does not exist.');
        }

        unset($this->widgets[$widget->getClassName()]);

        return $this;
    }

    /**
     * @param WidgetInterface $widget
     *
     * @return $this
     *
     * @throws WidgetException
     */
    public function update(WidgetInterface $widget)
    {
        if (empty($this->widgets[$widget->getClassName()])) {
            throw new WidgetException('Widget entry does not exist.');
        }

        $this->widgets[$widget->getClassName()] = $widget;

        return $this;
    }

    /**
     * @return $this
     */
    public function clean()
    {
        $this->widgets = [];

        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->widgets);
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->widgets);
    }
}

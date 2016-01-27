<?php

namespace FragSeb\Dashboard;

use DashboardSettings;
use FragSeb\Dashboard\Model\DashboardFactoryInterface;
use FragSeb\Dashboard\Model\DashboardModelInterface;
use FragSeb\Dashboard\Model\WidgetInterface;

/**
 * Class DashboardFactory.
 */
final class DashboardFactory implements DashboardFactoryInterface
{
    /**
     * @var Dashboard
     */
    private $dashboard;

    /**
     * @var array|null
     */
    private $data;

    /**
     * @param DashboardSettings       $settings
     * @param DashboardModelInterface $model
     *
     * @return Dashboard
     */
    public function create(DashboardSettings $settings, DashboardModelInterface $model)
    {

        $this->dashboard = new Dashboard();

        if ($userDashboard = $model->getEntity()->getDashboard()) {
            $this->data = $userDashboard->getIterator();
        }

        foreach ($settings->config() as $class => $config) {
            if (!class_exists($class)) {
                continue;
            }

            $this->add($class, $config);
        }

        return $this->dashboard;
    }

    /**
     * @param $class
     * @param $config
     * @throws Exception\WidgetException
     */
    private function add($class, $config = null)
    {
        /** @var Widget $widget */
        $widget = new $class();

        if (isset($config)) {

            foreach ($config as $property => $value) {
                $methode = 'set'.ucfirst($property);

                if (method_exists($widget, $methode)) {
                    $widget->{$methode}($value);
                }

                if (isset($this->data[$class])) {
                    $this->map($widget, $this->data[$class]);
                }

                $widget->setData($widget->getWidgetData());
            }
        }

        $this->dashboard->add($widget);
    }

    /**
     * @param WidgetInterface $widget
     * @param WidgetInterface $data
     */
    private function map(WidgetInterface $widget, WidgetInterface $data)
    {
        $widget->setWidgetposition($data->getWidgetposition());
        $widget->setActive($data->isActive());
        if($data->getSettings()) {
            $widget->setSettings($data->getSettings());
        }

        if ($widget->getHash() === $data->getHash()) {

            $data->setId($widget->getId());
            $data->setName($widget->getName());
            $data->setSettingView($widget->getSettingView());
            $data->setView($widget->getView());

        }
    }
}

<?php

namespace FragSeb\Dashboard;

/**
 * Class DashboardMapper
 */
final class DashboardMapper
{
    /**
     * @param $dashboard
     * @return string
     */
    public static function toJson($dashboard)
    {
        $json = [];
        foreach ($dashboard->getIterator() as $widget) {
            $json[$widget->getClassName()] = $widget->expose();
        }

        return json_encode($json);
    }

    /**
     * @param $data
     * @return Dashboard|void
     * @throws JsonMapperException
     * @throws WidgetException
     */
    public static function toObject($data)
    {
        if (null === $data) {
            return;
        }

        $mapper = new \JsonMapper();
        $dashboard = new Dashboard();
        foreach ($data as $class => $widget) {

            if(!class_exists($class)){
                continue;
            }

            try{
                $dashboard->add($mapper->map($widget, new $class()));

            } catch(\JsonMapperException $e){
                continue;
            }
        }

        return $dashboard;
    }
}

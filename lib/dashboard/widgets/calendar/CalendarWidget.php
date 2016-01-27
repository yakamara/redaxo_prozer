<?php
use FragSeb\Dashboard\Model\WidgetInterface;
use FragSeb\Dashboard\Widget\Widget;

/**
 * Created by PhpStorm.
 * User: Jochen
 * Date: 07.11.15
 * Time: 09:23
 */
class CalendarWidget extends Widget implements WidgetInterface
{
    protected $id = 'CalendarWidget';

    protected $name = 'Termine';

    protected $view = [
        'controller' => 'CalendarWidgetCtrl',
        'url' => '/screen/dashboard/?view=widgets/calendar/view'
    ];

    protected $settingView = [
        'controller' => 'CalendarSettingsCtrl',
        'url' => '/screen/dashboard/?view=widgets/calendar/settings',
    ];

}
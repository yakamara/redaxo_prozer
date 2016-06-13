<?php


use FragSeb\Dashboard\Model\WidgetInterface;
use FragSeb\Dashboard\Widget\Widget;

class BirthdayWidget extends Widget implements WidgetInterface
{

    protected $id = BirthdayWidget::class;

    protected $name = 'Birthday';

    protected $view = [
        'controller' => 'BirthdayWidgetCtrl',
        'url' => '/screen/dashboard/?view=widgets/birthday/view',
    ];

    protected $settingView = [
        'controller' => 'BirthdaySettingsCtrl',
        'url' => '/screen/dashboard/?view=widgets/birthday/settings',
    ];

    protected $adapter = BirthdayWidgetAdapter::class;

}
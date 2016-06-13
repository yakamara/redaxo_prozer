<?php
use FragSeb\Dashboard\Widget\WidgetPosition;

/**
 * Created by PhpStorm.
 * User: Jochen
 * Date: 07.11.15
 * Time: 15:18
 */
class DashboardSettings
{

    public function config()
    {
        $config = [
            BirthdayWidget::class => [
                'widgetposition' => new WidgetPosition(),
                'settings' => [
                    'frame' => 30
                ],
            ],
            CalendarWidget::class => [
                'widgetposition' => new WidgetPosition(),
                'settings' => [
                    'day' => (new DateTime())->format(DateTime::ATOM)
                ],
            ]
        ];


        return $config;
    }
}
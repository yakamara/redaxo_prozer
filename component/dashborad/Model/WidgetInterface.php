<?php

namespace FragSeb\Dashboard\Model;

/**
 * Interface WidgetInterface
 * @package FragSeb\Dashboard\Model
 */
interface WidgetInterface
{
    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getView();

    /**
     * @return array
     */
    public function getSettingView();

    /**
     * @return FragSeb\Dashboard\Widget\WidgetPosition
     */
    public function getWidgetposition();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return array
     */
    public function getSettings();


    /**
     * @return array
     */
    public function getWidgetData();
}
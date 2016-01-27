<?php
namespace FragSeb\Dashboard\Widget;

use FragSeb\Dashboard\Model\WidgetInterface;
use FragSeb\Dashboard\ExposeTrait;
use FragSeb\Dashboard\Model\WidgetModelAdapterInterface;

/**
 * Class Widget
 *
 *
 */
class Widget implements WidgetInterface
{
    use ExposeTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var FragSeb\Dashboard\Widget\WidgetPosition
     */
    protected $widgetposition;

    /**
     * @var array $view
     */
    protected $view;

    /**
     * @var array $settingView
     */
    protected $settingView;

    /**
     * @var array $data
     */
    protected $data = [];


    /**
     * @var bool $active
     */
    protected $active = false;


    /**
     * @var string $adapter
     */
    protected $adapter;

    /**
     * @var array $settings
     */
    protected $settings = [];

    /**
     * @return string
     */
    public function getClassName()
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return $this|FragSeb\Dashboard\Widget\WidgetPosition
     */
    public function getWidgetposition()
    {
        return $this->widgetposition;
    }

    /**
     * @param FragSeb\Dashboard\Widget\WidgetPosition
     *
     * @return $this
     */
    public function setWidgetposition($widgetposition)
    {
        $this->widgetposition = $widgetposition;

        return $this;
    }

    /**
     * @return array $docblock
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return array $data
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param array $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return array
     */
    public function getSettingView()
    {
        return $this->settingView;
    }

    /**
     * @param array $setting
     */
    public function setSettingView($setting)
    {
        $this->settingView = $setting;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }



    /**
     * @param string $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return array
     */
    public function getWidgetData()
    {

        if (class_exists($this->adapter)) {

            $adapter = new $this->adapter();
            if ($adapter instanceof WidgetModelAdapterInterface) {

                return $adapter->get($this->getSettings());
            }

            return [];
        }

        return [];
    }

    public function getHash()
    {
        $hash = [
            $this->getId(),
            $this->getSettingView(),
            $this->getView(),
        ];

        return hash('ripemd160', json_encode($hash));
    }
}

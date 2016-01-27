<?php

namespace FragSeb\Dashboard\Adapter;

use FragSeb\Dashboard\Entity\DashboardAdapterInterface;
use FragSeb\Dashboard\Exception\DashboardAdapterException;
use FragSeb\Dashboard\Model\DashboardModelInterface;
use FragSeb\Dashboard\Entity\DashboardEntity;


/**
 * Class DashboardRepository
 */
final class DashboardModelAdapter implements DashboardModelInterface, DashboardAdapterInterface
{

    /**
     * @var DashboardModelInterface
     */
    private $dashboard;
    /**
     * @var FragSeb\Dashboard\Entity\DashboardEntity
     */
    private $entity;


    function __construct() {

        $this->dashboard = \pz_dashboard::get();

        if(null === $this->dashboard) {
            $this->dashboard = new \pz_dashboard();
            $this->dashboard->create();
        }

        if(!$this->dashboard instanceof DashboardModelInterface) {
            throw new DashboardAdapterException('That is not the correct instance');
        }

        $this->entity = new DashboardEntity($this->dashboard->getVars());

    }

    /**
     *
     */
    public function create()
    {
        $this->dashboard->create();
    }

    /**
     *
     */
    public function update()
    {
        $this->dashboard->update();
    }

    /**
     *
     */
    public function remove()
    {
        $this->dashboard->remove();
    }

    /**
     * @return FragSeb\Dashboard\Entity\DashboardEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function setData($data)
    {
        $this->entity->setData($data);
        $this->dashboard->setVar('data', $data);

        return $this;
    }
}
<?php

namespace FragSeb\Dashboard\Entity;

use FragSeb\Dashboard\DashboardMapper;
use FragSeb\Dashboard\Model\DashboardInterface;

class DashboardEntity
{
    private $id;

    private $user_id;

    private $data;

    private $stamp;

    public function __construct(array $entity)
    {
        $this->id = $entity['id'];
        $this->user_id = $entity['user_id'];
        $this->data = $entity['data'];
        $this->stamp = $entity['stamp'];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return DashboardEntity
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return \FragSeb\Dashboard\Dashboard|null
     */
    public function getDashboard()
    {
        $dashboard = DashboardMapper::toObject(json_decode($this->data));

        if($dashboard instanceof DashboardInterface) {
            return $dashboard;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getStamp()
    {
        return $this->stamp;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


}
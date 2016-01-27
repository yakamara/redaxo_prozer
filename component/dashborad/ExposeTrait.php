<?php

namespace FragSeb\Dashboard;

/**
 * Trait ExposeTrait
 */
trait ExposeTrait
{

    /**
     * @return array
     */
    public function expose()
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {

            if (is_object($value)) {
                $data[$key] = $value->expose();
                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }
}
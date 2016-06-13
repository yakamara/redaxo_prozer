<?php
/**
 * User: Jochen
 * Date: 13.11.15
 * Time: 19:46
 */

namespace FragSeb\Dashboard\Model;


interface WidgetModelAdapterInterface
{

    /**
     * The get methode is a reference of model and
     * it is very important that the return is a array.
     *
     * @param array|null $settings
     * @return array
     */
    public function get(array $settings = null);

}
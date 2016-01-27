<?php

/**
 * Created by PhpStorm.
 * User: Jochen
 * Date: 01.11.15
 * Time: 20:51
 */
class pz_dashboard_controller extends pz_controller
{
    public function checkPerm()
    {
        if (pz::getUser()) {
            return true;
        }
        return false;
    }

    protected function getNavigation($p = [])
    {
        return pz_screen::getNavigation(
            $p,
            $this->navigation,
            $this->function,
            $this->name
        );
    }

    protected function response($p, $section)
    {
        $f = new pz_fragment();
        $f->setVar('header', pz_screen::getHeader($p), false);
        $f->setVar('function', $this->getNavigation($p), false);
        $f->setVar('section', $section, false);

        return $f->parse('pz_screen_gridster.tpl');
    }

    protected function widgetView($template)
    {
        $f = new pz_fragment();

        return $f->parse('dashboard/' . $template . '.tpl');
    }
}
<?php

class pz_history_screen
{
    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    public static function getSearchForm($p = [])
    {
        $link_refresh = pz::url(
            'screen',
            $p['controll'],
            $p['function'],
            array_merge($p['linkvars'], ['mode' => 'history_search'])
        );

        if (!isset($p['title_search'])) {
            $p['title_search'] = pz_i18n::msg('search_for_history_entries');
        }

        $p['linkvars']['mode'] = 'list';

        $return = '
        <header>
          <div class="header">
            <h1 class="hl1">'.$p['title_search'].'</h1>
          </div>
        </header>';

        $xform = new rex_xform();
        $xform->setObjectparams('real_field_names', true);
        $xform->setObjectparams('form_showformafterupdate', true);
        $xform->setObjectparams('form_action', "javascript:pz_loadFormPage('".$p['layer_list']."','history_search_form','".pz::url('screen', $p['controll'], $p['function'], $p['linkvars'])."')");
        $xform->setObjectparams('form_id', 'history_search_form');

        $xform->setValueField('objparams', ['fragment', 'pz_screen_xform.tpl', 'runtime']);
        $xform->setValueField('pz_date_screen', ['search_date_from', pz_i18n::msg('search_date_from')]);
        $xform->setValueField('pz_date_screen', ['search_date_to', pz_i18n::msg('search_date_to')]);
        $xform->setValueField('pz_select_screen', ['search_modi', pz_i18n::msg('history_modi'), pz_history::getModi(), '', '', 0, pz_i18n::msg('please_choose')]);
        $xform->setValueField('pz_select_screen', ['search_control', pz_i18n::msg('history_control'), pz_history::getControls(), '', '', 0, pz_i18n::msg('please_choose')]);
        $xform->setValueField('pz_select_screen', ['search_user_id', pz_i18n::msg('user'), pz::getUsersAsArray(pz::getUser()->getUsers()), '', '', 0, pz_i18n::msg('please_choose')]);

        /*
                $projects = pz::getUser()->getProjects();
                $xform->setValueField("pz_select_screen",array("search_project_id",pz_i18n::msg("project"),pz_project::getProjectsAsArray($projects),"","",0,pz_i18n::msg("please_choose")));

                $users = pz::getUser()->getUsers();
                $xform->setValueField("pz_select_screen",array("search_user_id",pz_i18n::msg("project"),pz_user::getUsersAsArray($users),"","",0,pz_i18n::msg("please_choose")));
        */
        // $xform->setValueField("checkbox",array("search_intrash",pz_i18n::msg("search_email_intrash")));
        $xform->setValueField('submit', ['submit', pz_i18n::msg('search'), '', 'search']);
        $return .= $xform->getForm();

        $return = '<div id="history_search" class="design1col xform-search" data-url="'.$link_refresh.'">'.$return.'</div>';
        return $return;
    }

    // --------------------------------------------------------------- Listviews

    public static function getListView($entries, $p = [])
    {
        $paginate_screen = new pz_paginate_screen($entries);
        $content = $paginate_screen->getPlainView($p);

        $list = '';
        foreach ($paginate_screen->getCurrentElements() as $entry) {
            if ($e = new pz_history_entry_screen($entry)) {
                $list .= '<div class="history">'.$e->getBlockView($p).'</div>';
            }
        }

        $content = $content.$list;
        $content .= $paginate_screen->setPaginateLoader($p, '#history_list');

        if ($paginate_screen->isScrollPage()) {
            return $content;
        }

        $f = new pz_fragment();
        $f->setVar('title', $p['title'], false);
        $f->setVar('content', $content, false);

        $link_refresh = pz::url('screen', $p['controll'], $p['function'], $p['linkvars']);

        if (isset($p['list_links'])) {
            $f->setVar('links', $p['list_links'], false);
        }

        // $f->setVar("orders",$orders);
        $return = $f->parse('pz_screen_list.tpl');

        if (count($entries) == 0) {
            $return .= '<div class="xform-warning">'.pz_i18n::msg('no_history_entries_found').'</div>';
        }

        return '<div id="history_list" class="design2col" data-url="'.$link_refresh.'">'.$return.'</div>';
    }
}

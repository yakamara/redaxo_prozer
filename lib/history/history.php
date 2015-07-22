<?php

class pz_history
{
    public static function getModi()
    {
        return [
            ['id' => 'login',  'label' => pz_i18n::msg('search_login')],
            ['id' => 'create',  'label' => pz_i18n::msg('search_create')],
            ['id' => 'update',  'label' => pz_i18n::msg('search_update')],
            ['id' => 'delete',  'label' => pz_i18n::msg('search_delete')],
//        array('id'=>'logout',  'label' => pz_i18n::msg('search_logout')),
            ['id' => 'download',  'label' => pz_i18n::msg('search_download')],
            ['id' => 'error',  'label' => pz_i18n::msg('search_error')],

        ];
    }

    public static function getControls()
    {
        return [
            ['id' => 'address',  'label' => pz_i18n::msg('search_address')],
            ['id' => 'calendar_event',  'label' => pz_i18n::msg('search_calendar_event')],
            ['id' => 'email','label' => pz_i18n::msg('search_email')],
            ['id' => 'project','label' => pz_i18n::msg('search_project')],
            ['id' => 'projectuser','label' => pz_i18n::msg('search_projectuser')],
            ['id' => 'project_file','label' => pz_i18n::msg('search_projectfiles')],
            ['id' => 'user','label' => pz_i18n::msg('search_user')],
            ['id' => 'clip','label' => pz_i18n::msg('search_clip')],
            ['id' => 'wiki','label' => pz_i18n::msg('search_wiki')],
        ];
    }

    public static function get($filter = [], $limit = 1000)
    {
        $w = pz::getFilter($filter);

        $sql = pz_sql::factory();
        $sql->setQuery('select * from pz_history ' . $w['where_sql'] . ' order by stamp desc LIMIT '. $limit, $w['params']);

        $history_entries = [];
        foreach ($sql->getArray() as $l) {
            $history_entry = new pz_history_entry($l);
            $history_entries[] = $history_entry;
        }

        return $history_entries;
    }
}

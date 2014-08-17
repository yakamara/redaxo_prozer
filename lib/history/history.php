<?php

class pz_history
{

  static function getModi()
  {
    return array(
        array('id'=>'login',  'label' => pz_i18n::msg('search_login')),
        array('id'=>'create',  'label' => pz_i18n::msg('search_create')),
        array('id'=>'update',  'label' => pz_i18n::msg('search_update')),
        array('id'=>'delete',  'label' => pz_i18n::msg('search_delete')),
//        array('id'=>'logout',  'label' => pz_i18n::msg('search_logout')),
        array('id'=>'download',  'label' => pz_i18n::msg('search_download'))
      );
  
  }


  static function getControls()
  {
    return array(
        array('id'=>'address',  'label' => pz_i18n::msg('search_address') ),
        array('id'=>'calendar_event',  'label' => pz_i18n::msg('search_calendar_event')),
        array('id'=>'email','label' => pz_i18n::msg('search_email')),
        array('id'=>'project','label' => pz_i18n::msg('search_project')),
        array('id'=>'projectuser','label' => pz_i18n::msg('search_projectuser')),
        array('id'=>'project_file','label' => pz_i18n::msg('search_projectfiles')),
        array('id'=>'user','label' => pz_i18n::msg('search_user')),
        array('id'=>'clip','label' => pz_i18n::msg('search_clip')),
      );
  
  }


  static function get($filter = array())
  {
    $w = pz::getFilter($filter);

    $sql = pz_sql::factory();
    $sql->setQuery('select * from pz_history ' . $w['where_sql'] . ' order by stamp desc LIMIT 1000', $w['params']);

    $history_entries = array();
    foreach ($sql->getArray() as $l) {
      $history_entry = new pz_history_entry($l);
      $history_entries[] = $history_entry;
    }

    return $history_entries;
  }

}

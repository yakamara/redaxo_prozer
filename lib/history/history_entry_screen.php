<?php

class pz_history_entry_screen
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    // --------------------------------------------------------------- Listviews

    public function getBlockView($p = [])
    {

        // address_id
        // event_id
        // file_id
        // wiki_id
        // project_id
        // history_user_id

        $data_id = $this->entry->getVar('data_id');
        $control = $this->entry->getVar('control');
        $func = $this->entry->getVar('func');
        $p['title'] = '';
        $data = $this->entry->getVar('data');
        $data = json_decode($data, true);

        $footer = '';

        switch ($control) {
            case('email'):

                if (($email = pz_email::get($data_id))) {
                    $email_screen = new pz_email_screen($email);
                    $footer = $email_screen->getBlockView($p);
                }
                $p['title'] = $data['subject'];
                break;

            case('project'):

                if (($project = pz_project::get($data_id))) {
                    $project_screen = new pz_project_screen($project);
                    $footer = $project_screen->getBlockView($p);
                }
                $p['title'] = $data['name'];
                break;

            case('projectuser'):

                break;

            case('projectfile'):

                $p['title'] = $data['name'];
                break;

            case('calendar_event'):
                if (($event = pz_calendar_event::get($data_id))) {
                    $event_screen = new pz_calendar_event_screen($event);
                    $footer = $event_screen->getBlockView($p);
                }
                if (isset($data['title'])) {
                    $p['title'] = $data['title'];
                }
                break;

            case('address'):
                if (($address = pz_address::get($data_id))) {
                    $address_screen = new pz_address_screen($address);
                    $footer = $address_screen->getBlockView($p);
                }
                $p['title'] = $data['firstname'].' '.$data['name'].' '.$data['company'];
                break;

            case('user'):
                if (($user = pz_user::get($data_id))) {
                    $user_screen = new pz_user_screen($user);
                    $footer = $user_screen->getBlockView($p);
                }
                break;

        }

        $p['show_toggle'] = false;
        if ($footer != '') {
            $p['show_toggle'] = true;
        }
        $header = $this->getHeaderView($p);

        return $header.'<footer class="hidden">'.$footer.'</footer>';
    }

    public function getHeaderView($p = [])
    {
        $user_name = '';
        $user_image = pz_user::getDefaultImage();
        if (($user = pz_user::get($this->entry->getVar('user_id')))) {
            $user_name = $user->getName();
            $user_image = $user->getInlineImage();
        }

        $project_name = '';
        if (($project = pz_project::get($this->entry->getVar('project_id')))) {
            $project_name = $project->getName();
        }

        $d = DateTime::createFromFormat('Y-m-d H:i:s', $this->entry->getVar('stamp'), pz::getDateTimeZone());
        $d = pz::getUser()->getDateTime($d);

        $title = '';
        $data = $this->entry->getVar('data');
        $data = json_decode($data, true);

        // echo '<pre>'.$this->entry->getVar("control");var_dump($data);echo '</pre>';

        $info = [];
        $info[] = $this->entry->getVar('mode');
        if ($this->entry->getVar('control') != '') {
            $info[] = $this->entry->getVar('control');
        }
        if ($this->entry->getVar('func') != '') {
            $info[] = $this->entry->getVar('func');
        }

        if ($this->entry->getVar('message') != '') {
            $info[] = $this->entry->getVar('message');
        }

        $return = '
        <header class="head">
          <figure><img src="'.$user_image.'" width="25" height="25" /></figure>
          <hgroup class="data">
            <h2 class="hl7 piped">
              <span class="name">'.strftime(pz_i18n::msg('show_datetime_normal'), $d->format('U')).' / '.$p['title'].'</span>
              <span>'.implode(' / ', $info).'</span>
              <span> '.$project_name.' / '.$user_name.' </span>';

        if ($p['show_toggle']) {
            $return .= '<a class="button bt1" href="javascript:void(0);" onclick="$(this).closest(\'.history\').find(\'footer\').toggleClass(\'hidden\');"><span class="inner">'.pz_i18n::msg('toggle').'</span></a>';
        }

        $return .= '
            </h2>
          </hgroup>
        </header>';

        return $return;
    }
}

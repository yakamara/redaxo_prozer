<?PHP

class rex_xform_validate_pz_projectuser extends rex_xform_validate_abstract
{
    public function enterObject()
    {
        return;
    }

    public function postValueAction()
    {
        if ($this->params['send'] == '1') {
            $msg = '';
            $project = $this->getElement(3);
            $user_id = (int) $this->params['value_pool']['sql']['user_id'];

            foreach ($project->getUsers() as $projectuser) {
                if ($projectuser->user->getId() == $user_id) {
                    $msg = pz_i18n::msg('error_user_already_exists');
                    break;
                }
            }

            if ($user_id < 1) {
                $msg = pz_i18n::msg('error_please_choose_user');
            }

            if ($msg != '') {
                $this->params['warning'][] = $this->params['error_class'];
                $this->params['warning_messages'][] = $msg;
            }
        }

        return;
    }

    public function getDescription()
    {
        return 'pz_projectuser';
    }
}

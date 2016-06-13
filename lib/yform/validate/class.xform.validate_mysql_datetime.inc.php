<?PHP

class rex_yform_validate_mysql_datetime extends rex_yform_validate_abstract
{
    public function enterObject()
    {
        if ($this->params['send'] == '1') {
            $field = $this->getElement(2);

            foreach ($this->obj as $o) {
                if ($o->getName() == $field) {
                    $value = $o->getValue();

                    $format = 'Y-m-d H:i:s';
                    if (!($date_object = DateTime::createFromFormat($format, $value)) || $date_object->format($format) != $value) {
                        $this->params['warning'][$o->getId()] = $this->params['error_class'];
                        $this->params['warning_messages'][$o->getId()] = $this->getElement(3);
                    }
                }
            }
        }

        return;
    }

    public function getDescription()
    {
        return 'mysql_datetime|field|errormsg';
    }
}

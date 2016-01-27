<?php

/**
 * User: Jochen
 * Date: 13.11.15
 * Time: 20:05
 */
use FragSeb\Dashboard\Model\WidgetModelAdapterInterface;

class BirthdayWidgetAdapter implements WidgetModelAdapterInterface
{

    private $settings;
    /**
     * The get methode is a reference of model and
     * it is very important that the return is a array.
     *
     * @return array
     */
    public function get(array $settings = null)
    {
        $this->settings = $settings;

        return $this->getAllBirthDayKids();
    }


    private function getAllBirthDayKids()
    {
        $contacts = [];
        $contacts = $this->query();

        if (count($contacts) === 0) {
            return [];
        }

        $data = [];
        /** @var pz_address $contact */
        foreach($contacts as $contact) {
            $birthday = $contact->getVar('birthday');

            if(null === $birthday) {
                continue;
            }

            $map = $contact->getVars();
            $map['fields'] = $contact->getFields();
            $map['photo'] = '';
            if($contact->getVar('photo')) {
                $map['photo'] = $contact->makeInlineImage($contact->getVar('photo'));
            }
            $map['diff'] = (int) $contact->getVar('birthday_in_days');

            $data['birthday'][] = $map;
        }

        return $data;
    }

    private function query()
    {
        $frame = 30;

        if($this->settings) {
            $frame = (int) $this->settings['frame'];
        }

        $sql = pz_sql::factory();
        $query = "SELECT *, (TO_DAYS(CONCAT_WS(\"-\",(YEAR(CURDATE()) + (DAYOFYEAR(birthday) < DAYOFYEAR(CURDATE()))), MONTH(birthday), DAYOFMONTH(birthday))))-(TO_DAYS(CURDATE())) AS birthday_in_days FROM pz_address WHERE TO_DAYS(CONCAT_WS(\"-\",(YEAR(CURDATE()) + (DAYOFYEAR(birthday) < DAYOFYEAR(CURDATE()))), MONTH(birthday), DAYOFMONTH(birthday))) BETWEEN TO_DAYS(CURDATE()) AND TO_DAYS(CURDATE()) + ".$frame." ORDER BY birthday DESC";

        $sql->setQuery($query);
        $addresses = [];
        foreach ($sql->getArray() as $row) {
            $addresses[] = new pz_address($row);
        }

        return $addresses;
    }
}
<?php

class pz_address extends pz_model
{
    public $vars = [];
    private $fields = [];

    public static $addresses = [],
        $sortlabels_phones = [
            'WORK',
            'CELL',
            'WORK,FAX',
            'HOME',
            'HOME,FAX',
        ],
        $sortlabels_emails = [
            'WORK',
            'HOME',
            'IPHONE',
        ],
        $sortlabels_addresses = [
            'WORK',
            'HOME',
        ];

    public function __construct($vars = [])
    {
        $this->setVars($vars);
    }

    public static function get($id = '')
    {
        if ($id == '') {
            return false;
        }
        $id = (int) $id;
        $sql = pz_sql::factory();
        $sql->setQuery('select * from pz_address where id=' . $id . ' LIMIT 2');
        $addresses = $sql->getArray();
        if (count($addresses) != 1) {
            return false;
        }
        return new self($addresses[0]);
    }

    public static function getByUri($uri)
    {
        $sql = pz_sql::factory();
        $sql->setQuery('select * from pz_address where uri="' . $uri . '" LIMIT 2');
        $addresses = $sql->getArray();
        if (count($addresses) != 1) {
            return false;
        }
        return new self($addresses[0]);
    }

    public static function getByEmail($email)
    {
        $sql = pz_sql::factory();
        $sql->setQuery('select * from pz_address_field where type="EMAIL" and value=? LIMIT 1', [$email]);
        $sql_a = $sql->getArray();

        if (count($sql_a) > 0) {
            $a = current($sql_a);
            if ($address = self::get($a['address_id'])) {
                return $address;
            }
        }
        return null;
    }

    public static function getAllByFulltext($fulltext = '', $orders = [])
    {
        $filter = [];
        if ($fulltext != '') {
            $filter[] = ['field' => 'vt', 'type' => 'like', 'value' => $fulltext];
        }
        return self::getAll($filter, $orders);
    }

    public static function getAllByEmailFulltext($fulltext = '', $orders = [])
    {
        $filter = [];
        if ($fulltext != '') {
            $filter[] = ['field' => 'vt_email', 'type' => 'like', 'value' => $fulltext];
        }
        return self::getAll($filter, $orders);
    }

    public static function getAll($filter = [], $orders = [])
    {
        $where = [];
        $params = [];

        // ----- Filter
        $return_filter = pz::getFilter($filter, $where, $params);

        // ----- Orders
        $orders[] = ['orderby' => 'id', 'sort' => 'desc'];
        $order_sql = [];
        foreach ($orders as $order) {
            $order_sql[] = $order['orderby'] . ' ' . $order['sort'];
        }

        $sql = pz_sql::factory();
        // $sql->debugsql = 1;
        $sql->setQuery('SELECT * FROM pz_address ' . $return_filter['where_sql'] . ' order by ' . implode(',', $order_sql) . ' LIMIT 5000', $return_filter['params']);
        $addresses = [];
        foreach ($sql->getArray() as $row) {
            $addresses[] = new self($row);
        }
        return $addresses;
    }

    public function getId()
    {
        return (int) $this->vars['id'];
    }

    public function getName()
    {
        return $this->makeSingleLine($this->vars['name']);
    }

    public function getFirstName()
    {
        return $this->makeSingleLine($this->vars['firstname']);
    }

    public function getFullName()
    {
        return $this->makeSingleLine(
            implode(' ',
                array_filter(
                    [$this->vars['prefix'], $this->vars['firstname'], $this->vars['additional_names'], $this->vars['name'], $this->vars['suffix']]
                )
            )
        );
    }

    public function getCompany()
    {
        return $this->makeSingleLine($this->vars['company']);
    }

    public function isCompany()
    {
        return (boolean) $this->vars['is_company'];
    }

    public function getNote()
    {
        return $this->checkMultiLine($this->vars['note']);
    }


    public function getInlineImage()
    {
        $return = '';
        $photo = trim($this->getVar('photo'));
        if ($photo != '') {
            $return = self::makeInlineImage($photo);
        }
        if ($return == '') {
            $return = self::getDefaultImage();
        }
        return $return;
    }

    public static function makeInlineImage($photo, $size = 'm', $mimetype = 'image/jpg')
    {

        // PHOTO;ENCODING=b;TYPE=JPEG:data:image/jpg;base64,iVBORw0KGgoAAAANSUhEUgAAA
        // PHOTO;ENCODING=b;TYPE=JPEG;X-ABCROP-RECTANGLE=ABClipRect_1&0&64&480&480&EZ+ Q5v4Z5Ou9atiMTeB+8w==:
        // PHOTO;BASE64=: /9j/4AAQSkZJRgABAQAAAQABAAD//gAoCgo
        // PHOTO;ENCODING=b;TYPE=JPEG:/9j/4AAQSkZJRgABAQA
        // check possible encodings: base64_decode
        // check possible image types: JPEG

        $photo = str_replace(' ', '', $photo);

        $p = strpos($photo, ',');
        if ($p !== false) {
            $photo = explode(',', $photo);
            $src = base64_decode($photo[1]);
        } else {
            $photo = explode(':', $photo);
            $src = base64_decode($photo[1]);
        }

        return pz::makeInlineImageFromSource($src, $size, $mimetype);
    }

    public static function getDefaultImage()
    {
        return '/assets/addons/prozer/css/user.png';
    }

    public function getFieldsByType($type = 'EMAIL')
    {
        $emails = [];
        foreach ($this->getFields() as $field) {
            switch ($field->getVar('type')) {
                case $type:
                    $emails[] = $field->getVar('value');
                    break;
            }
        }
        return $emails;
    }

    public function getFields()
    {
        if ($this->fields) {
            return $this->fields;
        }

        $sql = pz_sql::factory();
        $sql->setQuery('SELECT * FROM pz_address_field WHERE address_id = ? ORDER BY type ASC, preferred DESC', [$this->getId()]);
        foreach ($sql->getArray() as $row) {
            $this->fields[] = new pz_address_field($row);
        }
        return $this->fields;
    }

    public function saveToHistory($mode = 'update')
    {
        $sql = pz_sql::factory();
        $sql->setTable('pz_history')
            ->setValue('control', 'address')
            ->setValue('data_id', $this->getId())
            ->setValue('user_id', pz::getUser()->getId())
            ->setRawValue('stamp', 'NOW()')
            ->setValue('mode', $mode);
        // if ($mode != 'delete') {
        $data = $this->vars;
        unset($data['vt']);
        foreach ($this->getFields() as $field) {
            $data['fields'][] = $field->getVars();
        }
        $sql->setValue('data', json_encode($data));
        // }
        $sql->insert();
    }

    public function updateUriAndVT()
    {
        $vt = [];
        $vt[] = $this->getFullName();
        $vt[] = $this->getCompany();
        $vt[] = $this->getNote();
        $vt[] = $this->getVar('additional_names');
        $vt[] = $this->getVar('nickname');

        $vt_email = [];
        $vt_email[] = $this->getFullName();
        $vt_email[] = $this->getCompany();
        $vt_email[] = $this->getVar('additional_names');
        $vt_email[] = $this->getVar('nickname');

        foreach ($this->getFields() as $field) {
            $vt[] = $field->getVar('value');
            if ($field->getVar('type') == 'EMAIL') {
                $vt_email[] = $field->getVar('value');
            }
        }

        $sql = pz_sql::factory();
        $sql->setTable('pz_address')
            ->setWhere(['id' => $this->getId()])
            ->setValue('vt', implode(' ', $vt))
            ->setValue('vt_email', implode(' ', $vt_email));
        if ($this->getVar('uri') == '') {
            $sql->setRawValue('uri', 'CONCAT(UPPER(UUID()), ".vcf")');
        }
        $sql->update();
    }

    public function makeSingleLine($value)
    {
        return str_replace(["\n", "\r"], [' ', ''], $value);
    }

    public function checkMultiLine($value)
    {
        return str_replace(["\r"], [''], $value);
    }

    public function create()
    {
        $this->saveToHistory('create');
        $this->updateUriAndVT();

        pz_sabre_carddav_backend::incrementCtag();
    }

    public function update()
    {
        $this->saveToHistory('update');
        $this->updateUriAndVT();

        pz_sabre_carddav_backend::incrementCtag();
    }

    public function delete()
    {
        $this->saveToHistory('delete');

        pz_sql::factory()->setQuery('
            DELETE a, af
            FROM pz_address a
            LEFT JOIN pz_address_field af
            ON a.id = af.address_id
            WHERE a.id = ?
        ', [$this->vars['id']]);

        pz_sabre_carddav_backend::incrementCtag();
    }
}

<?php

class pz_sabre_principal_backend implements Sabre\DAVACL\PrincipalBackend\BackendInterface
{
    private $principals;

    public function getPrincipalsByPrefix($prefixPath)
    {
        $principals = array();

        if (in_array($prefixPath, array('principals', 'principals/users', 'users'))) {
            $sql = pz_sql::factory();
            $sql->setQuery('SELECT id, login, name, email FROM pz_user WHERE status = 1');

            foreach ($sql as $row) {
                $principals[] = $this->getPrincipalBySql($row);
            }
        }
        return $principals;
    }

    public function getPrincipalByPath($path)
    {
        $user = basename($path);

        if (isset($this->principals[$user])) {
            return $this->principals[$user];
        }

        $sql = pz_sql::factory();
        $sql->setQuery('SELECT id, login, name, email FROM pz_user WHERE status = 1 AND login = ? LIMIT 2', array($user));

        return $this->principals[$user] = $sql->getRows() == 1 ? $this->getPrincipalBySql($sql) : null;
    }

    private function getPrincipalBySql(pz_sql $sql)
    {
        return array(
            'id' => $sql->getValue('id'),
            'uri' => 'principals/users/' . strtolower($sql->getValue('login')),
            '{DAV:}displayname' => $sql->getValue('name'),
            '{http://sabredav.org/ns}email-address' => $sql->getValue('email')
        );
    }

    public function getGroupMemberSet($principal)
    {
        return array();
    }

    public function getGroupMembership($principal)
    {
        return array();
    }

    public function setGroupMemberSet($principal, array $members)
    {
    }

    function updatePrincipal($path, $mutations)
    {
        return false;
    }

    function searchPrincipals($prefixPath, array $searchProperties)
    {
        return array();
    }
}

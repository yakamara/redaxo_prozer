<?php

class pz_sabre_principal_backend implements Sabre\DAVACL\PrincipalBackend\BackendInterface
{
    private $principals;

    public function getPrincipalsByPrefix($prefixPath)
    {
        $principals = [];

        if (in_array($prefixPath, ['principals', 'principals/users', 'users'])) {
            $sql = rex_sql::factory();
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

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id, login, name, email FROM pz_user WHERE status = 1 AND login = ? LIMIT 2', [$user]);

        return $this->principals[$user] = $sql->getRows() == 1 ? $this->getPrincipalBySql($sql) : null;
    }

    public function findByUri($uri, $principalPrefix)
    {
        // TODO
    }

    private function getPrincipalBySql(rex_sql $sql)
    {
        return [
            'id' => $sql->getValue('id'),
            'uri' => 'principals/users/' . strtolower($sql->getValue('login')),
            '{DAV:}displayname' => $sql->getValue('name'),
            '{http://sabredav.org/ns}email-address' => $sql->getValue('email'),
        ];
    }

    public function getGroupMemberSet($principal)
    {
        return [];
    }

    public function getGroupMembership($principal)
    {
        return [];
    }

    public function setGroupMemberSet($principal, array $members)
    {
    }

    public function updatePrincipal($path, \Sabre\DAV\PropPatch $propPatch)
    {
        return false;
    }

    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        return [];
    }
}

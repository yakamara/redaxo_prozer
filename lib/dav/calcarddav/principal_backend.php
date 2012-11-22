<?php

class pz_sabre_principal_backend implements Sabre_DAVACL_IPrincipalBackend
{
  private $principals;

  /**
   * Returns a list of principals based on a prefix.
   *
   * This prefix will often contain something like 'principals'. You are only
   * expected to return principals that are in this base path.
   *
   * You are expected to return at least a 'uri' for every user, you can
   * return any additional properties if you wish so. Common properties are:
   *   {DAV:}displayname
   *   {http://sabredav.org/ns}email-address - This is a custom SabreDAV
   *     field that's actualy injected in a number of other properties. If
   *     you have an email address, use this property.
   *
   * @param string $prefixPath
   * @return array
   */
  public function getPrincipalsByPrefix($prefixPath)
  {
    $principals = array();

    if(in_array($prefixPath, array('principals', 'principals/users', 'users')))
    {
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT id, login, name, email FROM pz_user WHERE status = 1');

      foreach($sql as $row)
      {
        $principals[] = $this->getPrincipalBySql($row);
      }
    }
    return $principals;
  }

  /**
   * Returns a specific principal, specified by it's path.
   * The returned structure should be the exact same as from
   * getPrincipalsByPrefix.
   *
   * @param string $path
   * @return array
   */
  public function getPrincipalByPath($path)
  {
    $user = basename($path);

    if(isset($this->principals[$user]))
      return $this->principals[$user];

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT id, login, name, email FROM pz_user WHERE status = 1 AND login = ? LIMIT 2', array($user));

    return $this->principals[$user] = $sql->getRows() == 1 ? $this->getPrincipalBySql($sql) : null;
  }

  private function getPrincipalBySql(rex_sql $sql)
  {
    return array(
      'id' => $sql->getValue('id'),
      'uri' => 'principals/users/'. $sql->getValue('login'),
      '{DAV:}displayname' => $sql->getValue('name'),
      '{http://sabredav.org/ns}email-address' => $sql->getValue('email')
    );
  }

  /**
   * Returns the list of members for a group-principal
   *
   * @param string $principal
   * @return array
   */
  public function getGroupMemberSet($principal)
  {
    return array();
  }

  /**
   * Returns the list of groups a principal is a member of
   *
   * @param string $principal
   * @return array
   */
  public function getGroupMembership($principal)
  {
    return array();
  }

  /**
   * Updates the list of group members for a group principal.
   *
   * The principals should be passed as a list of uri's.
   *
   * @param string $principal
   * @param array $members
   * @return void
   */
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
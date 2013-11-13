<?php
namespace Unity\Component\Security;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
abstract class SecurityUser
{
    private $username;
    private $displayname;
    private $roles = [];

    /**
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @param string $username
     */
    protected function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Define the roles this user is a member of.
     *
     * @param array $roles
     */
    protected function setRoles(array $roles)
    {
        $this->roles = (array) $roles;
    }

    /**
     * Set the display-name of this user.
     *
     * @param string $display_name
     */
    protected function setDisplayName($display_name)
    {
        $this->displayname = $display_name;
    }

    /**
     * Returns the username of this user.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the display-name of this user.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayname;
    }

    /**
     * Returns true if this user is member of the given role.
     *
     * @param string $role
     */
    public function isMemberOf($role)
    {
        if (empty($this->roles)) {
            // empty, assume true.
            return true;
        }
        return in_array($role, $this->roles);
    }

    /**
     * Returns an array of roles this user is assigned to.
     *
     * @return array
     */
    public function getSecurityRoles()
    {
        return $this->roles;
    }
}

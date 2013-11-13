<?php
namespace Unity\Component\Security;

use Unity\Component\Annotation\Annotation;

/**
 * The Secure annotation can have 2 options:
 *
 *     ROLES        - Defines a list of roles a user must have to access
 *                    the designated controller or method.
 *     ROLE         - Defines one role a user must have.
 *
 * @author Harold Iedema <harold@iedema.me>
 */
class Secure extends Annotation
{
    /**
     * Returns an array of defined roles.
     *
     * @return array
     */
    public function getRoles()
    {
        if (isset($this->value['ROLE'])) {
            return $this->value['ROLE'];
        }
        return isset($this->value['ROLES']) ? $this->value['ROLES'] : [];
    }

    /**
     * Returns true if the given role has access to the designated
     * controller or method.
     *
     * @param string $role
     */
    public function hasRoleAccess($role)
    {
        if (empty($this->value)) {
            // No roles defined, assume true.
            return true;
        }
        if (isset($this->value['ROLE']) && strtolower($this->value['ROLE']) == strtolower($role)) {
            return true;
        }
        if (isset($this->value['ROLES']) && is_array($this->value['ROLES'])) {
            foreach ($this->value['ROLES'] as $registered_role) {
                if (strtolower($registered_role) === strtolower($role)) {
                    return true;
                }
            }
        }
        return false;
    }
}

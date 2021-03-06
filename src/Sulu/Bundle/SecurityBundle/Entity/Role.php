<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use Sulu\Component\Security\Authentication\RoleSettingInterface;

/**
 * Role.
 */
class Role extends BaseRole
{
    /**
     * @var Collection
     * @Groups({"fullRole"})
     */
    private $permissions;

    /**
     * @var Collection
     *
     * @Exclude
     */
    private $userRoles;

    /**
     * @var Collection
     *
     * @Exclude
     */
    private $groups;

    /**
     * @var Collection
     */
    private $settings;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->settings = new ArrayCollection();
    }

    /**
     * Add permissions.
     *
     * @return Role
     */
    public function addPermission(Permission $permissions)
    {
        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions.
     */
    public function removePermission(Permission $permissions)
    {
        $this->permissions->removeElement($permissions);
    }

    /**
     * Get permissions.
     *
     * @return Collection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Add userRoles.
     *
     * @return Role
     */
    public function addUserRole(UserRole $userRoles)
    {
        $this->userRoles[] = $userRoles;

        return $this;
    }

    /**
     * Remove userRoles.
     */
    public function removeUserRole(UserRole $userRoles)
    {
        $this->userRoles->removeElement($userRoles);
    }

    /**
     * Get userRoles.
     *
     * @return Collection
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }

    /**
     * Add groups.
     *
     * @return Role
     */
    public function addGroup(Group $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups.
     */
    public function removeGroup(Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups.
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add setting.
     *
     * @return Role
     */
    public function addSetting(RoleSettingInterface $setting)
    {
        $this->settings->set($setting->getKey(), $setting);

        return $this;
    }

    /**
     * Remove setting.
     */
    public function removeSetting(RoleSettingInterface $setting)
    {
        $this->settings->remove($setting->getKey());
    }

    /**
     * Get settings.
     *
     * @return Collection
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function getSetting($key)
    {
        return $this->settings->get($key);
    }
}

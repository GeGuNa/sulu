<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContactBundle\Entity;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;

/**
 * Fax.
 */
class Fax
{
    /**
     * @var string
     * @Groups({"fullAccount", "partialAccount", "fullContact", "partialContact"})
     */
    private $fax;

    /**
     * @var int
     * @Groups({"fullAccount", "partialAccount", "fullContact", "partialContact"})
     */
    private $id;

    /**
     * @var FaxType
     * @Groups({"fullAccount", "fullContact"})
     */
    private $faxType;

    /**
     * @var Collection
     * @Exclude
     */
    private $contacts;

    /**
     * @var Collection
     * @Exclude
     */
    private $accounts;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->accounts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set fax.
     *
     * @param string $fax
     *
     * @return Fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax.
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set faxType.
     *
     * @return Fax
     */
    public function setFaxType(FaxType $faxType)
    {
        $this->faxType = $faxType;

        return $this;
    }

    /**
     * Get faxType.
     *
     * @return FaxType
     */
    public function getFaxType()
    {
        return $this->faxType;
    }

    /**
     * Add contacts.
     *
     * @return Fax
     */
    public function addContact(\Sulu\Component\Contact\Model\ContactInterface $contacts)
    {
        $this->contacts[] = $contacts;

        return $this;
    }

    /**
     * Remove contacts.
     */
    public function removeContact(\Sulu\Component\Contact\Model\ContactInterface $contacts)
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * Get contacts.
     *
     * @return Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add accounts.
     *
     * @return Fax
     */
    public function addAccount(AccountInterface $account)
    {
        $this->accounts[] = $account;

        return $this;
    }

    /**
     * Remove accounts.
     */
    public function removeAccount(AccountInterface $account)
    {
        $this->accounts->removeElement($account);
    }

    /**
     * Get accounts.
     *
     * @return Collection
     */
    public function getAccounts()
    {
        return $this->accounts;
    }
}

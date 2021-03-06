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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

/**
 * interface for accounts.
 */
interface AccountInterface
{
    /**
     * Set name.
     *
     * @param string $name
     *
     * @return AccountInterface
     */
    public function setName($name);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set externalId.
     *
     * @param string $externalId
     *
     * @return AccountInterface
     */
    public function setExternalId($externalId);

    /**
     * Get externalId.
     *
     * @return string
     */
    public function getExternalId();

    /**
     * Set number.
     *
     * @param string $number
     *
     * @return AccountInterface
     */
    public function setNumber($number);

    /**
     * Get number.
     *
     * @return string
     */
    public function getNumber();

    /**
     * Set corporation.
     *
     * @param string $corporation
     *
     * @return AccountInterface
     */
    public function setCorporation($corporation);

    /**
     * Get corporation.
     *
     * @return string
     */
    public function getCorporation();

    /**
     * Set uid.
     *
     * @param string $uid
     *
     * @return AccountInterface
     */
    public function setUid($uid);

    /**
     * Get uid.
     *
     * @return string
     */
    public function getUid();

    /**
     * Set registerNumber.
     *
     * @param string $registerNumber
     *
     * @return AccountInterface
     */
    public function setRegisterNumber($registerNumber);

    /**
     * Get registerNumber.
     *
     * @return string
     */
    public function getRegisterNumber();

    /**
     * Set placeOfJurisdiction.
     *
     * @param string $placeOfJurisdiction
     *
     * @return AccountInterface
     */
    public function setPlaceOfJurisdiction($placeOfJurisdiction);

    /**
     * Get placeOfJurisdiction.
     *
     * @return string
     */
    public function getPlaceOfJurisdiction();

    /**
     * Set mainEmail.
     *
     * @param string $mainEmail
     *
     * @return AccountInterface
     */
    public function setMainEmail($mainEmail);

    /**
     * Get mainEmail.
     *
     * @return string
     */
    public function getMainEmail();

    /**
     * Set mainPhone.
     *
     * @param string $mainPhone
     *
     * @return AccountInterface
     */
    public function setMainPhone($mainPhone);

    /**
     * Get mainPhone.
     *
     * @return string
     */
    public function getMainPhone();

    /**
     * Set mainFax.
     *
     * @param string $mainFax
     *
     * @return AccountInterface
     */
    public function setMainFax($mainFax);

    /**
     * Set logo.
     *
     * @param Media $logo
     *
     * @return AccountInterface
     */
    public function setLogo($logo);

    /**
     * Get logo.
     *
     * @return Media
     */
    public function getLogo();

    /**
     * Get mainFax.
     *
     * @return string
     */
    public function getMainFax();

    /**
     * Set mainUrl.
     *
     * @param string $mainUrl
     *
     * @return AccountInterface
     */
    public function setMainUrl($mainUrl);

    /**
     * Get mainUrl.
     *
     * @return string
     */
    public function getMainUrl();

    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * @return \DateTime
     */
    public function getCreated();

    /**
     * @param \DateTime $created
     */
    public function setCreated($created);

    /**
     * @return \DateTime
     */
    public function getChanged();

    /**
     * @param \DateTime $changed
     */
    public function setChanged($changed);

    public function getChanger();

    public function setChanger($changer);

    public function getCreator();

    public function setCreator($creator);

    /**
     * @return Contact
     */
    public function getMainContact();

    /**
     * @param Contact $mainContact
     */
    public function setMainContact($mainContact);

    /**
     * Set lft.
     *
     * @param int $lft
     *
     * @return Account
     */
    public function setLft($lft);

    /**
     * Get lft.
     *
     * @return int
     */
    public function getLft();

    /**
     * Set rgt.
     *
     * @param int $rgt
     *
     * @return Account
     */
    public function setRgt($rgt);

    /**
     * Get rgt.
     *
     * @return int
     */
    public function getRgt();

    /**
     * Set depth.
     *
     * @param int $depth
     *
     * @return Account
     */
    public function setDepth($depth);

    /**
     * Get depth.
     *
     * @return int
     */
    public function getDepth();

    /**
     * Set parent.
     *
     * @param AccountInterface $parent
     *
     * @return Account
     */
    public function setParent(self $parent = null);

    /**
     * Get parent.
     *
     * @return AccountInterface
     */
    public function getParent();

    /**
     * Add urls.
     *
     * @return Account
     */
    public function addUrl(Url $urls);

    /**
     * Remove urls.
     */
    public function removeUrl(Url $urls);

    /**
     * Get urls.
     *
     * @return Collection
     */
    public function getUrls();

    /**
     * Add phones.
     *
     * @return Account
     */
    public function addPhone(Phone $phones);

    /**
     * Remove phones.
     */
    public function removePhone(Phone $phones);

    /**
     * Get phones.
     *
     * @return Collection
     */
    public function getPhones();

    /**
     * Add emails.
     *
     * @return Account
     */
    public function addEmail(Email $emails);

    /**
     * Remove emails.
     */
    public function removeEmail(Email $emails);

    /**
     * Get emails.
     *
     * @return Collection
     */
    public function getEmails();

    /**
     * Add notes.
     *
     * @return Account
     */
    public function addNote(Note $notes);

    /**
     * Remove notes.
     */
    public function removeNote(Note $notes);

    /**
     * Get notes.
     *
     * @return Collection
     */
    public function getNotes();

    /**
     * Get children.
     *
     * @return Collection
     */
    public function getChildren();

    /**
     * Add faxes.
     *
     * @return Account
     */
    public function addFax(Fax $faxes);

    /**
     * Remove faxes.
     */
    public function removeFax(Fax $faxes);

    /**
     * Get faxes.
     *
     * @return Collection
     */
    public function getFaxes();

    /**
     * Add social media profile.
     *
     * @return Account
     */
    public function addSocialMediaProfile(SocialMediaProfile $socialMediaProfile);

    /**
     * Remove social media profile.
     */
    public function removeSocialMediaProfile(SocialMediaProfile $socialMediaProfile);

    /**
     * Get social media profiles.
     *
     * @return Collection
     */
    public function getSocialMediaProfiles();

    /**
     * Add bankAccounts.
     *
     * @return Account
     */
    public function addBankAccount(BankAccount $bankAccounts);

    /**
     * Remove bankAccounts.
     */
    public function removeBankAccount(BankAccount $bankAccounts);

    /**
     * Get bankAccounts.
     *
     * @return Collection
     */
    public function getBankAccounts();

    /**
     * Add tags.
     *
     * @return Account
     */
    public function addTag(TagInterface $tags);

    /**
     * Remove tags.
     */
    public function removeTag(TagInterface $tags);

    /**
     * Get tags.
     *
     * @return Collection
     */
    public function getTags();

    /**
     * Add accountContacts.
     *
     * @return Account
     */
    public function addAccountContact(AccountContact $accountContacts);

    /**
     * Remove accountContacts.
     */
    public function removeAccountContact(AccountContact $accountContacts);

    /**
     * Get accountContacts.
     *
     * @return Collection
     */
    public function getAccountContacts();

    /**
     * Get accountAddresses.
     *
     * @return Collection
     */
    public function getAccountAddresses();

    /**
     * Returns the main address.
     */
    public function getMainAddress();

    /**
     * Get contacts.
     *
     * @return Collection
     */
    public function getContacts();

    /**
     * Add media.
     *
     * @return Account
     */
    public function addMedia(MediaInterface $media);

    /**
     * Remove media.
     */
    public function removeMedia(MediaInterface $media);

    /**
     * Get medias.
     *
     * @return Collection
     */
    public function getMedias();

    /**
     * Add accountAddresses.
     *
     * @return Account
     */
    public function addAccountAddress(AccountAddress $accountAddresses);

    /**
     * Remove accountAddresses.
     */
    public function removeAccountAddress(AccountAddress $accountAddresses);

    /**
     * Add children.
     *
     * @param AccountInterface $child
     *
     * @return Account
     */
    public function addChild(self $child);

    /**
     * Remove children.
     *
     * @param AccountInterface $child
     */
    public function removeChild(self $child);

    /**
     * Add categories.
     *
     * @return Account
     */
    public function addCategory(CategoryInterface $category);

    /**
     * Remove categories.
     */
    public function removeCategory(CategoryInterface $category);

    /**
     * Get categories.
     *
     * @return Collection
     */
    public function getCategories();
}

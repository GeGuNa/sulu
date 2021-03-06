<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Contact\Model;

use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContactBundle\Entity\AccountContact;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ContactBundle\Entity\BankAccount;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Sulu\Bundle\ContactBundle\Entity\ContactLocale;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\ContactBundle\Entity\Fax;
use Sulu\Bundle\ContactBundle\Entity\Note;
use Sulu\Bundle\ContactBundle\Entity\Phone;
use Sulu\Bundle\ContactBundle\Entity\SocialMediaProfile;
use Sulu\Bundle\ContactBundle\Entity\Url;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Entity\Tag;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

/**
 * Contact interface.
 */
interface ContactInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set first name.
     *
     * @param string $firstName
     *
     * @return ContactInterface
     */
    public function setFirstName($firstName);

    /**
     * Get first name.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set middle name.
     *
     * @param string $middleName
     *
     * @return ContactInterface
     */
    public function setMiddleName($middleName);

    /**
     * Get middle name.
     *
     * @return string
     */
    public function getMiddleName();

    /**
     * Set last name.
     *
     * @param string $lastName
     *
     * @return ContactInterface
     */
    public function setLastName($lastName);

    /**
     * Get last name.
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set title.
     *
     * @param object $title
     *
     * @return ContactInterface
     */
    public function setTitle($title);

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set position.
     *
     * @param string $position
     *
     * @return ContactInterface
     */
    public function setPosition($position);

    /**
     * Get position.
     *
     * @return string
     */
    public function getPosition();

    /**
     * Set birthday.
     *
     * @param \DateTime $birthday
     *
     * @return ContactInterface
     */
    public function setBirthday($birthday);

    /**
     * Get birthday.
     *
     * @return \DateTime
     */
    public function getBirthday();

    /**
     * Add locale.
     *
     * @return ContactInterface
     */
    public function addLocale(ContactLocale $locale);

    /**
     * Remove locale.
     */
    public function removeLocale(ContactLocale $locale);

    /**
     * Get locales.
     *
     * @return Collection
     */
    public function getLocales();

    /**
     * Add note.
     *
     * @return ContactInterface
     */
    public function addNote(Note $note);

    /**
     * Remove note.
     */
    public function removeNote(Note $note);

    /**
     * Get notes.
     *
     * @return Collection
     */
    public function getNotes();

    /**
     * Add email.
     *
     * @return ContactInterface
     */
    public function addEmail(Email $email);

    /**
     * Remove email.
     */
    public function removeEmail(Email $email);

    /**
     * Get emails.
     *
     * @return Collection
     */
    public function getEmails();

    /**
     * Add phone.
     *
     * @return ContactInterface
     */
    public function addPhone(Phone $phone);

    /**
     * Remove phone.
     */
    public function removePhone(Phone $phone);

    /**
     * Get phones.
     *
     * @return Collection
     */
    public function getPhones();

    /**
     * Add fax.
     *
     * @return ContactInterface
     */
    public function addFax(Fax $fax);

    /**
     * Remove fax.
     */
    public function removeFax(Fax $fax);

    /**
     * Get faxes.
     *
     * @return Collection
     */
    public function getFaxes();

    /**
     * Add social media profile.
     *
     * @return ContactInterface
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
     * Add url.
     *
     * @return ContactInterface
     */
    public function addUrl(Url $url);

    /**
     * Remove url.
     */
    public function removeUrl(Url $url);

    /**
     * Get urls.
     *
     * @return Collection
     */
    public function getUrls();

    /**
     * Set form of address.
     *
     * @param int $formOfAddress
     *
     * @return ContactInterface
     */
    public function setFormOfAddress($formOfAddress);

    /**
     * Get form of address.
     *
     * @return int
     */
    public function getFormOfAddress();

    /**
     * Add tag.
     *
     * @return ContactInterface
     */
    public function addTag(TagInterface $tag);

    /**
     * Remove tag.
     */
    public function removeTag(TagInterface $tag);

    /**
     * Get tags.
     *
     * @return Collection
     */
    public function getTags();

    /**
     * Parse tags to array containing tag names.
     *
     * @return array
     */
    public function getTagNameArray();

    /**
     * Set salutation.
     *
     * @param string $salutation
     *
     * @return ContactInterface
     */
    public function setSalutation($salutation);

    /**
     * Get salutation.
     *
     * @return string
     */
    public function getSalutation();

    /**
     * Add account contact.
     *
     * @return ContactInterface
     */
    public function addAccountContact(AccountContact $accountContact);

    /**
     * Remove account contact.
     */
    public function removeAccountContact(AccountContact $accountContact);

    /**
     * Get account contacts.
     *
     * @return Collection
     */
    public function getAccountContacts();

    /**
     * Set newsletter.
     *
     * @param bool $newsletter
     *
     * @return ContactInterface
     */
    public function setNewsletter($newsletter);

    /**
     * Get newsletter.
     *
     * @return bool
     */
    public function getNewsletter();

    /**
     * Set gender.
     *
     * @param string $gender
     *
     * @return ContactInterface
     */
    public function setGender($gender);

    /**
     * Get gender.
     *
     * @return string
     */
    public function getGender();

    /**
     * Returns main account.
     *
     * @return AccountInterface|null
     */
    public function getMainAccount();

    /**
     * Set main email.
     *
     * @param string $mainEmail
     *
     * @return ContactInterface
     */
    public function setMainEmail($mainEmail);

    /**
     * Get main email.
     *
     * @return string
     */
    public function getMainEmail();

    /**
     * Set main phone.
     *
     * @param string $mainPhone
     *
     * @return ContactInterface
     */
    public function setMainPhone($mainPhone);

    /**
     * Get main phone.
     *
     * @return string
     */
    public function getMainPhone();

    /**
     * Set main fax.
     *
     * @param string $mainFax
     *
     * @return ContactInterface
     */
    public function setMainFax($mainFax);

    /**
     * Get main fax.
     *
     * @return string
     */
    public function getMainFax();

    /**
     * Set main url.
     *
     * @param string $mainUrl
     *
     * @return ContactInterface
     */
    public function setMainUrl($mainUrl);

    /**
     * Get main url.
     *
     * @return string
     */
    public function getMainUrl();

    /**
     * Add contact address.
     *
     * @return ContactInterface
     */
    public function addContactAddress(ContactAddress $contactAddress);

    /**
     * Remove contact address.
     */
    public function removeContactAddress(ContactAddress $contactAddress);

    /**
     * Get contact addresses.
     *
     * @return Collection
     */
    public function getContactAddresses();

    /**
     * Returns addresses.
     */
    public function getAddresses();

    /**
     * Returns the main address.
     */
    public function getMainAddress();

    /**
     * Add medias.
     *
     * @return ContactInterface
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
     * Get the contacts avatar.
     *
     * @return Media
     */
    public function getAvatar();

    /**
     * Sets the avatar for the contact.
     *
     * @param Media $avatar
     */
    public function setAvatar($avatar);

    /**
     * Add category.
     *
     * @return ContactInterface
     */
    public function addCategory(CategoryInterface $category);

    /**
     * Remove category.
     */
    public function removeCategory(CategoryInterface $category);

    /**
     * Get categories.
     *
     * @return Collection
     */
    public function getCategories();

    /**
     * Add bank account.
     *
     * @return ContactInterface
     */
    public function addBankAccount(BankAccount $bankAccount);

    /**
     * Remove bank account.
     */
    public function removeBankAccount(BankAccount $bankAccount);

    /**
     * Get bankAccounts.
     *
     * @return Collection
     */
    public function getBankAccounts();
}

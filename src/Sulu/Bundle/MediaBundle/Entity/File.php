<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Security\Authentication\UserInterface;

/**
 * File.
 */
class File implements AuditableInterface
{
    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $changed;

    /**
     * @var int
     */
    private $version;

    /**
     * @var int
     */
    private $id;

    /**
     * @var DoctrineCollection
     */
    private $fileVersions;

    /**
     * @var MediaInterface
     */
    private $media;

    /**
     * @var UserInterface
     */
    private $changer;

    /**
     * @var UserInterface
     */
    private $creator;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->fileVersions = new ArrayCollection();
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get changed.
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * Set changed.
     *
     * @return $this
     */
    public function setChanged(\DateTime $changed)
    {
        $this->changed = $changed;

        return $this;
    }

    /**
     * Set version.
     *
     * @param int $version
     *
     * @return File
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
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
     * Add fileVersions.
     *
     * @return File
     */
    public function addFileVersion(FileVersion $fileVersions)
    {
        $this->fileVersions[] = $fileVersions;

        return $this;
    }

    /**
     * Remove fileVersions.
     */
    public function removeFileVersion(FileVersion $fileVersions)
    {
        $this->fileVersions->removeElement($fileVersions);
    }

    /**
     * Get fileVersions.
     *
     * @return DoctrineCollection|FileVersion[]
     */
    public function getFileVersions()
    {
        return $this->fileVersions;
    }

    /**
     * Get file version.
     *
     * @param int $version
     *
     * @return FileVersion|null
     */
    public function getFileVersion($version)
    {
        /** @var FileVersion $fileVersion */
        foreach ($this->fileVersions as $fileVersion) {
            if ($fileVersion->getVersion() === $version) {
                return $fileVersion;
            }
        }

        return null;
    }

    /**
     * Get latest file version.
     *
     * @return FileVersion
     */
    public function getLatestFileVersion()
    {
        return $this->getFileVersion($this->version);
    }

    /**
     * Set media.
     *
     * @return File
     */
    public function setMedia(MediaInterface $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media.
     *
     * @return MediaInterface
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set changer.
     *
     * @param UserInterface $changer
     *
     * @return File
     */
    public function setChanger(UserInterface $changer = null)
    {
        $this->changer = $changer;

        return $this;
    }

    /**
     * Get changer.
     *
     * @return UserInterface
     */
    public function getChanger()
    {
        return $this->changer;
    }

    /**
     * Set creator.
     *
     * @param UserInterface $creator
     *
     * @return File
     */
    public function setCreator(UserInterface $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator.
     *
     * @return UserInterface
     */
    public function getCreator()
    {
        return $this->creator;
    }
}

<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\Compat\Structure;

use Sulu\Component\Content\Compat\PageInterface;

class PageBridge extends StructureBridge implements PageInterface
{
    public function getView()
    {
        return $this->structure->view;
    }

    public function getController()
    {
        return $this->structure->controller;
    }

    public function getUrls()
    {
        return $this->inspector->getLocalizedUrlsForPage($this->getDocument());
    }

    public function getLanguageCode()
    {
        if (!$this->document) {
            return $this->locale;
        }

        // return original locale for shadow or ghost pages
        if ($this->getIsShadow() || ($this->getType() && 'ghost' === $this->getType()->getName())) {
            return $this->inspector->getOriginalLocale($this->getDocument());
        }

        return parent::getLanguageCode();
    }

    public function getCacheLifeTime()
    {
        return $this->structure->cacheLifetime;
    }

    public function getOriginTemplate()
    {
        return $this->structure->name;
    }

    public function setOriginTemplate($originTemplate)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getNavContexts()
    {
        return $this->document->getNavigationContexts();
    }

    public function setNavContexts($navContexts)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getExt()
    {
        return $this->document->getExtensionsData();
    }

    public function setExt($data)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getInternalLinkContent()
    {
        $target = $this->getDocument()->getRedirectTarget();
        if (!$target) {
            throw new \RuntimeException(\sprintf(
                'No redirect target set on document at path "%s" with redirect type "%s"',
                $this->inspector->getPath($this->document),
                $this->document->getRedirectType()
            ));
        }

        return $this->documentToStructure($target);
    }

    public function setInternalLinkContent($internalLinkContent)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function setInternal($internal)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function setNodeState($state)
    {
        $this->readOnlyException(__METHOD__);
    }
}

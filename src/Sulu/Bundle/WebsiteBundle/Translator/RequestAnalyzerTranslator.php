<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\WebsiteBundle\Translator;

use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Wrapper for translator to lazy initialize locale with request-analyzer.
 */
class RequestAnalyzerTranslator implements TranslatorInterface
{
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RequestAnalyzerInterface
     */
    private $requestAnalyzer;

    public function __construct(TranslatorInterface $translator, RequestAnalyzerInterface $requestAnalyzer)
    {
        $this->translator = $translator;
        $this->requestAnalyzer = $requestAnalyzer;
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $this->initialize();

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $this->initialize();

        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    public function setLocale($locale)
    {
        // don't initialize here because of the TranslateListener (will be called on every request)

        return $this->translator->setLocale($locale);
    }

    public function getLocale()
    {
        $this->initialize();

        return $this->translator->getLocale();
    }

    private function initialize()
    {
        if ($this->initialized || null === $this->requestAnalyzer->getCurrentLocalization()) {
            return;
        }

        $this->translator->setLocale($this->requestAnalyzer->getCurrentLocalization()->getLocale(Localization::LCID));
        $this->initialized = true;
    }
}

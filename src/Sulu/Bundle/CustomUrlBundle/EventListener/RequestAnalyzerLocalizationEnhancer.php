<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CustomUrlBundle\EventListener;

use Sulu\Component\Webspace\Analyzer\EnhancerInterface;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Update localization in case of custom-url route.
 */
class RequestAnalyzerLocalizationEnhancer implements EnhancerInterface
{
    /**
     * Update locale in request analyzer.
     */
    public function enhance(Request $request, RequestAnalyzerInterface $requestAnalyzer)
    {
        if (null === $request->get('_custom_url')) {
            return;
        }

        $requestAnalyzer->updateLocale($request->get('_custom_url')->getTargetLocale(), $request);
    }
}

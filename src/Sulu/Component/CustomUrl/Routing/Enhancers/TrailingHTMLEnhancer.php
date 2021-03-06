<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\CustomUrl\Routing\Enhancers;

use Sulu\Component\CustomUrl\Document\CustomUrlBehavior;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\HttpFoundation\Request;

/**
 * Redirects to a non html uri.
 */
class TrailingHTMLEnhancer extends AbstractEnhancer
{
    protected function doEnhance(
        CustomUrlBehavior $customUrl,
        Webspace $webspace,
        array $defaults,
        Request $request
    ) {
        if ('.html' !== \substr($request->getRequestUri(), -5, 5)) {
            return [];
        }

        return [
            '_finalized' => true,
            '_controller' => 'SuluWebsiteBundle:Redirect:redirect',
            'url' => \substr($request->getUri(), 0, -5),
        ];
    }
}

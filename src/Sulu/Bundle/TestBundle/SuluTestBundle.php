<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\TestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuluTestBundle extends Bundle
{
    public static function getConfigDir()
    {
        return __DIR__ . '/Resources/dist';
    }
}

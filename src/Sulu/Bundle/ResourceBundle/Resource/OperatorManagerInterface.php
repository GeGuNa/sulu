<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ResourceBundle\Resource;

/**
 * Interface for the repository of the operator manager
 * Interface OperatorManagerInterface.
 */
interface OperatorManagerInterface
{
    /**
     * Returns all operators with a specific locale.
     *
     * @param $locale
     */
    public function findAllByLocale($locale);
}

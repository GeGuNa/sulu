<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ResourceBundle\Entity;

/**
 * The interface for the condition group entity repository
 * Interface ConditionGroupRepositoryInterface.
 */
interface ConditionGroupRepositoryInterface
{
    /**
     * Finds an entity by id.
     *
     * @param $id
     */
    public function findById($id);
}

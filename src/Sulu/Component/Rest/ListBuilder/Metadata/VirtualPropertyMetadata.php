<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Rest\ListBuilder\Metadata;

/**
 * Describes a property which is not linked to a real property on the class.
 */
class VirtualPropertyMetadata extends PropertyMetadata
{
    public function __construct($class, $name)
    {
        $this->class = $class;
        $this->name = $name;
    }

    public function serialize()
    {
        return \serialize(
            [
                $this->class,
                $this->name,
                $this->metadata,
            ]
        );
    }

    public function unserialize($str)
    {
        list($this->class,
            $this->name,
            $this->metadata) = \unserialize($str);
    }
}

<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Rest\ListBuilder\Expression\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\AbstractDoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Expression\BetweenExpressionInterface;

/**
 * Represents a BETWEEN expression for doctrine - needs a field and two values.
 */
class DoctrineBetweenExpression extends AbstractDoctrineExpression implements BetweenExpressionInterface
{
    /**
     * Field descriptor used for comparison.
     *
     * @var AbstractDoctrineFieldDescriptor
     */
    protected $field;

    /**
     * @var mixed
     */
    protected $start;

    /**
     * @var mixed
     */
    protected $end;

    /**
     * DoctrineInExpression constructor.
     *
     * @param $start
     * @param $end
     */
    public function __construct(AbstractDoctrineFieldDescriptor $field, $start, $end)
    {
        $this->start = $start;
        $this->end = $end;
        $this->field = $field;
    }

    /**
     *  Returns a statement for an expression.
     *
     * @return string
     */
    public function getStatement(QueryBuilder $queryBuilder)
    {
        $paramName1 = $this->getFieldName() . $this->getUniqueId();
        $paramName2 = $this->getFieldName() . $this->getUniqueId();
        $queryBuilder->setParameter($paramName1, $this->getStart());
        $queryBuilder->setParameter($paramName2, $this->getEnd());

        return $this->field->getSelect() . ' BETWEEN :' . $paramName1 . ' AND :' . $paramName2;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getFieldName()
    {
        return $this->field->getName();
    }
}

<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Value\EvaluatedValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class EvaluatedValueResolver implements ChainableValueResolverInterface
{
    use NotClonableTrait;

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof EvaluatedValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param EvaluatedValue $value
     *
     * @throws UnresolvableValueException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope = [],
        int $tryCounter = 0
    ): ResolvedValueWithFixtureSet
    {
        $_scope = $scope;
        $evaluateExpression = function ($_expression) use ($_scope) {
            foreach ($_scope as $_scopeVariableName => $_scopeVariableValue) {
                $$_scopeVariableName = $_scopeVariableValue;
            }

            return eval("return $_expression;");
        };

        try {
            $evaluatedExpression = $evaluateExpression($value->getValue());
        } catch (\Throwable $throwable) {
            throw new UnresolvableValueException(
                sprintf(
                    'Could not evaluate the expression "%s".',
                    $value->getValue()
                ),
                0,
                $throwable
            );
        }

        return new ResolvedValueWithFixtureSet($evaluatedExpression, $fixtureSet);
    }
}

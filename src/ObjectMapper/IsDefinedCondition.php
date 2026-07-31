<?php

namespace App\ObjectMapper;

use Symfony\Component\ObjectMapper\ConditionCallableInterface;

/**
 * @implements ConditionCallableInterface<object, object>
 */
final class IsDefinedCondition implements ConditionCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): bool
    {
        return isset($value);
    }
}

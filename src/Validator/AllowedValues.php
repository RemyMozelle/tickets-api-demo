<?php

namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AllowedValues extends Constraint
{
    public string $message = 'The "{{ property_name }}" with value "{{ value }}" is not valid';

    #[HasNamedArguments]
    public function __construct(
        /**
         * @var list<string>
         */
        public array $choices = [],
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }
}

<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AllowedValuesValidator extends ConstraintValidator
{
    public function validate(mixed $choice, Constraint $constraint): void
    {
        if (!$constraint instanceof AllowedValues) {
            throw new UnexpectedTypeException($constraint, AllowedValues::class);
        }

        if (null === $choice || '' === $choice) {
            return;
        }

        foreach ((array) $choice as $value) {
            if (!in_array($value, $constraint->choices, true)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameters([
                        '{{ property_name }}' => $this->context->getPropertyName(),
                        '{{ value }}' => $value,
                    ])
                    ->addViolation();
            }
        }
    }
}

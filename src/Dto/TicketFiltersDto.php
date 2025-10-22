<?php

namespace App\Dto;

use App\Enum\Priority;
use App\Enum\Status;
use App\Validator\AllowedValues;
use phpDocumentor\Reflection\Types\Callable_;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Webmozart\Assert\Assert as AssertAssert;

class TicketFiltersDto
{
    public function __construct(
        #[AllowedValues(choices: Status::ALL)]
        public readonly string|array $status = "",

        #[AllowedValues(choices: Priority::ALL)]
        public readonly string|array $priority = "",

        #[Assert\Date()]
        public readonly ?string $startDate = null,

        #[Assert\Date()]
        #[Assert\GreaterThanOrEqual(propertyPath: 'startDate')]
        public readonly ?string $endDate = null,

        #[Assert\Time()]
        public readonly ?string $startTime = null,

        #[Assert\Time()]
        public readonly ?string $endTime = null,
    ) {}


    #[Assert\Callback()]
    public function validate(ExecutionContextInterface $context, mixed $payload)
    {
        if ($this->endDate && $this->startDate === null) {
            $context
                ->buildViolation('Start date est obligatoire si end date est défini')
                ->atPath('startDate')
                ->addViolation();
        }
    }
}

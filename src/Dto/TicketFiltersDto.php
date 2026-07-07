<?php

namespace App\Dto;

use App\Enum\Priority;
use App\Enum\Status;
use App\Validator\AllowedValues;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

// TODO: Modifier "AllowedValues pour qu'il accepte aussi les enum"
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

        if (($this->startTime || $this->endTime) && $this->startDate === null) {
            $context->buildViolation('Start date est obligatoire si vous voulez utiliser start time ou end time.')
                ->atPath('startTime')
                ->addViolation();
        }
    }
}

<?php

namespace App\Dto;

use App\Enum\Priority;
use App\Enum\Status;
use App\Validator\AllowedValues;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

// TODO: Modifier "AllowedValues pour qu'il accepte aussi les enum"
class TicketFiltersDto
{
    public function __construct(
        /**
         * @var string|list<string>
         */
        #[OA\Property(
            description: 'Status values',
            type: 'array',
            items: new OA\Items(ref: new Model(type: Status::class))
        )]
        #[AllowedValues(choices: Status::ALL)]
        public readonly string|array $status = '',

        /**
         * @var string|list<string>
         */
        #[OA\Property(
            description: 'Priority values',
            type: 'array',
            items: new OA\Items(ref: new Model(type: Priority::class))
        )]
        #[AllowedValues(choices: Priority::ALL)]
        public readonly string|array $priority = '',
        #[OA\Property(
            description: 'Starting date',
            type: 'string',
            example: '2025-01-01',
            format: 'date'
        )]
        #[Assert\Date()]
        public readonly ?string $startDate = null,
        #[OA\Property(
            description: 'Ending date',
            type: 'string',
            example: '2026-01-01',
            format: 'date'
        )]
        #[Assert\Date()]
        #[Assert\GreaterThanOrEqual(propertyPath: 'startDate')]
        public readonly ?string $endDate = null,
        #[OA\Property(
            description: 'Starting time',
            type: 'string',
            example: '10:00:00',
            format: 'time'
        )]
        #[Assert\Time()]
        public readonly ?string $startTime = null,
        #[OA\Property(
            description: 'Ending time',
            type: 'string',
            example: '20:00:00',
            format: 'time'
        )]
        #[Assert\Time()]
        public readonly ?string $endTime = null,
    ) {
    }

    #[Assert\Callback()]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
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

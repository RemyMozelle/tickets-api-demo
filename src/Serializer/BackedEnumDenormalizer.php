<?php

namespace App\Serializer;

use BackedEnum;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BackedEnumDenormalizer implements DenormalizerInterface
{
    public function __construct(
    ) {}

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return enum_exists($type);
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        try {
            return $type::from($data);
        } catch (\ValueError $e) {
            return $e;
        }
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            BackedEnum::class => true,
        ];
    }
}
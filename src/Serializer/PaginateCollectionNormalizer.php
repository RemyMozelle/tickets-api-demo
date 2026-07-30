<?php

namespace App\Serializer;

use App\Response\PaginateCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginateCollectionNormalizer implements NormalizerInterface
{
    public function __construct(
        private UrlGeneratorInterface $router,

        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $this->assertContextHasKeys(['route_name', 'current_url', 'route_params'], $context);

        /** @var PaginateCollection<object> $object */
        $page = $object->getPage();
        $limit = $object->getLimit();
        $totalPages = $object->getTotalPages();

        $routeName = $context['route_name'];
        $routeParams = $context['route_params'];
        $currentUrl = $context['current_url'];

        $firstRouteParameters = [...$routeParams, 'page' => 1, 'limit' => $limit];
        $lastRouteParameters = [...$routeParams, 'page' => $totalPages, 'limit' => $limit];
        $nextRouteParameters = [...$routeParams, 'page' => $page + 1, 'limit' => $limit];
        $prevRouteParameters = [...$routeParams, 'page' => $page - 1, 'limit' => $limit];

        return [
            'data' => array_map(fn($data) => $this->normalizer->normalize($data, $format, $context), $object->getResults()),
            'meta' => $object->getMeta(),
            'links' => [
                'first' => $this->router->generate($routeName, $firstRouteParameters, UrlGeneratorInterface::ABSOLUTE_URL),
                'last' => $this->router->generate($routeName, $lastRouteParameters, UrlGeneratorInterface::ABSOLUTE_URL),
                'prev' => $page > 1 ? $this->router->generate($routeName, $prevRouteParameters, UrlGeneratorInterface::ABSOLUTE_URL) : null,
                'next' => $page < $totalPages ? $this->router->generate($routeName, $nextRouteParameters, UrlGeneratorInterface::ABSOLUTE_URL) : null,
                'current' => $currentUrl,
            ],
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PaginateCollection && $format === 'json';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PaginateCollection::class => true,
        ];
    }

    /**
     * @param list<string> $keys
     * @param array<string, mixed> $context
     */
    private function assertContextHasKeys(array $keys, array $context): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $context)) {
                throw new \InvalidArgumentException(sprintf(
                    'PaginatedCollectionNormalizer requires context key "%s".',
                    $key
                ));
            }
        }
    }
}

<?php

/*
 * This file is part of Pomm's SymfonyBidge package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\SymfonyBridge\Serializer\Normalizer;

use PommProject\ModelManager\Model\FlexibleEntity\FlexibleEntityInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FlexibleEntityNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, string $format = null, array $context = array()): array
    {
        return $object->extract();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof FlexibleEntityInterface;
    }
}

<?php
/*
 * This file is part of Pomm's SymfonyBidge package.
 *
 * (c) 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\SymfonyBridge\Serializer\Normalizer;

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Pomm;
use PommProject\ModelManager\Exception\ModelException;
use PommProject\ModelManager\Model\FlexibleEntity\FlexibleEntityInterface;
use PommProject\ModelManager\Session;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalizer for flexible entities.
 *
 * @package PommSymfonyBridge
 * @copyright 2017 Grégoire HUBERT
 * @author Nicolas Joseph
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class FlexibleEntityDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly Pomm $pomm)
    {
    }

    /**
     * {@inheritdoc}
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return FlexibleEntityInterface
     * @throws FoundationException
     * @throws ModelException
     * @throws \ReflectionException
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = array()): FlexibleEntityInterface
    {
        if (isset($context['session:name'])) {
            /** @var Session $session */
            $session = $this->pomm->getSession($context['session:name']);
        } else {
            /** @var Session $session */
            $session = $this->pomm->getDefaultSession();
        }

        $model_name = $context['model:name'] ?? "${type}Model";

        $model = $session->getModel($model_name);
        return $model->createEntity($data);
    }

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        $reflection = new \ReflectionClass($type);
        $interfaces = $reflection->getInterfaces();

        return isset($interfaces[FlexibleEntityInterface::class]) && is_array($data);
    }
}

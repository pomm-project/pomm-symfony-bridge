<?php
/*
 * This file is part of Pomm's SymfonyBidge package.
 *
 * (c) 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\SymfonyBridge\PropertyInfo\Extractor;

use PommProject\Foundation\Converter\ConverterClient;
use PommProject\Foundation\Converter\ConverterPooler;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Pomm;
use PommProject\ModelManager\Exception\ModelException;
use PommProject\ModelManager\Session;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Extract data using pomm.
 *
 * @package PommSymfonyBridge
 * @copyright 2015 Grégoire HUBERT
 * @author Nicolas Joseph
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class TypeExtractor implements PropertyTypeExtractorInterface
{
    public function __construct(private readonly Pomm $pomm)
    {
    }

    /**
     * @see PropertyTypeExtractorInterface
     */
    public function getTypes(string $class, string $property, array $context = array()): ?array
    {
        if (isset($context['session:name'])) {
            /** @var Session $session */
            $session = $this->pomm->getSession($context['session:name']);
        } else {
            /** @var Session $session */
            $session = $this->pomm->getDefaultSession();
        }

        $model_name = $context['model:name'] ?? "{$class}Model";

        if (!class_exists($model_name)) {
            return null;
        }

        $sql_type = $this->getSqlType($session, $model_name, $property);
        $pomm_type = $this->getPommType($session, $sql_type);

        return [
            $this->createPropertyType($pomm_type)
        ];
    }

    /**
     * Get the sql type of $property
     *
     * @param Session $session
     * @param string $model_name
     * @param string $property
     * @return string
     * @throws FoundationException
     * @throws ModelException
     */
    private function getSqlType(Session $session, string $model_name, string $property): string
    {
        $model = $session->getModel($model_name);
        $structure = $model->getStructure();

        return $structure->getTypeFor($property);
    }

    /**
     * Get the corresponding php type of a $sql_type type
     *
     * @param Session $session
     * @param string $sql_type
     * @return string
     * @throws FoundationException
     */
    private function getPommType(Session $session, string $sql_type): string
    {
        /** @var ConverterPooler $converterPooler */
        $converterPooler =  $session->getPoolerForType('converter');

        $pomm_types = $converterPooler->getConverterHolder()->getTypesWithConverterName();

        if (!isset($pomm_types[$sql_type])) {
            throw new \RuntimeException("Invalid $sql_type");
        }

        return $pomm_types[$sql_type];
    }

    /**
     * Create a new Type for the $pomm_type type
     *
     * @param string $pomm_type
     * @return Type
     */
    private function createPropertyType(string $pomm_type): Type
    {
        $class = null;

        $type = match ($pomm_type) {
            'JSON', 'Array' => Type::BUILTIN_TYPE_ARRAY,
            'Binary', 'String' => Type::BUILTIN_TYPE_STRING,
            'Boolean' => Type::BUILTIN_TYPE_BOOL,
            'Number' => Type::BUILTIN_TYPE_INT,
            default => Type::BUILTIN_TYPE_OBJECT,
        };

        return new Type($type, false, $class);
    }
}

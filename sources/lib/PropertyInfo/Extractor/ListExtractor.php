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

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Pomm;
use PommProject\ModelManager\Exception\ModelException;
use PommProject\ModelManager\Session;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;

/**
 * Extract properties list using pomm.
 *
 * @package PommSymfonyBridge
 * @copyright 2015 Grégoire HUBERT
 * @author Nicolas Joseph
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class ListExtractor implements PropertyListExtractorInterface
{
    public function __construct(private readonly Pomm $pomm)
    {
    }

    /**
     * @throws FoundationException|ModelException
     * @see PropertyListExtractorInterface
     */
    public function getProperties(string $class, array $context = array()): ?array
    {
        if (isset($context['session:name'])) {
            /** @var Session $session */
            $session = $this->pomm->getSession($context['session:name']);
        } else {
            /** @var Session $session */
            $session = $this->pomm->getDefaultSession();
        }

        $model_name = $context['model:name'] ?? "${class}Model";

        if (!class_exists($model_name)) {
            return null;
        }

        return $this->getPropertiesList($session, $model_name);
    }

    /**
     * @throws ModelException
     * @throws FoundationException
     */
    private function getPropertiesList(Session $session, string $model_name): array
    {
        $model = $session->getModel($model_name);
        $structure = $model->getStructure();

        return $structure->getFieldNames();
    }
}

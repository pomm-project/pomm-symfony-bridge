<?php
/*
 * This file is part of Pomm's SymfonyBridge package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\SymfonyBridge\Form\ChoiceList;

use PommProject\Foundation\Pomm;
use PommProject\Foundation\Session\Session;
use PommProject\ModelManager\Model\Model;
use Symfony\Component\Form\Exception\StringCastException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Flexible entity choice list for Pomm
 *
 * @package PommSymfonyBridge
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class FlexibleEntityChoiceList extends ObjectChoiceList
{

    protected $session;

    protected $model;

    protected $class;

    protected $loaded = false;

    private $preferredChoices = [];

    /**
     * Creates a new flexible entity choice list.
     *
     * @param PropertyAccessorInterface $propertyAccessor The reflection graph for reading property paths.
     */
    public function __construct(Session $session, $model, $class,  $labelPath = null, $choices = null, $groupPath = null, array $preferredChoices = [], PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->session = $session;
        $this->model = $model;
        $this->class = $class;
        $this->loaded = is_array($choices) || $choices instanceof \Traversable;
        $this->preferredChoices = $preferredChoices;

        if (!$this->loaded) {
            $choices = array();
        }

        parent::__construct($choices, $labelPath, $preferredChoices, $groupPath, null, $propertyAccessor);
    }

    /**
     * Returns the list of choices.
     *
     * @return array
     *
     * @see ChoiceListInterface
     */
    public function getChoices()
    {
        $this->load();

        return parent::getChoices();
    }

    /**
     * Returns the values for the choices.
     *
     * @return array
     *
     * @see ChoiceListInterface
     */
    public function getValues()
    {
        $this->load();

        return parent::getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredViews()
    {
        $this->load();

        return parent::getPreferredViews();
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingViews()
    {
        $this->load();

        return parent::getRemainingViews();
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForChoices(array $models)
    {
        if (empty($models)) {
            return array();
        }

        if (!$this->loaded) {
            $values = array();
            foreach ($models as $index => $model) {
                if ($model instanceof $this->class) {
                    // Make sure to convert to the right format
                    $values[$index] = $this->fixValue(current($this->getIdentifierValues($model)));
                }
            }
            return $values;
        }

        $this->load();

        $values = array();
        $availableValues = $this->getValues();

        $choices = $this->fixChoices($models);
        foreach ($choices as $i => $givenChoice) {
            if (null === $givenChoice) {
                continue;
            }

            foreach ($this->getChoices() as $j => $choice) {
                if ($this->isEqual($choice, $givenChoice)) {
                    $values[$i] = $availableValues[$j];

                    // If all choices have been assigned, skip further loops.
                    unset($choices[$i]);
                    if (0 === count($choices)) {
                        break 2;
                    }
                }
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Deprecated since version 2.4, to be removed in 3.0.
     */

    public function getIndicesForChoices(array $models)
    {

        if (empty($models)) {
            return array();
        }

        $this->load();

        $indices = array();


        $choices = $this->fixChoices($models);
        foreach ($choices as $i => $givenChoice) {
            if (null === $givenChoice) {
                continue;
            }

            foreach ($this->getChoices() as $j => $choice) {
                if ($this->isEqual($choice, $givenChoice)) {
                    $indices[$i] = $j;

                    // If all choices have been assigned, skip further loops.
                    unset($choices[$i]);
                    if (0 === count($choices)) {
                        break 2;
                    }
                }
            }
        }

        return $indices;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Deprecated since version 2.4, to be removed in 3.0.
     */
    public function getIndicesForValues(array $values)
    {
        if (empty($values)) {
            return array();
        }

        $this->load();

        return parent::getIndicesForValues($values);
    }

    /**
     * Creates a new unique index for this model.
     *
     * If the model has a single-field identifier, this identifier is used.
     *
     * Otherwise a new integer is generated.
     *
     * @param mixed $model The choice to create an index for
     *
     * @return int|string A unique index containing only ASCII letters,
     *                    digits and underscores.
     */
    protected function createIndex($model)
    {
        return current($this->getIdentifierValues($model));
    }

    /**
     * Creates a new unique value for this model.
     *
     * If the model has a single-field identifier, this identifier is used.
     *
     * Otherwise a new integer is generated.
     *
     * @param mixed $model The choice to create a value for
     *
     * @return int|string A unique value without character limitations.
     */
    protected function createValue($model)
    {
        return (string) current($this->getIdentifierValues($model));
    }


    private function getIdentifierValues($entity)
    {
        $values = [];
        $model = $this->session->getModel($this->model);

        $primaryKeys = $model->getStructure()
            ->getPrimaryKey();

        foreach($primaryKeys as $key){
            $values[] = $entity->get($key);
        }
        return $values;
    }

    /**
     * Loads the list.
     *
     * @throws StringCastException
     */
    private function load()
    {
        if ($this->loaded) {
            return;
        }

        $choices = $this->session
            ->getModel($this->model)
            ->findAll();

        try {
            parent::initialize($choices, array(), array());
            $this->loaded = true;
        } catch (StringCastException $e) {
            throw new StringCastException(str_replace('argument $labelPath', 'option "property"', $e->getMessage()), null, $e);
        }


    }

}
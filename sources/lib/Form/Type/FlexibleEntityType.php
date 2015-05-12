<?php
/*
 * This file is part of Pomm's SymfonyBridge package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\SymfonyBridge\Form\Type;

use PommProject\Foundation\Pomm;
use PommProject\SymfonyBridge\Form\ChoiceList\FlexibleEntityChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Flexible entity type for Pomm.
 *
 * @package PommSymfonyBridge
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class FlexibleEntityType extends AbstractType
{

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    private $pomm;

    public function __construct(Pomm $pomm, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->pomm = $pomm;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $propertyAccessor = $this->propertyAccessor;

        $choiceList = function (Options $options) use ($propertyAccessor, $resolver) {

            if(isset($options['connection']) && $options['connection'] !== null)
                $session = $this->pomm[$options['connection']];
            else $session = $this->pomm->getDefaultSession();

            return new FlexibleEntityChoiceList(
                $session,
                $options['model'],
                $options['class'],
                $options['property'],
                $options['choices'],
                $options['group_by'],
                $options['preferred_choices'],
                $options['suffix'],
                $options['where'],
                $propertyAccessor
            );
        };

        $resolver->setDefaults(array(
            'connection' => null,
            'template' => 'choice',
            'multiple' => false,
            'property' => null,
            'expanded' => false,
            'group_by' => null,
            'suffix'   => null,
            'where'    => null,
            'choices'  => null,
            'choice_list' => $choiceList
        ));

        $resolver->setRequired(array('model', 'class'));
        $resolver->setAllowedTypes('where', array('null', 'PommProject\Foundation\Where'));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'flexible_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

}
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
 * @author Mikael Paris
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class FlexibleEntityType extends AbstractType
{

    /**
     * @var PropertyAccessorInterface
     */
    private $property_accessor;

    /**
     * @var Pomm
     */
    private $pomm;

    /**
     * @param Pomm                      $pomm
     * @param PropertyAccessorInterface $property_accessor
     */
    public function __construct(Pomm $pomm, PropertyAccessorInterface $property_accessor = null)
    {
        $this->pomm = $pomm;
        $this->property_accessor = $property_accessor ?: PropertyAccess::createPropertyAccessor();
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $property_accessor = $this->property_accessor;

        $choice_list = function (Options $options) use ($property_accessor) {

            if(isset($options['connection']) && $options['connection'] !== null)
                $session = $this->pomm[$options['connection']];
            else $session = $this->pomm->getDefaultSession();

            return new FlexibleEntityChoiceList(
                $session,
                $options['model'],
                $options['property'],
                $options['choices'],
                $options['group_by'],
                $options['preferred_choices'],
                $options['suffix'],
                $options['where'],
                $property_accessor
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
            'choice_list' => $choice_list
        ));

        $resolver->setRequired(array('model'));
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
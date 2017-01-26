<?php
/*
 * This file is part of Pomm's SymfonyBidge package.
 *
 * (c) 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\SymfonyBridge\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @Annotation
 */
class UniqueObject extends Constraint
{

    /**
     * @var string Default message
     */
    public $message = 'A {{ model }} object already exists with fields : ({{ fields }})';
    
    /**
     * @var array List of fields
     */
    public $fields = array();
    
    /**
     * @var string FQN of Pomm Model
     */
    public $model = null;
    
    /**
     * @var string Used to set the path where the error will be attached, default is global.
     */
    public $errorPath;
    
 
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (!is_array($this->fields) && !is_string($this->fields)) {
            throw new UnexpectedTypeException($this->fields, 'array');
        }

        if (0 === count($this->fields)) {
            throw new ConstraintDefinitionException("At least one field must be specified.");
        }

        if (null !== $this->errorPath && !is_string($this->errorPath)) {
            throw new UnexpectedTypeException($this->errorPath, 'string or null');
        }
        
        if(is_null($this->model) || trim($this->model) === ''){
            throw new ConstraintDefinitionException("Pomm model must be specified.");
        }

    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions()
    {
        return array('fields','model');
    }
    
    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
}
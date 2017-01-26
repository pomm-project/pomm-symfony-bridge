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
use Symfony\Component\Validator\ConstraintValidator;
use PommProject\Foundation\Pomm;

class UniqueObjectValidator extends ConstraintValidator
{
    /**
     * @var \PommProject\Foundation\Pomm
     */
    private $pomm;
    
    public function __construct(Pomm $pomm)
    {
       $this->pomm = $pomm; 
    }
    
    /**
     * Camelize field and return object method name 
     * 
     * @param string $field
     * @return string
     */
    private function getMethodName($field)
    {
        $fm = ucfirst(str_replace('_', '', ucwords($field, '_')));
        
        return 'get'.$fm;
    }
    
    /**
     * {@inheritdoc}
     */
    public function validate($object, Constraint $constraint)
    {
        $fields         = (array) $constraint->fields;
        $model          = $constraint->model;
        $query_string   = '';
        $params         = [];
        
        foreach ($fields as $f){
            $method = $this->getMethodName($f);
            $params[] = $object->$method();
            
            if(end($fields) !== $f){
                $query_string.= $f.' = $* AND ';
            } else {
                $query_string.= $f.' = $* ';
            }
        }
        $hasObject = $this->pomm->getDefaultSession()->getModel($model)->existWhere($query_string, $params);
        
        if($hasObject){
            $this->context->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->setParameter('{{ model }}', $model)
                ->setParameter('{{ fields }}', implode(',', $fields))
                ->addViolation();
        }
    }

}
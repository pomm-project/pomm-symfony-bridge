<?php
/**
 * This file has been automaticaly generated by Pomm's generator.
 * You MIGHT NOT edit this file as your changes will be lost at next
 * generation.
 */

namespace AppBundle\Model\AutoStructure;

use PommProject\ModelManager\Model\RowStructure;

/**
 * Service
 *
 * Structure class for relation public.service.
 *
 * Class and fields comments are inspected from table and fields comments.
 * Just add comments in your database and they will appear here.
 * @see http://www.postgresql.org/docs/9.0/static/sql-comment.html
 *
 *
 *
 * @see RowStructure
 */
class Service extends RowStructure
{
    /**
     *
     * Structure definition.
     */
    public function __construct()
    {
        $this
            ->setRelation('public.service')
            ;
    }
}

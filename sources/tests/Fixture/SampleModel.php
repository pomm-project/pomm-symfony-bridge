<?php

namespace PommProject\SymfonyBridge\Tests\Fixture;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\RowStructure;

class SampleModel extends Model
{
    public function __construct()
    {
        $this->structure = (new RowStructure())
            ->setRelation('sample')
            ->setPrimaryKey(['an_integer'])
            ->addField('an_integer', 'int4')
            ->addField('a_float', 'float8')
            ->addField('a_string', 'varchar')
            ->addField('a_boolean', 'bool')
            ->addField('a_json', 'jsonb')
            ->addField('a_binary', 'bytea')
            ->addField('a_timestamp', 'timestamptz')
            ->addField('an_interval', 'interval')
            ->addField('a_point', 'point')
            ->addField('a_circle', 'circle')
            ->addField('a_box', 'box')
            ->addField('a_numrange', 'numrange')
            ->addField('a_tsrange', 'tsrange')
            ;
        $this->flexible_entity_class = Sample::class;
    }
}

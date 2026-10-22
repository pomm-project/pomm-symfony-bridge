<?php

namespace PommProject\SymfonyBridge\Tests\Unit\PropertyInfo\Extractor;

use PHPUnit\Framework\TestCase;
use PommProject\Foundation\Converter\Type\Box;
use PommProject\Foundation\Converter\Type\Circle;
use PommProject\Foundation\Converter\Type\NumRange;
use PommProject\Foundation\Converter\Type\Point;
use PommProject\Foundation\Converter\Type\TsRange;
use PommProject\Foundation\Pomm;
use PommProject\ModelManager\SessionBuilder;
use PommProject\SymfonyBridge\PropertyInfo\Extractor\TypeExtractor;
use PommProject\SymfonyBridge\Tests\Fixture\Sample;
use Symfony\Component\PropertyInfo\Type;

class TypeExtractorTest extends TestCase
{
    private $extractor;

    protected function setUp(): void
    {
        // the connection is lazy: these tests never reach the database
        $pomm = new Pomm([
            'test' => [
                'dsn' => 'pgsql://user:pass@localhost/pomm_test',
                'class:session_builder' => SessionBuilder::class,
            ],
        ]);

        $this->extractor = new TypeExtractor($pomm);
    }

    /**
     * @dataProvider provideTypes
     */
    public function testGetTypes($property, $builtin_type, $class)
    {
        $types = $this->extractor->getTypes(Sample::class, $property);

        $this->assertEquals([new Type($builtin_type, false, $class)], $types);
    }

    public function provideTypes()
    {
        return [
            'integer' => ['an_integer', Type::BUILTIN_TYPE_INT, null],
            'float' => ['a_float', Type::BUILTIN_TYPE_FLOAT, null],
            'string' => ['a_string', Type::BUILTIN_TYPE_STRING, null],
            'boolean' => ['a_boolean', Type::BUILTIN_TYPE_BOOL, null],
            'json' => ['a_json', Type::BUILTIN_TYPE_ARRAY, null],
            'binary' => ['a_binary', Type::BUILTIN_TYPE_STRING, null],
            'timestamp' => ['a_timestamp', Type::BUILTIN_TYPE_OBJECT, 'DateTime'],
            'interval' => ['an_interval', Type::BUILTIN_TYPE_OBJECT, 'DateInterval'],
            'point' => ['a_point', Type::BUILTIN_TYPE_OBJECT, Point::class],
            'circle' => ['a_circle', Type::BUILTIN_TYPE_OBJECT, Circle::class],
            'box' => ['a_box', Type::BUILTIN_TYPE_OBJECT, Box::class],
            'numrange' => ['a_numrange', Type::BUILTIN_TYPE_OBJECT, NumRange::class],
            'tsrange' => ['a_tsrange', Type::BUILTIN_TYPE_OBJECT, TsRange::class],
        ];
    }

    public function testGetTypesWithoutModel()
    {
        $this->assertNull($this->extractor->getTypes(\stdClass::class, 'any'));
    }
}

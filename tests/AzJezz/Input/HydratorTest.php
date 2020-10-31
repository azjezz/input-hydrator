<?php

declare(strict_types=1);

namespace AzJezz\Input\Test;

use AzJezz\Input\Exception\BadInputException;
use AzJezz\Input\Exception\TypeException;
use AzJezz\Input\Hydrator;
use PHPUnit\Framework\TestCase;

final class HydratorTest extends TestCase
{
    public function testThatItWorksAsExpected(): void
    {
        $hydrator = new Hydrator();
        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!'
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(100, $search->limit);

        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => 25
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);

        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => 0
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(0, $search->limit);

        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => null,
            'filter' => []
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertNull($search->limit);

        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => ''
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertNull($search->limit);

        $result = $hydrator->hydrate(Fixture\RecaptchaResult::class, [
            'success' => true,
            'action' => 'registration',
            'score' => 0.9
        ]);

        static::assertSame(true, $result->success);
        static::assertSame('registration', $result->action);
        static::assertSame(0.9, $result->score);

        $result = $hydrator->hydrate(Fixture\RecaptchaResult::class, [
            'success' => true,
            'action' => 'registration',
            'score' => '0.9'
        ]);

        static::assertTrue($result->success);
        static::assertSame('registration', $result->action);
        static::assertSame(0.9, $result->score);

        $result = $hydrator->hydrate(Fixture\RecaptchaResult::class, [
            'success' => '0',
            'action' => 'registration',
            'score' => '0.0'
        ]);

        static::assertFalse($result->success);
        static::assertSame('registration', $result->action);
        static::assertSame(0.0, $result->score);

        $result = $hydrator->hydrate(Fixture\RecaptchaResult::class, [
            'success' => '0',
            'action' => 'registration',
            'score' => '0'
        ]);

        static::assertFalse($result->success);
        static::assertSame('registration', $result->action);
        static::assertSame(0.0, $result->score);


        $result = $hydrator->hydrate(Fixture\RecaptchaResult::class, [
            'success' => '1',
            'action' => 'registration',
            'score' => '0.9'
        ]);

        static::assertTrue($result->success);
        static::assertSame('registration', $result->action);
        static::assertSame(0.9, $result->score);

        $result = $hydrator->hydrate(Fixture\RecaptchaResult::class, [
            'success' => '0',
            'action' => 'registration',
            'score' => '1'
        ]);

        static::assertFalse($result->success);
        static::assertSame('registration', $result->action);
        static::assertSame(1.0, $result->score);

        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => '000'
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(0, $search->limit);

        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => 25
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);

        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => '25'
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);

        $search = $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => '25',
            'filter' => [
                'maximum_price' => '100',
                'minimum_price' => '80',
            ]
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);
        static::assertNotNull($search->filter);
        static::assertSame(100, $search->filter->maximumPrice);
        static::assertSame(80, $search->filter->minimumPrice);
    }

    public function testThatItWorksAsExpectedWithUnionTypes(): void
    {
        if (PHP_VERSION_ID < 80000) {
            static::markTestSkipped('PHP ^8.0 is required.');
        }

        $hydrator = new Hydrator();
        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!'
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(100, $search->limit);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => 25
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => 0
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(0, $search->limit);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => null
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertNull($search->limit);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => ''
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertNull($search->limit);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => '000'
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(0, $search->limit);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => 25
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => '25'
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => '25',
            'filter' => [
                'maximum_price' => '100',
                'minimum_price' => '80',
            ]
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);
        static::assertInstanceOf(Fixture\Filter::class, $search->filter);
        static::assertSame(100, $search->filter->maximumPrice);
        static::assertSame(80, $search->filter->minimumPrice);

        $search = $hydrator->hydrate(Fixture\UnionTypeSearch::class, [
            'query' => 'Hello, World!',
            'limit' => '25',
            'filter' => 'maximum_price=100&minimum_price=80'
        ]);

        static::assertSame('Hello, World!', $search->query);
        static::assertSame(25, $search->limit);
        static::assertIsString($search->filter);
        static::assertSame('maximum_price=100&minimum_price=80', $search->filter);
    }

    public function testItHandlesIntegersCorrectly(): void
    {
        $hydrator = new Hydrator();

        $this->expectException(BadInputException::class);
        $this->expectExceptionMessage('field "limit" has an incorrect type of "string", "int" was expected.');

        $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => '025'
        ]);
    }

    public function testItThrowsForNonArraySubInputField(): void
    {
        $hydrator = new Hydrator();

        $this->expectException(BadInputException::class);
        $this->expectExceptionMessage(
            'field "filter" has an incorrect type of "string",' .
            ' "AzJezz\Input\Test\Fixture\Filter" was expected.',
        );

        $hydrator->hydrate(Fixture\Search::class, [
            'query' => 'Hello, World!',
            'limit' => '25',
            'filter' => 'maximum_price=100&minimum_price=80'
        ]);
    }

    public function testItThrowsForMissingRequiredField(): void
    {
        $hydrator = new Hydrator();

        $this->expectException(BadInputException::class);
        $this->expectExceptionMessage('required field "query" is missing from the request.');

        $hydrator->hydrate(Fixture\Search::class, []);
    }

    public function testThrowsForNonInputTypedProperties(): void
    {
        $hydrator = new Hydrator();

        $this->expectException(TypeException::class);
        $this->expectExceptionMessage(
            'Property "something" of "AzJezz\Input\Test\Fixture\NonInputProperty" input ' .
            'class has an unsupported type ( "stdClass" ).',
        );

        $hydrator->hydrate(Fixture\NonInputProperty::class, [
            'something' => 'hello'
        ]);
    }

    public function testThrowsForArrayType(): void
    {
        $hydrator = new Hydrator();

        $this->expectException(TypeException::class);
        $this->expectExceptionMessage(
            'Property "fields" of "AzJezz\Input\Test\Fixture\ArrayProperty" input ' .
            'class has an unsupported type ( "array" ).',
        );

        $hydrator->hydrate(Fixture\ArrayProperty::class, [
            'fields' => []
        ]);
    }

    public function testThrowsForIterableType(): void
    {
        $hydrator = new Hydrator();

        $this->expectException(TypeException::class);
        $this->expectExceptionMessage(
            'Property "fields" of "AzJezz\Input\Test\Fixture\IterableProperty" input' .
            ' class has an unsupported type ( "iterable" ).',
        );

        $hydrator->hydrate(Fixture\IterableProperty::class, [
            'fields' => []
        ]);
    }

    public function testThrowsForObjectType(): void
    {
        $hydrator = new Hydrator();

        $this->expectException(TypeException::class);
        $this->expectExceptionMessage(
            'Property "fields" of "AzJezz\Input\Test\Fixture\ObjectProperty" input' .
            ' class has an unsupported type ( "object" ).',
        );

        $hydrator->hydrate(Fixture\ObjectProperty::class, [
            'fields' => []
        ]);
    }

    public function testThrowsForUntypedProperties(): void
    {
        $hydrator = new Hydrator();

        $this->expectException(TypeException::class);
        $this->expectExceptionMessage(
            'Property "AzJezz\Input\Test\Fixture\UntypedProperty" of "something"' .
            ' input class is not typed.',
        );

        $hydrator->hydrate(Fixture\UntypedProperty::class, [
            'fields' => []
        ]);
    }
}

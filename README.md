# Input Hydrator

![Unit tests status](https://github.com/azjezz/input-hydrator/workflows/unit%20tests/badge.svg?branch=develop)
![Static analysis status](https://github.com/azjezz/input-hydrator/workflows/static%20analysis/badge.svg?branch=develop)
![Security analysis status](https://github.com/azjezz/input-hydrator/workflows/security%20analysis/badge.svg?branch=develop)
![Coding standards status](https://github.com/azjezz/input-hydrator/workflows/coding%20standards/badge.svg?branch=develop)
[![TravisCI Build Status](https://travis-ci.com/azjezz/input-hydrator.svg?branch=develop)](https://travis-ci.com/azjezz/psl)
[![Coverage Status](https://coveralls.io/repos/github/azjezz/input-hydrator/badge.svg?branch=develop)](https://coveralls.io/github/azjezz/psl?branch=develop)
[![Type Coverage](https://shepherd.dev/github/azjezz/input-hydrator/coverage.svg)](https://shepherd.dev/github/azjezz/psl)
[![Total Downloads](https://poser.pugx.org/azjezz/input-hydrator/d/total.svg)](https://packagist.org/packages/azjezz/psl)
[![Latest Stable Version](https://poser.pugx.org/azjezz/input-hydrator/v/stable.svg)](https://packagist.org/packages/azjezz/psl)
[![License](https://poser.pugx.org/azjezz/input-hydrator/license.svg)](https://packagist.org/packages/azjezz/psl)

Input hydrator is a simple hydrator made for the sole purpose of hydrating data-transfer input objects.


## Installation

```console
$ composer require azjezz/input-hydrator
```


## Example:

```php
use AzJezz\Input;

final class Search implements Input\InputInterface
{
    public string $query;
}

/**
 * @var Search $search
 */
$search = (new Input\Hydrator())->hydrate(Search::class, $_GET);

print $search->query;
```

While hydrating objects, some exceptions might be thrown:
    - `AzJezz\Input\Exception\TypeException`, this exception should result in 500 HTTP status code,
    as it represents an issue within the input class itself. such as the usage of a non-supported type,
    or missing type for a specific property.
    - `AzJezz\Input\Exception\BadInputException`, this exception should result in a 400 HTTP status code,
    as it means that the supplied request data doesn't match the input DTO structure.
    
Currently, InputHydrator is limited to a small set of types:
    - `scalar` ( `string`, `int`, `float`, and `bool` )
    - `null`
    - *any object that implements `AzJezz\Input\InputInterface`*
    
Union types are supported as well for PHP >= 8.0, for example:

```php
use AzJezz\Input;

final class Filter implements Input\InputInterface
{
    public ?int $maximumPrice;
    public ?int $minimumPrice;
}

final class Search implements Input\InputInterface
{
    public string $query;
    public null|Filter|string $filter = null;
}

/**
 * $filter is optional, and is missing from the request, therfore it's gonna contain the default value.
 *
 * @var Search $search
 */
$search = (new Input\Hydrator())->hydrate(Search::class, [
  'query' => 'hello'
]);

/**
 * $search->filter is now an instance of `Filter`
 *
 * @var Search $search
 */
$search = (new Input\Hydrator())->hydrate(Search::class, [
  'query' => 'hello',
  'filter' => [
    'maximum_price' => 1000,
    'minimum_price' => 10, // the field is optional ( nullable ), so we can remove this line.
  ]
]);

/**
 * $search->filter is now an instance of `Filter`
 *
 * @var Search $search
 */
$search = (new Input\Hydrator())->hydrate(Search::class, [
  'query' => 'hello',
   // this is okay as the `null|Filter|string` union contains `string`
  'filter' => 'maximum_price=1000&minimum_price=10',
]);

print $search->query;
```

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
<?php

declare(strict_types=1);

namespace AzJezz\Input;

use AzJezz\Input\Exception\BadInputException;
use AzJezz\Input\Exception\TypeException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

final class Hydrator implements HydratorInterface
{
    /**
     * Map the given request to the input data-transfer object.
     *
     * @psalm-template T of InputInterface
     *
     * @psalm-param class-string<T>         $input_class
     * @psalm-param array<array-key, mixed> $request
     *
     * @psalm-return T
     *
     * @throws BadInputException If unable to construct the input class from the given request data.
     * @throws TypeException     If the input class contains a property that is either untyped,
     *                           or of a non-supported type.
     */
    public function hydrate(string $input_class, array $request): InputInterface
    {
        $reflection = new ReflectionClass($input_class);
        /**
         * @var InputInterface $instance
         * @psalm-var T $instance
         */
        $instance = $reflection->newInstanceWithoutConstructor();

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $properties = array_filter(
            $properties,
            static fn(ReflectionProperty $property): bool => !$property->isStatic(),
        );

        foreach ($properties as $property) {
            $value = $this->getFieldValue($instance, $request, $property);

            $property->setValue($instance, $value);
        }

        return $instance;
    }

    private function getFieldName(ReflectionProperty $property): string
    {
        $value = trim($property->getName());
        $value = preg_replace('/[^a-zA-Z0-9_]/', '_', $value);
        $value = preg_replace('/(?<=\\w)([A-Z])/', '_$1', $value);
        $value = preg_replace('/_{2,}/', '_', $value);
        return strtolower($value);
    }

    /**
     * @throws TypeException     If the input class contains a property that is untyped.
     */
    private function getFieldType(ReflectionProperty $property): ReflectionType
    {
        $type = $property->getType();
        if (null === $type) {
            throw TypeException::forMissingPropertyType(
                $property->getDeclaringClass()->getName(),
                $property->getName(),
            );
        }

        return $type;
    }

    /**
     * @psalm-param array<array-key, mixed> $request
     *
     * @psalm-return InputInterface|scalar|null
     *
     * @throws BadInputException If unable to construct the input class from the given request data.
     * @throws TypeException     If the input class contains a property that is either untyped,
     *                           or of a non-supported type.
     */
    private function getFieldValue(InputInterface $input, array $request, ReflectionProperty $property)
    {
        $field_name = $this->getFieldName($property);
        $field_type_reflection = $this->getFieldType($property);

        /**
         * Check if the field exists in the request data.
         */
        if (!array_key_exists($field_name, $request)) {
            /**
             * In case we don't have a value, check if the property has a default value, which we can use.
             */
            if ($property->isDefault() && $property->isInitialized($input)) {
                /** @psalm-var scalar $value */
                $value = $property->getValue($input);

                return $value;
            }

            /**
             * Otherwise, Check if the property allows null values, in which case,
             * we can return null ( the field is optional ).
             */
            if ($field_type_reflection->allowsNull()) {
                return null;
            }

            // The field is not optional, doesn't have a default value, and is missing from the request.
            // This means that we have a bad request in our hands.
            throw BadInputException::createForMissingField($field_name);
        }

        /** @var mixed $field_value */
        $field_value = $request[$field_name];
        // @codeCoverageIgnoreStart
        if (class_exists(ReflectionUnionType::class) && $field_type_reflection instanceof ReflectionUnionType) {
            /** @var list<ReflectionType|ReflectionNamedType|ReflectionUnionType> $inner_types */
            $inner_types = $field_type_reflection->getTypes();
            foreach ($inner_types as $inner_type) {
                try {
                    return $this->coerceType($property, $field_name, $field_value, $inner_type);
                } catch (TypeException $exception) {
                    throw $exception;
                } catch (BadInputException $exception) {
                }
            }

            throw BadInputException::createForInvalidFieldTypeFromValue(
                $field_name,
                (string)$field_type_reflection,
                $field_value,
            );
        }
        // @codeCoverageIgnoreEnd

        // Now that we know the field exists, let's assert it's type.
        return $this->coerceType($property, $field_name, $field_value, $field_type_reflection);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     * @psalm-return InputInterface|scalar|null
     *
     * @throws BadInputException If unable to construct the input class from the given request data.
     * @throws TypeException     If the input class contains a property that is of a non-supported type.
     */
    private function coerceType(ReflectionProperty $property, string $name, $value, ReflectionType $type)
    {
        $type_as_string = $type instanceof ReflectionNamedType ? $type->getName() : (string)$type;
        if (class_exists($type_as_string)) {
            // Check if the type is a subclass of InputInterface
            if (is_subclass_of($type_as_string, InputInterface::class)) {
                // If the type is a subclass of input, we need to ensure that the value is an array.
                if (!is_array($value)) {
                    // otherwise we throw a bad request exception
                    throw BadInputException::createForInvalidFieldTypeFromValue($name, $type_as_string, $value);
                }

                /**
                 * Since the value is an array, and the type is an input, we can try to map it.
                 *
                 * @var InputInterface
                 */
                return $this->hydrate($type_as_string, $value);
            }

            throw TypeException::forUnsupportedPropertyType(
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                $type_as_string,
            );
        }

        if ('array' === $type_as_string || 'iterable' === $type_as_string || 'object' === $type_as_string) {
            /**
             * we don't support array type as PHP doesn't support generics.
             */
            throw TypeException::forUnsupportedPropertyType(
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                $type_as_string
            );
        }

        if ('string' === $type_as_string) {
            if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
                return (string) $value;
            }
        }

        if ('int' === $type_as_string) {
            if (is_int($value)) {
                return $value;
            }

            if (is_string($value)) {
                $trimmed_value = ltrim($value, '0');
                $integer_value = (int)$trimmed_value;
                if (((string)$integer_value) === $value) {
                    return $integer_value;
                }

                if ('' === $trimmed_value && '' !== $value) {
                    return 0;
                }
            }
        }

        if ('float' === $type_as_string) {
            if (is_float($value) || is_int($value)) {
                return (float)$value;
            }

            if (is_string($value) && '' !== $value) {
                if (ctype_digit($value)) {
                    return (float)$value;
                }

                if (1 === preg_match("/^-?(?:\\d*\\.)?\\d+(?:[eE]\\d+)?$/", $value)) {
                    return (float)$value;
                }
            }
        }

        if ('bool' === $type_as_string) {
            if (is_bool($value)) {
                return $value;
            }

            if ('1' === $value || 1 === $value) {
                return true;
            }

            if ('0' === $value || 0 === $value) {
                return false;
            }
        }

        if ($type->allowsNull()) {
            if ('' === $value || null === $value) {
                return null;
            }
        }

        throw BadInputException::createForInvalidFieldTypeFromValue($name, $type_as_string, $value);
    }
}

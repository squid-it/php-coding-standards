---
name: phpdoc
description: 'PHPDoc rules for array types, `@return`, and `@throws`. Read when writing or changing PHP method signatures or docblocks.'
---

# PHPDoc Requirements

These rules supplement the company-wide `AGENTS.md`.

When writing or modifying PHP code, follow these documentation standards:

## Array Parameters - Always Include

- **`@param array<int, string> $items`** - for indexed arrays of strings
- **`@param array<string, mixed> $config`** - for associative arrays with mixed values
- **`@param array<int, ClassName> $objects`** - for arrays of objects
- **`@param array<string, array<string, int>> $nested`** - for nested array structures

## Return Types - Only for Arrays

- Include **`@return`** PHPDoc only when the method returns an array
- **`@return array<string, int>`** - specify the array structure
- Omit **`@return`** for other types (rely on the method signature return type declaration)

## Other Documentation

- **`@throws ExceptionName`** - for every exception or throwable that can escape the method
- Do not add an `@throws` tag for an exception that is caught and cannot escape the method
- **`@param`** descriptions for complex parameters when helpful
- **`@throws Throwable`** - for PHPUnit tests from which a throwable can escape
- Method descriptions when behavior is not obvious from the name

## Examples

```php
/**
 * @param array<int, string> $items Input items to process
 *
 * @throws InvalidArgumentException When items array is empty
 *
 * @return array<string, int> Processed items with counts
 */
private function processItems(array $items): array

/**
 * @param array<string, mixed> $config Configuration options
 *
 * @throws ConfigurationException When config is invalid
 */
private function validate(array $config): ValidationResult
```

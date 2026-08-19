# Aegisora Is Array Rule Guardian

[![Latest Version](https://img.shields.io/packagist/v/aegisora/is-array-rule-guardian?style=flat-square)](https://packagist.org/packages/aegisora/is-array-rule-guardian)
[![Total Downloads](https://img.shields.io/packagist/dt/aegisora/is-array-rule-guardian?style=flat-square)](https://packagist.org/packages/aegisora/is-array-rule-guardian)
![Code Coverage Badge](./badge.svg)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
![PHPStan Badge](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat)

Is Array Rule Guardian provides a simple shortcut for array validation using `aegisora/guardian` and `aegisora/is-array-rule`.

It is designed for cases where you want to quickly check whether a value **is an array**, without manually building an `IsArrayRule` and a validation pipeline by hand.

This package is built on top of:

* [aegisora/guardian](https://github.com/Aegisora/guardian)
* [aegisora/is-array-rule](https://github.com/Aegisora/is-array-rule)

---

## ✨ Features

* 🔹 Simple shortcut API for `IsArrayRule`
* 🔹 Validates that a value is an array via `check()`
* 🔹 Works with both empty and non-empty arrays
* 🔹 Uses `aegisora/guardian` internally
* 🔹 Uses `aegisora/is-array-rule` internally
* 🔹 Supports a custom validation exception
* 🔹 Keeps rule execution errors separated from validation errors
* 🔹 Fully compatible with the Aegisora ecosystem
* 🔹 Ready to use out of the box

---

## 📦 Installation

```bash
composer require aegisora/is-array-rule-guardian
```

---

## 🚀 Core Concept

This package wraps the common array validation flow:

```php
$guardian->check(
    $value,
    IsArrayRule::create(),
    new ValueIsNotArrayException()
);
```

into a dedicated shortcut class:

```php
$isArrayRuleGuardian->check($value, new ValueIsNotArrayException());
```

Instead of manually creating an `IsArrayRule` and passing it to `Guardian`, you can use `IsArrayRuleGuardian` directly.

---

## 🏗️ Basic Usage

```php
use Aegisora\Guardian\Guardian;
use Aegisora\Guardian\Exceptions\GuardianValidationException;
use Aegisora\RuleGuardians\IsArrayRule\IsArrayRuleGuardian;

$guardian = new Guardian();

$isArrayRuleGuardian = new IsArrayRuleGuardian($guardian);

try {
    $isArrayRuleGuardian->check($value);
    // $value is an array
} catch (GuardianValidationException $exception) {
    // $value is not an array
}
```

`check()` **passes when** `$value` is an array, and **fails otherwise**.

---

## ✅ How the array check works

A value is considered valid when it is an array, regardless of whether it is empty or not:

```php
$isArrayRuleGuardian->check([]);              // passes (empty array)
$isArrayRuleGuardian->check([1]);             // passes (non-empty array)

$isArrayRuleGuardian->check(1);               // fails (int)
$isArrayRuleGuardian->check(1.1);             // fails (float)
$isArrayRuleGuardian->check('');              // fails (string)
$isArrayRuleGuardian->check(new stdClass());  // fails (object)
$isArrayRuleGuardian->check(tmpfile());       // fails (resource)
$isArrayRuleGuardian->check(static fn () => null); // fails (callable)
```

---

## 🧩 Usage with Custom Exception

You may provide your own exception for validation failure. It must be the **last** argument.

```php
use Aegisora\Guardian\Guardian;
use Aegisora\RuleGuardians\IsArrayRule\IsArrayRuleGuardian;
use App\Exceptions\ValueIsNotArrayException;

$guardian = new Guardian();

$isArrayRuleGuardian = new IsArrayRuleGuardian($guardian);

$isArrayRuleGuardian->check(
    $value,
    new ValueIsNotArrayException()
);
```

If the value is not an array, the provided exception will be thrown instead of `GuardianValidationException`.

This is useful when validation errors should have domain-specific meaning.

---

## 🧪 Example in Application Service

```php
use Aegisora\RuleGuardians\IsArrayRule\IsArrayRuleGuardian;
use App\Exceptions\InvalidPayloadException;

final class PayloadProcessor
{
    private IsArrayRuleGuardian $isArrayRuleGuardian;

    public function __construct(
        IsArrayRuleGuardian $isArrayRuleGuardian
    ) {
        $this->isArrayRuleGuardian = $isArrayRuleGuardian;
    }

    /**
     * @param mixed $payload
     */
    public function process($payload): void
    {
        $this->isArrayRuleGuardian->check(
            $payload,
            new InvalidPayloadException()
        );

        // business logic for processing an array payload
    }
}
```

---

## 🚨 Exceptions

The package raises validation-related exceptions, all delegated to `Guardian` (the outcome of running the rule):

### `GuardianValidationException`

Thrown when validation fails and no custom exception is provided.

The rule code for a failed array check is `is_array_rule`.

```php
use Aegisora\Guardian\Exceptions\GuardianValidationException;

try {
    $isArrayRuleGuardian->check($value);
} catch (GuardianValidationException $exception) {
    echo $exception->getRuleCode(); // "is_array_rule"
}
```

### Custom exception

When a custom exception is passed as the last argument, it is thrown instead of `GuardianValidationException` on validation failure.

```php
use App\Exceptions\ValueIsNotArrayException;

try {
    $isArrayRuleGuardian->check($value, new ValueIsNotArrayException());
} catch (ValueIsNotArrayException $exception) {
    // domain-specific handling
}
```

### `GuardianExecutingRuleException`

Thrown when the underlying rule fails to execute (raises a `RuleException` during validation), as opposed to simply reporting an invalid result.

The array check accepts any value type and reports non-array values as an invalid result, so this exception is not triggered by the input itself — it is surfaced only if `Guardian` fails to execute the rule.

```php
use Aegisora\Guardian\Exceptions\GuardianExecutingRuleException;

try {
    $isArrayRuleGuardian->check($value);
} catch (GuardianExecutingRuleException $exception) {
    // the rule could not be executed
}
```

---

## 🧩 API

### `IsArrayRuleGuardian::check()`

```php
/**
 * @param mixed $value
 * @throws GuardianExecutingRuleException
 * @throws GuardianValidationException
 * @throws \Throwable
 */
public function check($value, ?\Throwable $exception = null): void
```

Validates that `$value` is an **array**.

Arguments:

* `$value` — the value to validate
* `$exception` — an optional custom `\Throwable` to be thrown on validation failure

The method returns `void`. It communicates results through exceptions only — it returns nothing on success and throws on failure:

* `GuardianValidationException` — the array check failed and no custom exception was provided
* the provided custom exception — the check failed and a custom exception was passed
* `GuardianExecutingRuleException` — the rule could not be executed

---

## 🏛️ Architecture

This package is a small shortcut layer over the Aegisora validation pipeline.

Flow:

1. `IsArrayRuleGuardian::check()` is called with a value and an optional exception
2. An `IsArrayRule` is created (`create()`)
3. `Guardian` executes the rule against the value
4. If the check passes, execution continues normally
5. If the check fails, the custom exception or `GuardianValidationException` is thrown
6. If the rule could not be executed, `GuardianExecutingRuleException` is thrown

Internal flow:

```
value → IsArrayRuleGuardian → Guardian → IsArrayRule → Result → Exception
```

---

## 🔗 Related Packages

* [aegisora/guardian](https://github.com/Aegisora/guardian) — validation execution orchestrator
* [aegisora/is-array-rule](https://github.com/Aegisora/is-array-rule) — is array rule
* [aegisora/rule-contract](https://github.com/Aegisora/rule-contract) — base rule contract and validation result architecture

---

## ⚖️ License

This package is open-source and licensed under the MIT License. See the LICENSE for details.

---

## 🌱 Contributing

Contributions are welcome and greatly appreciated!. See the CONTRIBUTING for details.

---

## 🌟 Support

If you find this project useful, please consider giving it a star on GitHub!

It helps the project grow and motivates further development.

<?php

namespace Aegisora\RuleGuardians\IsArrayRule\Tests\Unit;

use Aegisora\Guardian\Exceptions\GuardianExecutingRuleException;
use Aegisora\Guardian\Exceptions\GuardianValidationException;
use Aegisora\Guardian\Guardian;
use Aegisora\RuleGuardians\IsArrayRule\IsArrayRuleGuardian;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

class IsArrayRuleGuardianTest extends TestCase
{
    private const RULE_CODE = 'is_array_rule';

    private IsArrayRuleGuardian $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardian = new IsArrayRuleGuardian(
            new Guardian()
        );
    }

    /**
     * @dataProvider getArrayValuesProvidedData
     * @param mixed $value
     */
    public function testSuccessfullyCheck(
        $value
    ): void {
        $this->expectNotToPerformAssertions();

        $this->guardian->check($value);
    }

    public static function getArrayValuesProvidedData(): array
    {
        return [
            'value - empty array' => [
                'value' => [],
            ],
            'value - not empty array' => [
                'value' => [1,],
            ],
        ];
    }

    /**
     * @dataProvider getNotArrayValuesProvidedData
     * @param mixed $value
     */
    public function testFailedCheckWithDefaultCustomException(
        $value
    ): void {
        $this->expectException(GuardianValidationException::class);

        try {
            $this->guardian->check($value);
        } catch (GuardianValidationException $exception) {
            self::assertSame(self::RULE_CODE, $exception->getRuleCode());

            throw $exception;
        }
    }

    public static function getNotArrayValuesProvidedData(): array
    {
        return [
            'value - integer' => [
                'value' => 1,
            ],
            'value - float' => [
                'value' => 1.1,
            ],
            'value - string' => [
                'value' => '',
            ],
            'value - object' => [
                'value' => new stdClass(),
            ],
            'value - resource' => [
                'value' => tmpfile(),
            ],
            'value - callable' => [
                'value' => static function () {
                },
            ],
        ];
    }

    /**
     * @dataProvider getFailedCheckProvidedData
     * @param mixed $value
     */
    public function testFailedCheck(
        $value,
        ?Throwable $customRuleValidationException,
        string $expectedExceptionClassName
    ): void {
        $this->expectException($expectedExceptionClassName);

        $this->guardian->check($value, $customRuleValidationException);
    }

    public static function getFailedCheckProvidedData(): array
    {
        return [
            'value - integer, custom exception - null' => [
                'value' => 1,
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - integer, custom exception - not null' => [
                'value' => 1,
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - float, custom exception - null' => [
                'value' => 1.1,
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - float, custom exception - not null' => [
                'value' => 1.1,
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - string, custom exception - null' => [
                'value' => '',
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - string, custom exception - not null' => [
                'value' => '',
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - object, custom exception - null' => [
                'value' => new stdClass(),
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - object, custom exception - not null' => [
                'value' => new stdClass(),
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - resource, custom exception - null' => [
                'value' => tmpfile(),
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - resource, custom exception - not null' => [
                'value' => tmpfile(),
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'value - callable, custom exception - null' => [
                'value' => static function () {
                },
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'value - callable, custom exception - not null' => [
                'value' => static function () {
                },
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
        ];
    }

    public function testFailedCheckCauseGuardianThrowsGuardianExecutingRuleException(): void
    {
        $this->expectException(GuardianExecutingRuleException::class);

        $guardian = new IsArrayRuleGuardian(
            $this->getGuardianThrowsExceptionOnCheck(GuardianExecutingRuleException::class)
        );

        $guardian->check(null);
    }

    public function testFailedCheckCauseGuardianThrowsNotExpectedException(): void
    {
        $this->expectException(Throwable::class);

        $guardian = new IsArrayRuleGuardian(
            $this->getGuardianThrowsExceptionOnCheck(Throwable::class)
        );

        $guardian->check(null);
    }

    /**
     * @return Guardian|MockObject
     */
    private function getGuardianThrowsExceptionOnCheck(string $expectedExceptionClass): Guardian
    {
        $guardian = $this->getGuardianMock();

        $guardian
            ->expects(self::once())
            ->method('check')
            ->willThrowException($this->createMock($expectedExceptionClass));

        return $guardian;
    }

    /**
     * @return Guardian|MockObject
     */
    private function getGuardianMock(): Guardian
    {
        /** @var Guardian|MockObject $mock */
        $mock = $this->createMock(Guardian::class);

        return $mock;
    }
}

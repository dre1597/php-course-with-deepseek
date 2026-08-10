<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/FormValidator.php';

class FormValidatorTest extends TestCase
{
    private array $validData;

    protected function setUp(): void
    {
        $this->validData = [
            'name'     => 'Maria',
            'email'    => 'maria@example.com',
            'password' => '12345678',
            'bio'      => 'Developer from Rio',
        ];
    }

    public function testConstructorAcceptsDataArray(): void
    {
        $validator = new FormValidator($this->validData);

        $this->assertInstanceOf(FormValidator::class, $validator);
    }

    public function testConstructorAcceptsEmptyArray(): void
    {
        $validator = new FormValidator([]);

        $this->assertInstanceOf(FormValidator::class, $validator);
    }

    public function testPassesReturnsTrueByDefault(): void
    {
        $validator = new FormValidator($this->validData);

        $this->assertTrue($validator->passes());
    }

    public function testFailsReturnsFalseByDefault(): void
    {
        $validator = new FormValidator($this->validData);

        $this->assertFalse($validator->fails());
    }

    public function testGetErrorsReturnsEmptyArrayByDefault(): void
    {
        $validator = new FormValidator($this->validData);

        $this->assertSame([], $validator->getErrors());
    }

    public function testRequiredPassesWhenFieldPresentAndNonEmpty(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->required('name');

        $this->assertTrue($validator->passes());
    }

    public function testRequiredFailsWhenFieldMissing(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->required('age');

        $this->assertTrue($validator->fails());
    }

    public function testRequiredFailsWhenFieldIsEmptyString(): void
    {
        $validator = new FormValidator(['username' => '']);
        $validator->required('username');

        $this->assertTrue($validator->fails());
    }

    public function testRequiredAcceptsZeroAsString(): void
    {
        $validator = new FormValidator(['score' => '0']);
        $validator->required('score');

        $this->assertTrue($validator->passes());
    }

    public function testRequiredReturnsSelfForChaining(): void
    {
        $validator = new FormValidator($this->validData);

        $result = $validator->required('name');

        $this->assertSame($validator, $result);
    }

    public function testMinLengthPassesWhenStringMeetsMinimum(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->minLength('password', 8);

        $this->assertTrue($validator->passes());
    }

    public function testMinLengthFailsWhenStringIsTooShort(): void
    {
        $validator = new FormValidator(['username' => 'ab']);
        $validator->minLength('username', 3);

        $this->assertTrue($validator->fails());
    }

    public function testMinLengthPassesWhenFieldNotPresent(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->minLength('missing', 3);

        $this->assertTrue($validator->passes());
    }

    public function testMinLengthReturnsSelfForChaining(): void
    {
        $validator = new FormValidator($this->validData);

        $result = $validator->minLength('password', 8);

        $this->assertSame($validator, $result);
    }

    public function testMinLengthPassesAtExactBoundary(): void
    {
        $validator = new FormValidator(['code' => 'abc']);
        $validator->minLength('code', 3);

        $this->assertTrue($validator->passes());
    }

    public function testMaxLengthPassesWhenStringIsUnderLimit(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->maxLength('name', 10);

        $this->assertTrue($validator->passes());
    }

    public function testMaxLengthPassesWhenStringEqualsLimit(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->maxLength('name', 5);

        $this->assertTrue($validator->passes());
    }

    public function testMaxLengthFailsWhenStringExceedsLimit(): void
    {
        $validator = new FormValidator(['bio' => 'Esta bio é longa demais']);
        $validator->maxLength('bio', 10);

        $this->assertTrue($validator->fails());
    }

    public function testMaxLengthPassesWhenFieldNotPresent(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->maxLength('missing', 10);

        $this->assertTrue($validator->passes());
    }

    public function testMaxLengthReturnsSelfForChaining(): void
    {
        $validator = new FormValidator($this->validData);

        $result = $validator->maxLength('name', 10);

        $this->assertSame($validator, $result);
    }

    public function testEmailPassesWithValidEmail(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->email('email');

        $this->assertTrue($validator->passes());
    }

    public function testEmailFailsWithInvalidEmail(): void
    {
        $validator = new FormValidator(['contact' => 'not-an-email']);
        $validator->email('contact');

        $this->assertTrue($validator->fails());
    }

    public function testEmailPassesWhenFieldNotPresent(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->email('missing');

        $this->assertTrue($validator->passes());
    }

    public function testEmailReturnsSelfForChaining(): void
    {
        $validator = new FormValidator($this->validData);

        $result = $validator->email('email');

        $this->assertSame($validator, $result);
    }

    public function testChainedRulesOnSameField(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->required('name')->minLength('name', 3);

        $this->assertTrue($validator->passes());
    }

    public function testChainedRulesOnDifferentFields(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->required('name')
                   ->required('email')
                   ->required('password');

        $this->assertTrue($validator->passes());
    }

    public function testMultipleRulesAccumulateErrors(): void
    {
        $validator = new FormValidator(['username' => 'ab', 'contact' => 'bad']);
        $validator->required('username')
                   ->minLength('username', 5)
                   ->required('contact')
                   ->email('contact');

        $errors = $validator->getErrors();

        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('username', $errors);
        $this->assertArrayHasKey('contact', $errors);
    }

    public function testMultipleErrorsOnSameFieldAreAllPreserved(): void
    {
        $validator = new FormValidator(['username' => 'ab']);
        $validator->required('username')
                   ->minLength('username', 5)
                   ->maxLength('username', 1)
                   ->email('username');

        $errors = $validator->getErrors();

        $this->assertArrayHasKey('username', $errors);
        $this->assertIsArray($errors['username']);
        $this->assertGreaterThanOrEqual(2, count($errors['username']));
    }

    public function testGetErrorsContainsMessagesForEachField(): void
    {
        $validator = new FormValidator(['username' => '']);
        $validator->required('username')->minLength('username', 3);

        $errors = $validator->getErrors();

        $this->assertArrayHasKey('username', $errors);
        $this->assertNotEmpty($errors['username']);
    }

    public function testGetErrorsReturnsMessagesAsStrings(): void
    {
        $validator = new FormValidator(['username' => '']);
        $validator->required('username');

        $errors = $validator->getErrors();

        $this->assertIsString($errors['username'][0]);
    }

    public function testPassesReturnsFalseWhenHasErrors(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->required('age');

        $this->assertFalse($validator->passes());
    }

    public function testFailsReturnsTrueWhenHasErrors(): void
    {
        $validator = new FormValidator($this->validData);
        $validator->required('age');

        $this->assertTrue($validator->fails());
    }
}

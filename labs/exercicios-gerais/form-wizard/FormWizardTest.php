<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/FormWizard.php';

class FormWizardTest extends TestCase
{
    public function testGetStepDefaultsToOne(): void
    {
        $this->assertSame(1, FormWizard::getStep([]));
    }

    public function testGetStepReturnsPostedStep(): void
    {
        $this->assertSame(2, FormWizard::getStep(['_step' => '2']));
    }

    public function testGetStepClampsZeroToOne(): void
    {
        $this->assertSame(1, FormWizard::getStep(['_step' => '0']));
    }

    public function testGetStepClampsOverFourToOne(): void
    {
        $this->assertSame(1, FormWizard::getStep(['_step' => '5']));
    }

    public function testGetStepAllowsStepFour(): void
    {
        $this->assertSame(4, FormWizard::getStep(['_step' => '4']));
    }

    public function testFieldReturnsValueWhenPresent(): void
    {
        $this->assertSame('Maria', FormWizard::field(['name' => 'Maria'], 'name'));
    }

    public function testFieldReturnsDefaultWhenMissing(): void
    {
        $this->assertSame('', FormWizard::field([], 'name'));
    }

    public function testFieldReturnsCustomDefault(): void
    {
        $this->assertSame('N/A', FormWizard::field([], 'city', 'N/A'));
    }

    public function testAllFieldsReturnsAllSevenFields(): void
    {
        $post = ['name' => 'Ana', 'email' => 'ana@test.com'];

        $data = FormWizard::allFields($post);

        $this->assertCount(7, $data);
        $this->assertSame('Ana', $data['name']);
        $this->assertSame('ana@test.com', $data['email']);
        $this->assertSame('', $data['street']);
    }

    public function testValidateStepOneFailsWhenAllEmpty(): void
    {
        $errors = FormWizard::validateStep(1, []);

        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('birth_date', $errors);
    }

    public function testValidateStepOnePassesWithValidData(): void
    {
        $post = [
            'name'       => 'João',
            'email'      => 'joao@example.com',
            'birth_date' => '1990-01-01',
        ];

        $errors = FormWizard::validateStep(1, $post);

        $this->assertEmpty($errors);
    }

    public function testValidateStepOneFailsOnInvalidEmail(): void
    {
        $post = [
            'name'       => 'João',
            'email'      => 'nao-é-email',
            'birth_date' => '1990-01-01',
        ];

        $errors = FormWizard::validateStep(1, $post);

        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayNotHasKey('name', $errors);
    }

    public function testValidateStepTwoFailsWhenAllEmpty(): void
    {
        $errors = FormWizard::validateStep(2, []);

        $this->assertArrayHasKey('street', $errors);
        $this->assertArrayHasKey('number', $errors);
        $this->assertArrayHasKey('city', $errors);
        $this->assertArrayHasKey('state', $errors);
    }

    public function testValidateStepTwoPassesWithValidData(): void
    {
        $post = [
            'street' => 'Rua A',
            'number' => '123',
            'city'   => 'Rio de Janeiro',
            'state'  => 'RJ',
        ];

        $errors = FormWizard::validateStep(2, $post);

        $this->assertEmpty($errors);
    }

    public function testValidateUnknownStepReturnsNoErrors(): void
    {
        $errors = FormWizard::validateStep(3, []);
        $this->assertEmpty($errors);

        $errors = FormWizard::validateStep(4, []);
        $this->assertEmpty($errors);
    }

    public function testRenderHiddenFieldsAtStepOneReturnsEmpty(): void
    {
        $html = FormWizard::renderHiddenFields([], 1);

        $this->assertSame('', $html);
    }

    public function testRenderHiddenFieldsAtStepTwoIncludesPersonalFields(): void
    {
        $post = ['name' => 'Ana', 'email' => 'ana@test.com', 'birth_date' => '2000-05-10'];

        $html = FormWizard::renderHiddenFields($post, 2);

        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('value="Ana"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="birth_date"', $html);
        $this->assertStringNotContainsString('name="street"', $html);
    }

    public function testRenderHiddenFieldsAtStepThreeIncludesAllFields(): void
    {
        $post = [
            'name'       => 'Ana',
            'email'      => 'ana@test.com',
            'birth_date' => '2000-05-10',
            'street'     => 'Rua B',
            'number'     => '456',
            'city'       => 'SP',
            'state'      => 'SP',
        ];

        $html = FormWizard::renderHiddenFields($post, 3);

        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="street"', $html);
        $this->assertStringContainsString('name="state"', $html);
    }

    public function testRenderHiddenFieldsEscapesValues(): void
    {
        $post = ['name' => '<script>alert(1)</script>', 'email' => 'x@x.com', 'birth_date' => '2000-01-01'];

        $html = FormWizard::renderHiddenFields($post, 2);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testRenderHiddenFieldsHandlesMissingFields(): void
    {
        $html = FormWizard::renderHiddenFields([], 2);

        $this->assertStringContainsString('name="name" value=""', $html);
        $this->assertStringContainsString('name="email" value=""', $html);
        $this->assertStringContainsString('name="birth_date" value=""', $html);
    }
}

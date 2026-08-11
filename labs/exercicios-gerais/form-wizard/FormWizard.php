<?php

class FormWizard
{
    private const array PERSONAL_FIELDS = ['name', 'email', 'birth_date'];
    private const array ADDRESS_FIELDS = ['street', 'number', 'city', 'state'];
    private const array ALL_FIELDS = ['name', 'email', 'birth_date', 'street', 'number', 'city', 'state'];

    public static function getStep(array $post): int
    {
        $step = (int)($post['_step'] ?? 1);

        if ($step < 1 || $step > 4) {
            return 1;
        }

        return $step;
    }

    public static function field(array $post, string $field, string $default = ''): string
    {
        return $post[$field] ?? $default;
    }

    public static function allFields(array $post): array
    {
        $data = [];
        foreach (self::ALL_FIELDS as $field) {
            $data[$field] = self::field($post, $field);
        }

        return $data;
    }

    public static function validateStep(int $step, array $post): array
    {
        $errors = [];

        if ($step === 1) {
            $errors = self::validateFirstStep($post, $errors);
        }

        if ($step === 2) {
            $errors = self::validateSecondStep($post, $errors);
        }

        return $errors;
    }

    public static function renderHiddenFields(array $post, int $currentStep): string
    {
        $html = '';

        if ($currentStep >= 2) {
            foreach (self::PERSONAL_FIELDS as $field) {
                $value = htmlspecialchars(self::field($post, $field), ENT_QUOTES, 'UTF-8');
                $html .= '<input type="hidden" name="' . $field . '" value="' . $value . '">' . "\n";
            }
        }

        if ($currentStep >= 3) {
            foreach (self::ADDRESS_FIELDS as $field) {
                $value = htmlspecialchars(self::field($post, $field), ENT_QUOTES, 'UTF-8');
                $html .= '<input type="hidden" name="' . $field . '" value="' . $value . '">' . "\n";
            }
        }

        return $html;
    }

    public static function validateFirstStep(array $post, array $errors): array
    {
        $name = self::field($post, 'name');
        if ($name === '') {
            $errors['name'] = 'O nome é obrigatório.';
        }

        $email = self::field($post, 'email');
        if ($email === '') {
            $errors['email'] = 'O e-mail é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }

        $birth = self::field($post, 'birth_date');
        if ($birth === '') {
            $errors['birth_date'] = 'A data de nascimento é obrigatória.';
        }
        return $errors;
    }

    public static function validateSecondStep(array $post, array $errors): array
    {
        $street = self::field($post, 'street');
        if ($street === '') {
            $errors['street'] = 'A rua é obrigatória.';
        }

        $number = self::field($post, 'number');
        if ($number === '') {
            $errors['number'] = 'O número é obrigatório.';
        }

        $city = self::field($post, 'city');
        if ($city === '') {
            $errors['city'] = 'A cidade é obrigatória.';
        }

        $state = self::field($post, 'state');
        if ($state === '') {
            $errors['state'] = 'O estado é obrigatório.';
        }
        return $errors;
    }
}

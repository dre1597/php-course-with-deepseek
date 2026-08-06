<?php
class FormValidator {
    private array $errors = [];

    public function validate(array $data, array $rules): array {
        $cleanData = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';

            // Required field
            if (in_array('required', $fieldRules) && trim((string) $value) === '') {
                $this->errors[$field] = "The field '{$field}' is required.";
                continue;
            }

            // Default sanitization: strip tags and extra spaces
            $value = trim(strip_tags((string) $value));

            // Email
            if (in_array('email', $fieldRules) && $value !== '') {
                $validatedEmail = filter_var($value, FILTER_VALIDATE_EMAIL);
                if ($validatedEmail === false) {
                    $this->errors[$field] = "Invalid email.";
                } else {
                    $value = $validatedEmail;
                }
            }

            // Integer
            if (in_array('int', $fieldRules)) {
                $int = filter_var($value, FILTER_VALIDATE_INT);
                if ($int === false) {
                    $this->errors[$field] = "Must be an integer.";
                } else {
                    $value = $int;
                }
            }

            // Minimum length
            foreach ($fieldRules as $rule) {
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (strlen((string) $value) < $min) {
                        $this->errors[$field] = "Must be at least {$min} characters.";
                    }
                }
                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (strlen((string) $value) > $max) {
                        $this->errors[$field] = "Must be at most {$max} characters.";
                    }
                }
            }

            $cleanData[$field] = $value;
        }

        return $cleanData;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }
}

// Usage
$validator = new FormValidator();
$rules = [
    'name'  => ['required', 'min:3', 'max:100'],
    'email' => ['required', 'email'],
    'age'   => ['int', 'min:0', 'max:150'],
];
$data = $validator->validate($_POST, $rules);

if ($validator->hasErrors()) {
    print_r($validator->getErrors());
} else {
    echo "Valid data!";
    print_r($data);
}

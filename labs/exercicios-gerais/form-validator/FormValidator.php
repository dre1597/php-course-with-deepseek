<?php

class FormValidator
{
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    public function required(string $field): self
    {
        if (!isset($this->data[$field]) || $this->data[$field] === '') {
            $this->errors[$field][] = "$field is required.";
        }

        return $this;
    }

    public function minLength(string $field, int $min): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field][] = "$field must have at least $min characters.";
        }

        return $this;
    }

    public function maxLength(string $field, int $max): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field][] = "$field must have at most $max characters.";
        }

        return $this;
    }

    public function email(string $field): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "$field must be a valid email address.";
        }

        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

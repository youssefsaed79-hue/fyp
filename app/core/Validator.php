<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Input Validator for form submissions
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                $this->applyRule($field, $value, $ruleName, $params);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        $label = ucfirst(str_replace(['_', '-'], ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "The {$label} field is required.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$label} must be a valid email address.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "The {$label} must be a numeric value.");
                }
                break;

            case 'integer':
                if (!empty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "The {$label} must be an integer.");
                }
                break;

            case 'min':
                $min = (float) ($params[0] ?? 0);
                if (is_numeric($value)) {
                    if ((float) $value < $min) {
                        $this->addError($field, "The {$label} must be at least {$min}.");
                    }
                } elseif (is_string($value)) {
                    if (mb_strlen($value) < (int) $min) {
                        $this->addError($field, "The {$label} must be at least {$min} characters.");
                    }
                }
                break;

            case 'max':
                $max = (float) ($params[0] ?? 0);
                if (is_numeric($value)) {
                    if ((float) $value > $max) {
                        $this->addError($field, "The {$label} may not be greater than {$max}.");
                    }
                } elseif (is_string($value)) {
                    if (mb_strlen($value) > (int) $max) {
                        $this->addError($field, "The {$label} may not be greater than {$max} characters.");
                    }
                }
                break;

            case 'in':
                if (!empty($value) && !in_array((string) $value, $params, true)) {
                    $this->addError($field, "The selected {$label} is invalid.");
                }
                break;

            case 'date':
                if (!empty($value)) {
                    $d = \DateTime::createFromFormat('Y-m-d', (string) $value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $this->addError($field, "The {$label} must be a valid date format (YYYY-MM-DD).");
                    }
                }
                break;

            case 'unique':
                if (!empty($value) && count($params) >= 2) {
                    $table = $params[0];
                    $column = $params[1];
                    $exceptId = $params[2] ?? null;
                    $idCol = $params[3] ?? 'id';

                    $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val";
                    $queryParams = ['val' => $value];
                    if ($exceptId !== null && $exceptId !== '') {
                        $sql .= " AND `{$idCol}` != :except_id";
                        $queryParams['except_id'] = $exceptId;
                    }
                    $count = (int) Database::fetchValue($sql, $queryParams);
                    if ($count > 0) {
                        $this->addError($field, "The {$label} has already been taken.");
                    }
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
}

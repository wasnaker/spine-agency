<?php

declare(strict_types=1);

namespace Modules\Agency\Support;

use ReflectionClass;

/**
 * Helper untuk merender deskripsi teks dari activity log changes pada modul Agency.
 */
class ActivityLogText
{
    /** @var array<array{string|int, string|int}> */
    private array $changes;

    /** @var class-string */
    private string $modelClass;

    public function __construct(array $changes, string $modelClass)
    {
        $this->changes = $changes;
        $this->modelClass = $modelClass;
    }

    /**
     * Render deskripsi teks.
     */
    public function render(): string
    {
        $parts = [];
        foreach ($this->changes as $field => $change) {
            if ($this->isSensitiveField($field)) {
                continue;
            }
            $old = $change['old'] ?? '';
            $new = $change['new'] ?? '';
            $label = $this->label($field);
            $parts[] = "{$label}: {$old} -> {$new}";
        }
        return implode(', ', $parts);
    }

    private function isSensitiveField(string $field): bool
    {
        $sensitive = [
            'password',
            'remember_token',
            'current_password',
            'new_password',
            'confirm_password',
            'token',
            '_token',
        ];
        $fieldLower = strtolower($field);
        foreach ($sensitive as $s) {
            if ($fieldLower === $s || str_ends_with($fieldLower, '_' . $s) || str_starts_with($fieldLower, $s . '_')) {
                return true;
            }
        }
        return false;
    }

    private function label(string $field): string
    {
        $rc = new ReflectionClass($this->modelClass);
        if ($rc->hasMethod('labels')) {
            $instance = $rc->newInstance();
            $labels = $instance->labels();
            if (isset($labels[$field])) {
                return $labels[$field];
            }
        }
        return ucwords(str_replace('_', ' ', $field));
    }
}

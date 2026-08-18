<?php

namespace Helpers;

/**
 * Validator — validasi & sanitasi input server-side
 */
class Validator
{
    private array $errors = [];

    /**
     * Ambil semua error.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Cek apakah ada error.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Field wajib diisi.
     */
    public function required(string $field, mixed $value, string $label): self
    {
        if ($value === null || trim((string)$value) === '') {
            $this->errors[$field] = "{$label} wajib diisi.";
        }
        return $this;
    }

    /**
     * Nilai harus numeric dan dalam range [min, max].
     */
    public function numericRange(string $field, mixed $value, float $min, float $max, string $label): self
    {
        if (!is_numeric($value)) {
            $this->errors[$field] = "{$label} harus berupa angka.";
            return $this;
        }

        $v = (float)$value;
        if ($v < $min || $v > $max) {
            $this->errors[$field] = "{$label} harus antara {$min} dan {$max}.";
        }
        return $this;
    }

    /**
     * Panjang string max.
     */
    public function maxLength(string $field, mixed $value, int $max, string $label): self
    {
        if (mb_strlen((string)$value) > $max) {
            $this->errors[$field] = "{$label} maksimal {$max} karakter.";
        }
        return $this;
    }

    /**
     * Value harus ada dalam daftar yang diizinkan.
     */
    public function inList(string $field, mixed $value, array $allowed, string $label): self
    {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} tidak valid.";
        }
        return $this;
    }

    /**
     * Validasi total bobot kriteria harus = 1.000 (toleransi 0.001).
     */
    public static function validateTotalBobot(float $total): bool
    {
        return abs($total - 1.0) <= 0.001;
    }

    /**
     * Sanitasi string — trim + htmlspecialchars.
     */
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitasi float.
     */
    public static function sanitizeFloat(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitasi integer.
     */
    public static function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
}

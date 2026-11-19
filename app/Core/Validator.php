<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validasi input formulir dengan aturan berantai.
 *
 * Setiap aturan menerima label agar pesan error tetap terbaca manusia.
 * Contoh: Validator::make($data)->required('nama_barang', 'Nama barang').
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    private function __construct(private readonly array $data)
    {
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, string $label): self
    {
        $value = $this->value($field);

        if ($value === null || $value === '' || $value === []) {
            $this->fail($field, $label . ' wajib diisi.');
        }

        return $this;
    }

    public function max(string $field, string $label, int $length): self
    {
        $value = $this->value($field);

        if (is_string($value) && mb_strlen($value) > $length) {
            $this->fail($field, $label . ' maksimal ' . $length . ' karakter.');
        }

        return $this;
    }

    public function min(string $field, string $label, int $length): self
    {
        $value = $this->value($field);

        if (is_string($value) && $value !== '' && mb_strlen($value) < $length) {
            $this->fail($field, $label . ' minimal ' . $length . ' karakter.');
        }

        return $this;
    }

    public function email(string $field, string $label): self
    {
        $value = $this->value($field);

        if (is_string($value) && $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->fail($field, $label . ' tidak berformat email yang benar.');
        }

        return $this;
    }

    /**
     * Memastikan nilai termasuk daftar yang diizinkan.
     */
    public function in(string $field, string $label, array $allowed): self
    {
        $value = $this->value($field);

        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->fail($field, $label . ' tidak valid.');
        }

        return $this;
    }

    public function numeric(string $field, string $label): self
    {
        $value = $this->value($field);

        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->fail($field, $label . ' harus berupa angka.');
        }

        return $this;
    }

    /**
     * Memastikan dua field berisi nilai yang sama.
     */
    public function confirmed(string $field, string $label, string $confirmationField): self
    {
        if ($this->value($field) !== $this->value($confirmationField)) {
            $this->fail($field, $label . ' dan konfirmasinya tidak sama.');
        }

        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    private function value(string $field): mixed
    {
        $value = $this->data[$field] ?? null;

        return is_string($value) ? trim($value) : $value;
    }

    private function fail(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }
}

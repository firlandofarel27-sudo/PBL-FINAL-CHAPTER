<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Pembungkus data request: method, path, input, dan berkas unggahan.
 */
final class Request
{
    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $input,
        private readonly array $files
    ) {
    }

    /**
     * Menangkap request saat ini dan membuang awalan base path.
     */
    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri    = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base   = App::basePath();

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $path = '/' . trim($uri, '/');

        return new self($method, $path === '/' ? '/' : rtrim($path, '/'), $_POST, $_FILES);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * Path lengkap dengan query string, dipakai untuk menyimpan halaman
     * tujuan sebelum pengguna diarahkan ke halaman masuk.
     */
    public function uri(): string
    {
        $query = (string) ($_SERVER['QUERY_STRING'] ?? '');

        return $query === '' ? $this->path : $this->path . '?' . $query;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Nilai input POST, dipangkas spasinya bila berupa string.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->input[$key] ?? $default;

        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Seluruh input POST dalam bentuk array.
     */
    public function all(): array
    {
        return array_map(
            static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value,
            $this->input
        );
    }

    /**
     * Berkas unggahan berdasarkan nama field, atau null bila tidak dikirim.
     */
    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $file;
    }

    /**
     * Nilai query string.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        $value = $_GET[$key] ?? $default;

        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Seluruh query string.
     */
    public function queries(): array
    {
        return $_GET;
    }

    /**
     * Token CSRF yang dikirim bersama form.
     */
    public function token(): ?string
    {
        $token = $this->input['_token'];

        return is_string($token) ? $token : null;
    }
}

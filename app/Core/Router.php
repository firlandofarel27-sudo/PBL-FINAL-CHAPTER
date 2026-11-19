<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

/**
 * Router sederhana berbasis pola path.
 *
 * Rute didefinisikan di routes/web.php dengan format
 * '/laporan/barang/{id}' dan dijalankan ke Controller@method.
 */
final class Router
{
    /** @var array<int, array{method: string, regex: string, keys: array<int, string>, handler: string, middleware: array<int, string>}> */
    private array $routes = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, string $handler, array $middleware): void
    {
        $keys  = [];
        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static function (array $match) use (&$keys): string {
                $keys[] = $match[1];

                return '([^/]+)';
            },
            $path
        );

        /* Jumlah segmen tetap dipakai untuk mengurutkan rute: rute literal
           seperti /laporan/baru harus dicoba sebelum /laporan/barang/{id}. */
        $static = substr_count(trim($path, '/'), '/') + 1 - count($keys);

        $this->routes[] = [
            'method'     => $method,
            'regex'      => '#^' . $regex . '$#',
            'keys'       => $keys,
            'handler'    => $handler,
            'middleware' => $middleware,
            'static'     => $static,
        ];
    }

    /**
     * Mencocokkan request dengan rute, menjalankan middleware, lalu controller.
     */
    public function dispatch(Request $request): void
    {
        /* Rute dengan segmen literal lebih banyak dicoba lebih dulu, sehingga
           /laporan/baru tidak tertangkap oleh /laporan/barang/{id}. */
        $routes = $this->routes;
        usort($routes, static fn (array $a, array $b): int => $b['static'] <=> $a['static']);

        foreach ($routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (preg_match($route['regex'], $request->path(), $matches) !== 1) {
                continue;
            }

            array_shift($matches);

            $params = [];

            foreach ($route['keys'] as $index => $key) {
                $params[$key] = rawurldecode((string) ($matches[$index] ?? ''));
            }

            $this->guard($request, $route['middleware']);
            $this->call($route['handler'], $params, $request);

            return;
        }

        $this->notFound();
    }

    /**
     * Menjalankan middleware sesuai urutan deklarasi rute.
     */
    private function guard(Request $request, array $middleware): void
    {
        if ($request->isPost() && !Session::verifyCsrf($request->token())) {
            Session::flash('error', 'Sesi formulir kedaluwarsa. Silakan ulangi.');

            Response::back();
        }

        foreach ($middleware as $entry) {
            [$name, $argument] = array_pad(explode(':', $entry, 2), 2, null);

            match ($name) {
                'auth'  => AuthMiddleware::handle($request),
                'guest' => AuthMiddleware::guest(),
                'role'  => RoleMiddleware::handle((string) $argument, $request),
                default => null,
            };
        }
    }

    /**
     * Memanggil Controller@method.
     *
     * Parameter bertipe Request diisi objek request, parameter lain diisi
     * dari path (misalnya {id}) sesuai nama parameternya.
     */
    private function call(string $handler, array $params, Request $request): void
    {
        [$class, $method] = explode('@', $handler, 2);

        $fqcn = 'App\\Controllers\\' . $class;

        if (!class_exists($fqcn)) {
            throw new \RuntimeException('Controller tidak ditemukan: ' . $fqcn);
        }

        $controller = new $fqcn();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException('Method controller tidak ditemukan: ' . $handler);
        }

        $reflection = new \ReflectionMethod($controller, $method);
        $arguments  = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()
                && is_a($type->getName(), Request::class, true)) {
                $arguments[] = $request;

                continue;
            }

            $name = $parameter->getName();

            if (array_key_exists($name, $params)) {
                $arguments[] = $params[$name];

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();

                continue;
            }

            throw new \RuntimeException('Parameter rute tidak terpenuhi: ' . $handler . '($' . $name . ')');
        }

        $controller->{$method}(...$arguments);
    }

    private function notFound(): void
    {
        Response::html(
            View::capture('errors/404'),
            404
        );
    }
}

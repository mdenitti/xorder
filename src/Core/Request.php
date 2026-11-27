<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP request abstraction with dynamic base-path handling for root or subfolder installs.
 */
final class Request
{
    private string $method;
    private string $path;
    private string $basePath;
    private string $appUrl = '';

    /** @var array<string, mixed> */
    private array $query;

    /** @var array<string, mixed> */
    private array $post;

    /** @var array<string, mixed> */
    private array $server;

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     */
    public function __construct(
        string $method,
        string $path,
        string $basePath,
        array $query,
        array $post,
        array $server
    ) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->basePath = $basePath;
        $this->query = $query;
        $this->post = $post;
        $this->server = $server;
    }

    public static function capture(string $configuredBaseUrl = '', string $appUrl = ''): self
    {
        $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $basePath = self::detectBasePath($configuredBaseUrl, $_SERVER);

        // Geen rewrites: het uitgevoerde .php-bestand (+ query) bepaalt het logische pad,
        // bv. order-edit.php?id=5 => /app/orders/5/edit (zie Routes).
        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $path = Routes::fromFile(basename($scriptName), $_GET);

        $request = new self($method, $path, $basePath, $_GET, $_POST, $_SERVER);
        $request->appUrl = rtrim($appUrl, '/');

        return $request;
    }

    /**
     * @param array<string, mixed> $server
     */
    private static function detectBasePath(string $configuredBaseUrl, array $server): string
    {
        if ($configuredBaseUrl !== '') {
            return rtrim($configuredBaseUrl, '/');
        }

        $scriptName = (string) ($server['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($scriptName));

        if ($dir === '/' || $dir === '.' || $dir === '\\') {
            return '';
        }

        if (str_ends_with($dir, '/public')) {
            $parent = substr($dir, 0, -strlen('/public'));
            $requestUri = (string) ($server['REQUEST_URI'] ?? '');
            if (!str_starts_with($requestUri, $dir)) {
                return $parent === '/' ? '' : rtrim($parent, '/');
            }
        }

        return rtrim($dir, '/');
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function path(): string
    {
        return $this->path;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * Link binnen de app. Geef het logische pad mee (bv. '/app/orders/5/edit', mag ook ?query en #anker bevatten)
     * en je krijgt de link naar het juiste .php-bestand terug (bv. '/order-edit.php?id=5'). Zie Routes.
     */
    public function url(string $path = '/'): string
    {
        return $this->basePath . $this->mapPath($path);
    }

    public function absoluteUrl(string $path = '/'): string
    {
        if ($this->appUrl !== '') {
            return $this->appUrl . $this->mapPath($path);
        }

        $https = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || ((int) ($this->server['SERVER_PORT'] ?? 80) === 443);
        $scheme = $https ? 'https' : 'http';
        $host = (string) ($this->server['HTTP_HOST'] ?? 'localhost');

        return $scheme . '://' . $host . $this->url($path);
    }

    /**
     * Logisch pad (met optionele ?query en #anker) => pad naar het .php-bestand, zonder basePath.
     */
    private function mapPath(string $path): string
    {
        $fragment = '';
        $hashPos = strpos($path, '#');
        if ($hashPos !== false) {
            $fragment = substr($path, $hashPos);
            $path = substr($path, 0, $hashPos);
        }

        $query = '';
        $queryPos = strpos($path, '?');
        if ($queryPos !== false) {
            $query = substr($path, $queryPos + 1);
            $path = substr($path, 0, $queryPos);
        }

        $mapped = Routes::toFile('/' . ltrim($path, '/'));
        if ($query !== '') {
            $mapped .= (str_contains($mapped, '?') ? '&' : '?') . $query;
        }

        return $mapped . $fragment;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function allPost(): array
    {
        return $this->post;
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function acceptLanguage(): string
    {
        return (string) ($this->server['HTTP_ACCEPT_LANGUAGE'] ?? '');
    }
}

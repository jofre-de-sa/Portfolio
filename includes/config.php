<?php
/**
 * Configuração central.
 * Lê variáveis do ficheiro .env (desenvolvimento local) ou do ambiente
 * do servidor (produção: Render, Railway, etc.).
 */

declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

load_env(dirname(__DIR__) . '/.env');

// Sessão segura (usada para CSRF, limite de envios e login do admin)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
    ]);
    session_start();
}

define('SUPABASE_URL', rtrim((string) env('SUPABASE_URL', ''), '/'));
define('SUPABASE_ANON_KEY', (string) env('SUPABASE_ANON_KEY', ''));
define('SUPABASE_SERVICE_KEY', (string) env('SUPABASE_SERVICE_KEY', ''));
define('SITE_URL', rtrim((string) env('SITE_URL', ''), '/'));
define('ADMIN_PASSWORD', (string) env('ADMIN_PASSWORD', ''));

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

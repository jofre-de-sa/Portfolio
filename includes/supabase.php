<?php
/**
 * Cliente mínimo para a API REST do Supabase (PostgREST), usando cURL.
 * Não precisa de Composer nem de bibliotecas externas.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Supabase
{
    public function __construct(
        private string $url,
        private string $key
    ) {}

    /** Cliente público (anon key) — sujeito às políticas RLS. */
    public static function anon(): self
    {
        return new self(SUPABASE_URL, SUPABASE_ANON_KEY);
    }

    /** Cliente de administração (service_role) — usar só no servidor. */
    public static function service(): self
    {
        return new self(SUPABASE_URL, SUPABASE_SERVICE_KEY);
    }

    public function isConfigured(): bool
    {
        return $this->url !== '' && $this->key !== '';
    }

    /** SELECT: $query segue a sintaxe PostgREST, ex. "select=*&order=created_at.desc" */
    public function select(string $table, string $query = 'select=*'): array
    {
        return $this->request('GET', "$table?$query");
    }

    public function insert(string $table, array $row): array
    {
        return $this->request('POST', $table, $row, ['Prefer: return=minimal']);
    }

    public function update(string $table, string $filter, array $data): array
    {
        return $this->request('PATCH', "$table?$filter", $data, ['Prefer: return=minimal']);
    }

    public function delete(string $table, string $filter): array
    {
        return $this->request('DELETE', "$table?$filter");
    }

    private function request(string $method, string $path, ?array $body = null, array $extraHeaders = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Supabase não está configurado (verifique o .env).');
        }

        $ch = curl_init($this->url . '/rest/v1/' . $path);
        $headers = ['apikey: ' . $this->key];
        // Chaves antigas (anon / service_role) são JWT e vão também no Authorization.
        // As novas (sb_publishable_ / sb_secret_) só vão no cabeçalho apikey.
        if (str_starts_with($this->key, 'eyJ')) {
            $headers[] = 'Authorization: Bearer ' . $this->key;
        }
        $headers = array_merge($headers, [
            'Content-Type: application/json',
            'Accept: application/json',
        ], $extraHeaders);

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $response = curl_exec($ch);
        $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erro de ligação ao Supabase: ' . $error);
        }
        if ($status >= 400) {
            throw new RuntimeException("Supabase respondeu $status: $response");
        }

        $decoded = json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : [];
    }
}

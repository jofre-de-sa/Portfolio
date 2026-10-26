<?php
/**
 * Acesso aos dados: projectos (Supabase com reserva local) e mensagens.
 */

declare(strict_types=1);

require_once __DIR__ . '/supabase.php';

/**
 * Devolve os projectos publicados. Se o Supabase não estiver configurado,
 * falhar ou não tiver projectos, usa data/projects.php.
 *
 * @return array{0: array, 1: string} [projectos, origem]
 */
function get_projects(): array
{
    $db = Supabase::anon();
    if ($db->isConfigured()) {
        try {
            $rows = $db->select(
                'projects',
                'select=*&published=eq.true&order=featured.desc,sort_order.asc,created_at.desc'
            );
            if ($rows) {
                return [$rows, 'supabase'];
            }
        } catch (Throwable $ex) {
            error_log('[portfolio] ' . $ex->getMessage());
        }
    }
    return [require dirname(__DIR__) . '/data/projects.php', 'local'];
}

/** Grava uma mensagem do formulário de contacto. */
function save_message(array $data): void
{
    Supabase::anon()->insert('messages', $data);
}

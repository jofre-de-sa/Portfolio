<?php
/**
 * Recebe o formulário de contacto e grava a mensagem no Supabase.
 * Responde em JSON a pedidos fetch() e redirecciona os envios sem JavaScript.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

function respond(bool $ok, string $message, int $status, bool $isAjax, array $errors = []): never
{
    if ($isAjax) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
    } else {
        $_SESSION['flash'] = ['ok' => $ok, 'message' => $message];
        header('Location: ../index.php#contacto', true, 303);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Método não permitido.', 405, $isAjax);
}

if (!csrf_check($_POST['csrf'] ?? null)) {
    respond(false, 'A sessão expirou. Recarregue a página e tente de novo.', 419, $isAjax);
}

// Armadilha anti-spam: bots preenchem o campo escondido.
if (!empty($_POST['website'])) {
    respond(true, 'Mensagem enviada. Obrigado!', 200, $isAjax);
}

// Limite: 1 mensagem a cada 60 s por sessão
$now = time();
if (!empty($_SESSION['last_message_at']) && $now - $_SESSION['last_message_at'] < 60) {
    respond(false, 'Aguarde um minuto antes de enviar outra mensagem.', 429, $isAjax);
}

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? 'Outro'));
$message = trim((string) ($_POST['message'] ?? ''));

$errors = [];
if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors['name'] = 'Indique o seu nome.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors['email'] = 'Indique um email válido.';
}
if (mb_strlen($message) < 10 || mb_strlen($message) > 3000) {
    $errors['message'] = 'A mensagem deve ter entre 10 e 3000 caracteres.';
}
if ($errors) {
    respond(false, 'Verifique os campos assinalados.', 422, $isAjax, $errors);
}

try {
    save_message([
        'name'       => $name,
        'email'      => $email,
        'subject'    => mb_substr($subject, 0, 120),
        'message'    => $message,
        'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    ]);
} catch (Throwable $ex) {
    error_log('[portfolio] ' . $ex->getMessage());
    respond(false, 'Não foi possível enviar agora. Tente mais tarde ou use o email ao lado.', 500, $isAjax);
}

$_SESSION['last_message_at'] = $now;
respond(true, 'Mensagem enviada. Obrigado! Entrarei em contacto em breve.', 200, $isAjax);

<?php
/**
 * Caixa de entrada privada: lê as mensagens do Supabase.
 * Acesso protegido pela variável ADMIN_PASSWORD.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/supabase.php';

$error = '';

// Logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Sessão expirada. Tente de novo.';
    } elseif (ADMIN_PASSWORD === '') {
        $error = 'Defina ADMIN_PASSWORD no .env ou nas variáveis do servidor.';
    } elseif (hash_equals(ADMIN_PASSWORD, (string) $_POST['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        usleep(800000); // trava tentativas em série
        $error = 'Palavra-passe incorrecta.';
    }
}

$logged = !empty($_SESSION['admin']);
$messages = [];

if ($logged) {
    $db = Supabase::service();
    try {
        // Acções: marcar como lida / apagar
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
            $id = (int) ($_POST['id'] ?? 0);
            if (($_POST['action'] ?? '') === 'read' && $id) {
                $db->update('messages', "id=eq.$id", ['is_read' => true]);
            } elseif (($_POST['action'] ?? '') === 'delete' && $id) {
                $db->delete('messages', "id=eq.$id");
            }
            header('Location: admin.php');
            exit;
        }
        $messages = $db->select('messages', 'select=*&order=created_at.desc&limit=200');
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

$unread = count(array_filter($messages, fn($m) => empty($m['is_read'])));
?>
<!doctype html>
<html lang="pt" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Mensagens — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script>try{const t=localStorage.getItem('theme');if(t)document.documentElement.setAttribute('data-bs-theme',t);}catch(e){}</script>
</head>
<body class="admin-body">
<div class="container py-5" style="max-width: 900px;">

<?php if (!$logged): ?>
    <div class="admin-login">
        <h1 class="section-title h3 mb-1">Caixa de entrada</h1>
        <p class="text-body-secondary mb-4">Área privada do portfólio.</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <label class="form-label" for="password">Palavra-passe</label>
            <input class="form-control mb-3" type="password" id="password" name="password" required autofocus>
            <button class="btn btn-accent w-100">Entrar</button>
        </form>
        <a href="index.php" class="d-block text-center mt-3 small">← Voltar ao portfólio</a>
    </div>
<?php else: ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="section-title h3 mb-0">Mensagens</h1>
            <span class="text-body-secondary small"><?= count($messages) ?> no total · <?= $unread ?> por ler</span>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-ghost btn-sm"><i class="bi bi-house"></i> Site</a>
            <a href="admin.php?logout=1" class="btn btn-ghost btn-sm"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </div>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <?php if (!$messages && !$error): ?>
        <div class="text-center text-body-secondary py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Ainda não há mensagens.</div>
    <?php endif; ?>

    <?php foreach ($messages as $m): $isRead = !empty($m['is_read']); ?>
        <article class="msg-card<?= $isRead ? '' : ' is-unread' ?>">
            <header class="d-flex flex-wrap justify-content-between gap-2">
                <div>
                    <strong><?= e($m['name']) ?></strong>
                    <a class="small ms-1" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?? '')) ?>"><?= e($m['email']) ?></a>
                </div>
                <time class="small text-body-secondary"><?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></time>
            </header>
            <span class="chip chip-sm my-2 d-inline-block"><?= e($m['subject'] ?? '') ?></span>
            <p class="mb-3" style="white-space: pre-line;"><?= e($m['message']) ?></p>
            <div class="d-flex gap-2">
                <a class="btn btn-accent btn-sm" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?? '')) ?>"><i class="bi bi-reply"></i> Responder</a>
                <?php if (!$isRead): ?>
                <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><input type="hidden" name="action" value="read">
                    <button class="btn btn-ghost btn-sm"><i class="bi bi-check2"></i> Marcar como lida</button></form>
                <?php endif; ?>
                <form method="post" onsubmit="return confirm('Apagar esta mensagem?');"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><input type="hidden" name="action" value="delete">
                    <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash"></i></button></form>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

</div>
</body>
</html>

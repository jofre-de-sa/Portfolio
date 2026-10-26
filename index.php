<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/repository.php';

$profile = require __DIR__ . '/data/profile.php';
[$projects, $source] = get_projects();

$categories = [
    'all'     => 'Todos',
    'web'     => 'Web',
    'desktop' => 'Desktop',
    'api'     => 'APIs',
    'mobile'  => 'Mobile',
];
$present = array_unique(array_column($projects, 'category'));

// Resultado do envio sem JavaScript (redireccionamento a partir de api/contact.php)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$initials = implode('', array_map(
    fn($w) => mb_strtoupper(mb_substr($w, 0, 1)),
    array_slice(preg_split('/\s+/', trim($profile['name'])), 0, 2)
));
$year = date('Y');
?>
<!doctype html>
<html lang="pt" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($profile['name']) ?> — <?= e($profile['role']) ?></title>
    <meta name="description" content="<?= e($profile['headline']) ?>">

    <!-- Pré-visualização ao partilhar no LinkedIn / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($profile['name']) ?> — <?= e($profile['role']) ?>">
    <meta property="og:description" content="<?= e($profile['headline']) ?>">
    <meta property="og:image" content="<?= e(SITE_URL) ?>/assets/img/og-cover.png">
    <meta property="og:url" content="<?= e(SITE_URL) ?>/">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script>
        // Aplica o tema guardado antes de desenhar a página (evita "piscar")
        try {
            const t = localStorage.getItem('theme') ||
                (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', t);
        } catch (e) {}
    </script>
</head>
<body data-bs-spy="scroll" data-bs-target="#mainNav" data-bs-offset="90">

<!-- ============ NAVEGAÇÃO ============ -->
<nav id="mainNav" class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#inicio">
            <span class="brand-mark"><?= e($initials) ?></span>
            <span class="d-none d-sm-inline"><?= e($profile['name']) ?></span>
        </a>
        <div class="d-flex align-items-center gap-2 order-lg-last">
            <button class="btn btn-icon" id="themeToggle" type="button" aria-label="Mudar tema">
                <i class="bi bi-moon-stars"></i>
            </button>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLinks"
                    aria-controls="navLinks" aria-expanded="false" aria-label="Abrir menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navLinks">
            <ul class="navbar-nav ms-auto me-lg-3">
                <li class="nav-item"><a class="nav-link" href="#sobre">Sobre</a></li>
                <li class="nav-item"><a class="nav-link" href="#projectos">Projectos</a></li>
                <li class="nav-item"><a class="nav-link" href="#servicos">Serviços</a></li>
                <li class="nav-item"><a class="nav-link nav-cta" href="#contacto">Contacto</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ============ HERO ============ -->
<header id="inicio" class="hero">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7 reveal">
                <span class="eyebrow"><span class="dot"></span> Disponível para novos projectos</span>
                <h1 class="hero-title">
                    Olá, sou <span class="accent"><?= e($profile['name']) ?></span>.<br>
                    <?= e($profile['role']) ?>.
                </h1>
                <p class="hero-lead"><?= e($profile['headline']) ?></p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="#projectos" class="btn btn-accent btn-lg">Ver projectos <i class="bi bi-arrow-down-short"></i></a>
                    <a href="#contacto" class="btn btn-ghost btn-lg">Falar comigo</a>
                </div>
                <ul class="hero-stats">
                    <?php foreach ($profile['stats'] as $s): ?>
                        <li><strong><?= e($s['value']) ?></strong><span><?= e($s['label']) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-lg-5 reveal">
                <div class="code-card" aria-hidden="true">
                    <div class="code-card-bar"><span></span><span></span><span></span><em>factura.php</em></div>
<pre><code><span class="k">$factura</span> = Factura::<span class="f">emitir</span>([
  <span class="s">'cliente'</span> => <span class="s">'5000000000'</span>,
  <span class="s">'linhas'</span>  => <span class="k">$carrinho</span>,
  <span class="s">'iva'</span>     => <span class="n">14</span>,
]);

<span class="k">$agt</span>-><span class="f">submeter</span>(<span class="k">$factura</span>);
<span class="c">// ✓ validada pela AGT em 1.2s</span></code></pre>
                    <div class="code-card-badge"><i class="bi bi-patch-check-fill"></i> Software validado</div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ============ SOBRE ============ -->
<section id="sobre" class="section">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-5 reveal">
                <span class="section-label">01 — Sobre</span>
                <h2 class="section-title">Software que resolve problemas reais de negócio.</h2>
                <p class="text-body-secondary"><?= e($profile['about']) ?></p>
                <p class="mb-0 small text-body-secondary"><i class="bi bi-geo-alt"></i> <?= e($profile['location']) ?></p>
                <?php if ($profile['cv_url'] !== '#'): ?>
                    <a href="<?= e($profile['cv_url']) ?>" class="btn btn-ghost mt-4" download><i class="bi bi-download"></i> Descarregar CV</a>
                <?php endif; ?>
            </div>
            <div class="col-lg-7 reveal">
                <div class="skills-grid">
                    <?php foreach ($profile['skills'] as $group => $items): ?>
                        <div class="skill-group">
                            <h3><?= e($group) ?></h3>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($items as $item): ?><span class="chip"><?= e($item) ?></span><?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ PROJECTOS ============ -->
<section id="projectos" class="section section-alt">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 reveal">
            <div>
                <span class="section-label">02 — Projectos</span>
                <h2 class="section-title mb-0">Trabalho seleccionado</h2>
            </div>
            <div class="filter-bar" role="tablist" aria-label="Filtrar projectos">
                <?php foreach ($categories as $key => $label):
                    if ($key !== 'all' && !in_array($key, $present, true)) continue; ?>
                    <button type="button" class="filter-btn<?= $key === 'all' ? ' active' : '' ?>" data-filter="<?= e($key) ?>"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-4" id="projectGrid">
            <?php foreach ($projects as $i => $p):
                $tech = is_array($p['tech'] ?? null) ? $p['tech'] : [];
                $features = is_array($p['features'] ?? null) ? $p['features'] : [];
                $payload = [
                    'title' => $p['title'], 'summary' => $p['summary'], 'category' => $p['category'],
                    'icon' => $p['icon'] ?? 'code-square', 'tech' => $tech, 'features' => $features,
                    'demo_url' => $p['demo_url'] ?? '', 'repo_url' => $p['repo_url'] ?? '',
                ];
            ?>
                <div class="col-md-6 col-xl-4 project-item reveal" data-category="<?= e($p['category']) ?>">
                    <article class="project-card<?= !empty($p['featured']) ? ' is-featured' : '' ?>">
                        <div class="project-head">
                            <span class="project-icon"><i class="bi bi-<?= e($p['icon'] ?? 'code-square') ?>"></i></span>
                            <span class="project-cat"><?= e($categories[$p['category']] ?? $p['category']) ?></span>
                        </div>
                        <h3><?= e($p['title']) ?></h3>
                        <p><?= e($p['summary']) ?></p>
                        <div class="d-flex flex-wrap gap-2 mt-auto pt-2">
                            <?php foreach (array_slice($tech, 0, 4) as $t): ?><span class="chip chip-sm"><?= e($t) ?></span><?php endforeach; ?>
                        </div>
                        <button type="button" class="project-more stretched-link"
                                data-project='<?= e(json_encode($payload, JSON_UNESCAPED_UNICODE)) ?>'>
                            Ver detalhes <i class="bi bi-arrow-right"></i>
                        </button>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($source === 'local'): ?>
            <!-- Projectos lidos de data/projects.php (Supabase não ligado ou tabela vazia) -->
        <?php endif; ?>
    </div>
</section>

<!-- ============ SERVIÇOS ============ -->
<section id="servicos" class="section">
    <div class="container">
        <div class="reveal">
            <span class="section-label">03 — Serviços</span>
            <h2 class="section-title">Como posso ajudar</h2>
        </div>
        <div class="row g-4 mt-1">
            <?php
            $services = [
                ['receipt-cutoff', 'Facturação electrónica', 'Software de facturação integrado com a AGT, incluindo SAF-T e callback de validação.'],
                ['window-stack',   'Aplicações desktop',     'Sistemas para Windows rápidos, com modo offline e impressão térmica.'],
                ['globe2',         'Sistemas web & ERP',     'Plataformas de gestão acessíveis em qualquer lugar, à medida do negócio.'],
                ['plug',           'Integrações & APIs',     'Ligação entre sistemas, bancos, pagamentos e serviços do Estado.'],
            ];
            foreach ($services as $n => [$icon, $title, $text]): ?>
                <div class="col-sm-6 col-lg-3 reveal">
                    <div class="service">
                        <span class="service-num">0<?= $n + 1 ?></span>
                        <i class="bi bi-<?= $icon ?>"></i>
                        <h3><?= e($title) ?></h3>
                        <p><?= e($text) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ CONTACTO ============ -->
<section id="contacto" class="section section-contact">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-5 reveal">
                <span class="section-label">04 — Contacto</span>
                <h2 class="section-title">Tem um projecto em mente?</h2>
                <p class="text-body-secondary">Escreva-me uma mensagem. Respondo normalmente em menos de 24 horas.</p>
                <ul class="contact-list">
                    <li><i class="bi bi-envelope"></i><a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a></li>
                    <li><i class="bi bi-telephone"></i><a href="tel:<?= e(preg_replace('/\s+/', '', $profile['phone'])) ?>"><?= e($profile['phone']) ?></a></li>
                    <li><i class="bi bi-geo-alt"></i><span><?= e($profile['location']) ?></span></li>
                </ul>
                <div class="d-flex gap-2 mt-4">
                    <?php foreach ($profile['socials'] as $net => $url): ?>
                        <a class="btn btn-icon btn-social" href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($net)) ?>">
                            <i class="bi bi-<?= e($net) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-7 reveal">
                <form id="contactForm" class="contact-form" action="api/contact.php" method="post" novalidate>
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <!-- Armadilha anti-spam: humanos não vêem este campo -->
                    <div class="hp" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div id="formAlert" class="alert <?= $flash ? ($flash['ok'] ? 'alert-success' : 'alert-danger') : 'd-none' ?>" role="status">
                        <?= $flash ? e($flash['message']) : '' ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="name" name="name" required minlength="2" maxlength="100" autocomplete="name">
                            <div class="invalid-feedback">Indique o seu nome.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required maxlength="150" autocomplete="email">
                            <div class="invalid-feedback">Indique um email válido.</div>
                        </div>
                        <div class="col-12">
                            <label for="subject" class="form-label">Assunto</label>
                            <select class="form-select" id="subject" name="subject">
                                <option>Novo projecto</option>
                                <option>Software de facturação</option>
                                <option>Aplicação desktop</option>
                                <option>Proposta de emprego</option>
                                <option>Outro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label">Mensagem</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required minlength="10" maxlength="3000"
                                      placeholder="Conte-me um pouco sobre o que precisa…"></textarea>
                            <div class="d-flex justify-content-between">
                                <div class="invalid-feedback d-block invisible flex-grow-1" id="messageFeedback">A mensagem deve ter pelo menos 10 caracteres.</div>
                                <small class="text-body-secondary ms-auto" id="charCount">0 / 3000</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-accent btn-lg w-100" id="submitBtn">
                                <span class="btn-label">Enviar mensagem <i class="bi bi-send"></i></span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container d-flex flex-wrap justify-content-between gap-2">
        <span>© <?= $year ?> <?= e($profile['name']) ?></span>
        <span>Feito com PHP, Bootstrap e Supabase</span>
    </div>
</footer>

<!-- ============ MODAL DE DETALHES ============ -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="project-icon" id="pmIcon"></span>
                    <div>
                        <span class="project-cat" id="pmCat"></span>
                        <h3 class="modal-title" id="projectModalTitle"></h3>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p id="pmSummary"></p>
                <h4 class="pm-sub">Funcionalidades</h4>
                <ul id="pmFeatures" class="pm-features"></ul>
                <h4 class="pm-sub">Tecnologias</h4>
                <div id="pmTech" class="d-flex flex-wrap gap-2"></div>
            </div>
            <div class="modal-footer" id="pmLinks"></div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><span id="toastMsg"></span></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>

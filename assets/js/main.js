/* Portfólio — interacções */
(() => {
    'use strict';

    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => [...el.querySelectorAll(s)];

    /* ---------- Tema claro / escuro ---------- */
    const root = document.documentElement;
    const themeBtn = $('#themeToggle');
    const setIcon = () => {
        if (!themeBtn) return;
        themeBtn.innerHTML = root.getAttribute('data-bs-theme') === 'dark'
            ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
    };
    setIcon();
    themeBtn?.addEventListener('click', () => {
        const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-bs-theme', next);
        try { localStorage.setItem('theme', next); } catch (e) {}
        setIcon();
    });

    /* ---------- Navbar ao fazer scroll + fechar menu móvel ---------- */
    const nav = $('#mainNav');
    const onScroll = () => nav?.classList.toggle('scrolled', window.scrollY > 20);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    $$('#navLinks .nav-link').forEach(a => a.addEventListener('click', () => {
        const menu = $('#navLinks');
        if (menu.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(menu).hide();
    }));

    /* ---------- Animação de entrada ---------- */
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver(entries => {
            entries.forEach(en => {
                if (en.isIntersecting) { en.target.classList.add('visible'); io.unobserve(en.target); }
            });
        }, { threshold: 0.12 });
        $$('.reveal').forEach(el => io.observe(el));
    } else {
        $$('.reveal').forEach(el => el.classList.add('visible'));
    }

    /* ---------- Filtro de projectos ---------- */
    $$('.filter-btn').forEach(btn => btn.addEventListener('click', () => {
        $$('.filter-btn').forEach(b => b.classList.toggle('active', b === btn));
        const f = btn.dataset.filter;
        $$('.project-item').forEach(item => {
            item.classList.toggle('is-hidden', f !== 'all' && item.dataset.category !== f);
        });
    }));

    /* ---------- Modal de detalhes ---------- */
    const modalEl = $('#projectModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const catNames = { web: 'Web', desktop: 'Desktop', api: 'API', mobile: 'Mobile' };
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    $$('.project-more').forEach(btn => btn.addEventListener('click', () => {
        const p = JSON.parse(btn.dataset.project);
        $('#projectModalTitle').textContent = p.title;
        $('#pmCat').textContent = catNames[p.category] || p.category;
        $('#pmIcon').innerHTML = `<i class="bi bi-${esc(p.icon)}"></i>`;
        $('#pmSummary').textContent = p.summary;
        $('#pmFeatures').innerHTML = (p.features || []).map(f => `<li>${esc(f)}</li>`).join('') || '<li>—</li>';
        $('#pmTech').innerHTML = (p.tech || []).map(t => `<span class="chip">${esc(t)}</span>`).join('');
        const links = [];
        if (p.demo_url) links.push(`<a class="btn btn-accent" href="${esc(p.demo_url)}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Ver demo</a>`);
        if (p.repo_url) links.push(`<a class="btn btn-ghost" href="${esc(p.repo_url)}" target="_blank" rel="noopener"><i class="bi bi-github"></i> Código</a>`);
        if (!links.length) links.push(`<a class="btn btn-accent" href="#contacto" data-bs-dismiss="modal">Pedir uma demonstração</a>`);
        $('#pmLinks').innerHTML = links.join('');
        modal.show();
    }));

    /* ---------- Formulário de contacto ---------- */
    const form = $('#contactForm');
    if (!form) return;

    const alertBox = $('#formAlert');
    const btn = $('#submitBtn');
    const msg = $('#message');
    const counter = $('#charCount');
    const msgFeedback = $('#messageFeedback');

    const showAlert = (ok, text) => {
        alertBox.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
        alertBox.textContent = text;
    };
    const updateCount = () => { counter.textContent = `${msg.value.length} / 3000`; };
    msg.addEventListener('input', updateCount);
    updateCount();

    const validate = () => {
        let ok = true;
        ['name', 'email', 'message'].forEach(id => {
            const el = $('#' + id);
            const valid = el.checkValidity() && el.value.trim().length >= (id === 'message' ? 10 : id === 'name' ? 2 : 1);
            el.classList.toggle('is-invalid', !valid);
            if (id === 'message') msgFeedback.classList.toggle('invisible', valid);
            ok = ok && valid;
        });
        return ok;
    };
    $$('input, textarea', form).forEach(el => el.addEventListener('blur', () => {
        if (el.value) validate();
    }));

    form.addEventListener('submit', async ev => {
        ev.preventDefault();
        if (!validate()) return;

        btn.disabled = true;
        $('.btn-label', btn).classList.add('d-none');
        $('.spinner-border', btn).classList.remove('d-none');

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'fetch' },
            });
            const data = await res.json();
            if (data.ok) {
                form.reset();
                updateCount();
                $$('.is-invalid', form).forEach(el => el.classList.remove('is-invalid'));
                alertBox.className = 'alert d-none';
                $('#toastMsg').textContent = data.message;
                bootstrap.Toast.getOrCreateInstance($('#toast')).show();
            } else {
                showAlert(false, data.message);
                Object.keys(data.errors || {}).forEach(k => $('#' + k)?.classList.add('is-invalid'));
            }
        } catch (e) {
            showAlert(false, 'Sem ligação ao servidor. Tente novamente.');
        } finally {
            btn.disabled = false;
            $('.btn-label', btn).classList.remove('d-none');
            $('.spinner-border', btn).classList.add('d-none');
        }
    });
})();

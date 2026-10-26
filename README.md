# Portfólio — PHP · Bootstrap 5 · Supabase

Portfólio pessoal com lista de projectos (ERP de Facturação, Crumb Manager, software desktop…),
filtro por categoria, detalhes em janela, tema claro/escuro e **caixa de mensagens** gravada no
Supabase, com uma página privada (`admin.php`) para as ler.

```
portfolio/
├── index.php              ← página principal
├── admin.php              ← caixa de entrada privada (protegida por palavra-passe)
├── api/contact.php        ← recebe o formulário e grava no Supabase
├── includes/              ← configuração, cliente Supabase, acesso a dados
├── data/profile.php       ← >>> OS SEUS DADOS (nome, bio, contactos, competências)
├── data/projects.php      ← projectos de reserva (usados se o Supabase não responder)
├── database/schema.sql    ← tabelas + segurança + projectos de exemplo
├── assets/css, js, img    ← estilo, interacções, favicon e imagem de partilha
├── Dockerfile, render.yaml← hospedagem no Render
└── .env.example           ← modelo das variáveis secretas
```

> **Importante:** o GitHub Pages só serve sites estáticos (HTML/CSS/JS) e **não executa PHP**.
> Por isso o plano é: **código no GitHub → site a correr no Render (grátis) → dados no Supabase**.
> Cada `git push` para o GitHub actualiza o site automaticamente.

---

## Passo 0 — Personalizar

1. Abra `data/profile.php` e troque nome, função, bio, email, telefone, links e competências.
2. Abra `data/projects.php` (ou, mais tarde, a tabela `projects` no Supabase) e ajuste os projectos.
   O campo `icon` é um nome de <https://icons.getbootstrap.com> (ex.: `receipt`, `pc-display`).
3. Substitua `assets/img/og-cover.png` (1200×627 px) por uma captura do seu site já personalizado —
   é a imagem que aparece quando partilha o link no LinkedIn.
4. Cores: no topo de `assets/css/style.css`, mude `--accent` para a sua cor.

## Passo 1 — Criar a base de dados no Supabase

1. Entre em <https://supabase.com> → **New project**. Escolha nome, palavra-passe da BD e uma região
   próxima (ex.: *West EU*). Aguarde 1–2 minutos.
2. Menu lateral → **SQL Editor** → **New query**. Cole todo o conteúdo de `database/schema.sql` → **Run**.
   Isto cria as tabelas `projects` e `messages`, liga a segurança (RLS) e insere 6 projectos de exemplo.
3. Menu → **Project Settings → API Keys** (ou **API**) e copie:
   - **Project URL** → `SUPABASE_URL`
   - chave **publishable** (ou **anon / public**) → `SUPABASE_ANON_KEY`
   - chave **secret** (ou **service_role**) → `SUPABASE_SERVICE_KEY`

> A chave secreta dá acesso total à BD. Só vai para o `.env` local e para as variáveis do Render —
> **nunca** para o GitHub nem para código JavaScript.

**Como fica a segurança:** com as políticas do `schema.sql`, qualquer visitante pode *ler projectos
publicados* e *enviar* mensagens, mas ninguém consegue ler, alterar ou apagar mensagens com a chave
pública. Só o `admin.php`, no servidor, usa a chave secreta.

**Gerir projectos:** Supabase → **Table Editor → projects**. Adicione linhas, marque `featured` para
destacar, `published = false` para esconder, e use `sort_order` para ordenar. As alterações aparecem
no site imediatamente.

## Passo 2 — Testar no seu computador (opcional)

Precisa de PHP 8.1+ com a extensão cURL (no Windows, o XAMPP já traz).

```bash
cd portfolio
cp .env.example .env        # no Windows: copy .env.example .env
# edite o .env com as chaves do Supabase e uma ADMIN_PASSWORD
php -S localhost:8000
```

Abra <http://localhost:8000>, envie uma mensagem de teste e veja-a em
<http://localhost:8000/admin.php>.

## Passo 3 — Colocar o código no GitHub

1. Crie conta em <https://github.com> → **New repository** → nome `portfolio` → *Public* → **Create**
   (não marque "Add README", já existe um).
2. Instale o Git (<https://git-scm.com>) e, dentro da pasta do projecto:

```bash
git init
git add .
git commit -m "Primeira versão do portfólio"
git branch -M main
git remote add origin https://github.com/SEU-UTILIZADOR/portfolio.git
git push -u origin main
```

3. Confirme no GitHub que o ficheiro **`.env` não aparece** na lista (o `.gitignore` impede-o).

> Alternativa sem linha de comandos: **GitHub Desktop** (<https://desktop.github.com>) →
> *Add local repository* → *Publish repository*.

## Passo 4 — Pôr o site online no Render (grátis)

1. Entre em <https://render.com> com a sua conta GitHub.
2. **New → Web Service** → escolha o repositório `portfolio`.
3. O Render detecta o `Dockerfile`. Confirme: *Language* **Docker**, *Branch* **main**,
   *Instance type* **Free**.
4. Em **Environment Variables**, adicione:

   | Nome | Valor |
   |---|---|
   | `SUPABASE_URL` | o Project URL |
   | `SUPABASE_ANON_KEY` | a chave publishable / anon |
   | `SUPABASE_SERVICE_KEY` | a chave secret / service_role |
   | `ADMIN_PASSWORD` | uma palavra-passe forte |
   | `SITE_URL` | o endereço que o Render lhe der, ex. `https://portfolio-xxxx.onrender.com` |

5. **Create Web Service**. Em 2–4 minutos o site fica em `https://portfolio-xxxx.onrender.com`.
   (Depois de saber o endereço, actualize `SITE_URL` se ainda não o tinha.)
6. A partir daqui, cada `git push` faz um novo deploy automático.

Notas:
- No plano grátis, o serviço "adormece" após um período sem visitas; a primeira visita seguinte
  demora alguns segundos a acordá-lo.
- **Domínio próprio** (ex.: `seunome.ao` ou `.com`): Render → *Settings → Custom Domains* e siga as
  instruções de DNS. Actualize `SITE_URL`.
- **Alternativa:** qualquer alojamento partilhado com PHP (cPanel). Envie os ficheiros por FTP e crie
  lá o `.env` com os mesmos valores.

## Passo 5 — Ler as mensagens

Abra `https://SEU-SITE/admin.php`, entre com a `ADMIN_PASSWORD`. Pode responder (abre o seu email),
marcar como lida ou apagar. As mensagens também ficam visíveis no Supabase em **Table Editor → messages**.

**Receber aviso por email a cada mensagem (opcional):** Supabase → **Database → Webhooks** (ou
*Integrations → Database Webhooks*) → evento *INSERT* na tabela `messages` → enviar para um serviço
como Zapier, Make ou n8n que lhe mande um email.

## Passo 6 — Afixar no LinkedIn

1. **Em destaque (Featured)** — a forma mais visível:
   perfil → **Adicionar secção** (*Add profile section*) → **Recomendado → Adicionar em destaque**
   (*Add featured*) → **Adicionar um link** → cole o endereço do site. O LinkedIn lê o título,
   descrição e a imagem `og-cover.png`. Ajuste o título se quiser → **Guardar**.
2. **Website nas informações de contacto:** no topo do perfil, ícone de lápis → **Informações de
   contacto** → **Website** → cole o link, tipo *Portfólio* → Guardar.
3. **Link personalizado no topo do perfil:** no lápis da introdução, procure **Link personalizado**
   (*Custom button / Website*) e defina o texto "Ver portfólio".
4. **Projectos:** **Adicionar secção → Recomendado → Adicionar projectos** — crie uma entrada por
   projecto (ERP de Facturação, Crumb Manager…) com o link para a respectiva secção do site.
5. **Publicação:** escreva um post a apresentar o portfólio com o link e 2–3 imagens dos projectos.

> Se a pré-visualização não mostrar a imagem actualizada, use o
> **LinkedIn Post Inspector** (<https://www.linkedin.com/post-inspector/>) e cole o URL para
> forçar o LinkedIn a ler de novo.

## Problemas comuns

| Sintoma | Causa provável |
|---|---|
| "Não foi possível enviar agora" | Chaves erradas no Render, ou o `schema.sql` não foi executado. Veja *Logs* no Render. |
| Aparecem só os projectos de `data/projects.php` | A tabela `projects` está vazia ou `SUPABASE_URL`/`SUPABASE_ANON_KEY` estão errados. |
| `admin.php` mostra erro 401 | `SUPABASE_SERVICE_KEY` errada (está a usar a chave pública). |
| "A sessão expirou" no formulário | A página ficou aberta muito tempo; recarregue. |
| A imagem do LinkedIn não aparece | `SITE_URL` em falta ou errado; depois use o Post Inspector. |

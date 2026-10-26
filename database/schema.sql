-- =============================================================
--  Portfólio — esquema da base de dados (Supabase / PostgreSQL)
--  Cole este ficheiro no Supabase: SQL Editor → New query → Run
-- =============================================================

-- ---------- Tabela de projectos ----------
create table if not exists public.projects (
    id          bigint generated always as identity primary key,
    title       text        not null,
    summary     text        not null,
    category    text        not null default 'web'
                check (category in ('web', 'desktop', 'api', 'mobile')),
    icon        text        not null default 'code-square',  -- nome de ícone Bootstrap Icons
    tech        text[]      not null default '{}',
    features    text[]      not null default '{}',
    demo_url    text,
    repo_url    text,
    featured    boolean     not null default false,
    published   boolean     not null default true,
    sort_order  int         not null default 0,
    created_at  timestamptz not null default now()
);

-- ---------- Tabela de mensagens do formulário ----------
create table if not exists public.messages (
    id          bigint generated always as identity primary key,
    name        text        not null check (char_length(name) between 2 and 100),
    email       text        not null check (char_length(email) <= 150 and email ~* '^[^@\s]+@[^@\s]+\.[^@\s]+$'),
    subject     text        check (char_length(subject) <= 120),
    message     text        not null check (char_length(message) between 10 and 3000),
    user_agent  text,
    is_read     boolean     not null default false,
    created_at  timestamptz not null default now()
);

create index if not exists messages_created_at_idx on public.messages (created_at desc);

-- ---------- Segurança (Row Level Security) ----------
-- Com RLS ligado, a chave pública (anon) só faz o que as políticas permitem.
alter table public.projects enable row level security;
alter table public.messages enable row level security;

-- Visitantes podem LER projectos publicados.
drop policy if exists "Projectos publicados são públicos" on public.projects;
create policy "Projectos publicados são públicos"
    on public.projects for select
    to anon, authenticated
    using (published = true);

-- Visitantes podem ENVIAR mensagens, mas nunca lê-las, alterá-las ou apagá-las.
drop policy if exists "Qualquer pessoa pode enviar mensagem" on public.messages;
create policy "Qualquer pessoa pode enviar mensagem"
    on public.messages for insert
    to anon, authenticated
    with check (is_read = false);

-- (A página admin.php usa a chave service_role, que ignora o RLS,
--  por isso não é precisa nenhuma política de leitura de mensagens.)

-- ---------- Projectos de exemplo ----------
insert into public.projects (title, summary, category, icon, tech, features, featured, sort_order) values
('ERP de Facturação Electrónica',
 'Facturação certificada com integração à API da AGT: emissão, assinatura e envio de facturas com retorno automático da validação.',
 'web', 'receipt', '{PHP,PostgreSQL,"API AGT",Bootstrap}',
 '{"Facturas, recibos e notas de crédito","SAF-T (AO)","Callback de validação",Multi-empresa}', true, 1),

('Crumb Manager',
 'Gestão de padaria e pastelaria: produção diária, encomendas, stock de matéria-prima e vendas no balcão.',
 'web', 'basket2', '{Laravel,MySQL,Chart.js}',
 '{"Planeamento de fornadas","Fichas técnicas e custos","Ponto de venda",Relatórios}', true, 2),

('POS Desktop Offline',
 'Ponto de venda para Windows que funciona sem internet e sincroniza com o servidor quando a ligação volta.',
 'desktop', 'pc-display', '{C#,WinForms,SQLite,"Impressora térmica"}',
 '{"Modo offline","Leitor de código de barras","Fecho de caixa",Sincronização}', true, 3),

('Gestão de Stock & Armazém',
 'Controlo de entradas, saídas, transferências entre armazéns e alertas de stock mínimo.',
 'desktop', 'boxes', '{WPF,.NET,"SQL Server"}',
 '{Inventário,"Lotes e validades",Alertas,"Exportação Excel"}', false, 4),

('Recursos Humanos & Salários',
 'Processamento salarial com IRT e Segurança Social, faltas, férias e recibos de vencimento.',
 'web', 'people', '{PHP,MySQL,jQuery}',
 '{"Cálculo de IRT/INSS","Recibos em PDF","Mapa de férias","Portal do funcionário"}', false, 5),

('API de Integração AGT',
 'Serviço intermédio que recebe os callbacks da AGT e entrega o resultado às aplicações desktop dos clientes.',
 'api', 'diagram-3', '{Node.js,WebSocket,Supabase}',
 '{"Endpoint de callback","Encaminhamento por NIF","Notificações em tempo real","Registo de auditoria"}', false, 6);

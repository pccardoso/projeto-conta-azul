# CLAUDE.md

Este arquivo fornece orientações ao Claude Code (claude.ai/code) ao trabalhar com código neste repositório.

## Visão geral do projeto

API em Laravel 13 (PHP 8.3) que integra **Conta Azul** (plataforma contábil/financeira, com duas credenciais de cliente separadas por "base": `COBERTURA_TOTAL` e `MEU_VEICULO`), **Pipefy** (kanban/workflow via GraphQL) e **Efí/Gerencianet** (gateway de pagamento: Pix e cartão de crédito). Fluxo principal: um evento financeiro no Conta Azul vira um registro `FinancialReleases` vinculado a um card do Pipefy; quando o Conta Azul marca o evento como `QUITADO`, a aplicação envia e-mail ao beneficiário e move/atualiza o card no Pipefy. A autenticação da própria API é feita via Laravel Sanctum (tokens bearer), separada dos tokens OAuth emitidos por Conta Azul/Pipefy/Efí.

## Comandos

```bash
# Instalação e setup (copia .env, gera key, migra, builda assets)
composer setup

# Dev local: sobe PHP, worker de fila e Vite em paralelo
composer dev

# Roda a suíte de testes completa (Pest)
composer test
# equivalente a: php artisan config:clear && php artisan test

# Roda um único arquivo de teste / filtra por nome
php artisan test tests/Feature/SomeTest.php
php artisan test --filter=test_name

# Lint / formatação (Pint)
vendor/bin/pint
vendor/bin/pint --test   # apenas verifica, sem alterar

# Frontend (Inertia + Vue 3 + Tailwind, uso mínimo — a maior parte da app é uma API pura)
npm run dev
npm run build

# Worker de fila (SendEmailOficina e outros e-mails são enfileirados)
php artisan queue:listen --tries=1
```

Os testes usam **Pest v4** (`tests/Pest.php`, `tests/Feature`, `tests/Unit`) com PHPUnit como runner por baixo; o ambiente de teste usa SQLite em memória e cache/sessão/mail do tipo array (ver `phpunit.xml`).

Docker: `docker-compose.dev.yml` / `docker-compose.pro.yml` com `docker/php/Dockerfile` e `docker/nginx/default.conf`.

## Arquitetura

**Padrão Controller → Service → (Model | API externa).** Os controllers em `app/Http/Controllers` são enxutos: validam via `app/Http/Requests/{Domain}/*Request.php` e delegam para um service em `app/Service`. A lógica de negócio, chamadas HTTP a APIs externas e tratamento de erros ficam nos services — não nos controllers ou models.

### Pontos de integração

- **`ContaAzulService`** (`app/Service/ContaAzulService.php`) — lê `Tokens` (id `1` = MEU_VEICULO, id `2` = COBERTURA_TOTAL — a seleção é feita via `TypeIntegrationContaAzulEnum`) e chama a API REST do Conta Azul para protocolos/eventos financeiros. Os access tokens são renovados fora desse service (ver abaixo).
- **`TokensService`** — renova os dois tokens OAuth do Conta Azul (Meu Veículo + Cobertura Total) via grant `refresh_token`, executado periodicamente (`app:refresh-token-command`, a cada 55 min, ver `routes/console.php`). Assume que existem exatamente duas linhas na tabela `tokens`, ordenadas por id.
- **`PipefyService`** — todo acesso ao Pipefy é feito via GraphQL contra `https://api.pipefy.com/graphql`, autenticado com um token de client-credentials cacheado em `Cache` sob a chave `pipefy_access_token` (~29 dias; ver `[[Token caching methodology]]` na memória). Operações principais: `getCard`, `getCardWithRelations` (percorre `child_relations` para achar cards relacionados — beneficiário, dados bancários), `updateCard`, `updateLabel` (mescla os ids de label em vez de substituir — a API por padrão substitui todos os labels), `moveCard`. Os ids de fase dos cards e o mapeamento tipo-de-beneficiário→posição-de-relação estão hardcoded (ver `ValidateFinancialCommand` e `FinancialReleasesService::getArrayBeneficiaryPipefy`). `app/Support/PipefyConfiguration.php` guarda a configuração de pipe/posição-de-relação usada para resolver a cadeia card financeiro → pai → beneficiário → dados bancários; adicione novos pipes ali se precisar resolver um novo relacionamento.
- **Efí (`app/Service/Efi/*`, `app/Contracts/EfiPaymentGatewayInterface.php`)** — padrão strategy: `EfiPaymentGatewayFactory::make(EfiPaymentMethodEnum)` retorna `EfiPixService` ou `EfiCreditCardService`, ambos implementando `authenticate()` / `gerarPagamento(array)`. A autenticação do Pix usa mTLS (`services.efi.certificate_path`, um certificado `.p12`/`.pem` em `storage/app/private/efi/`) + Basic Auth com credenciais de cliente.

### Fluxo de domínio (agendado + via webhook)

1. `FinancialReleasesController::store` → `FinancialReleasesService::createFinancialRelease` busca o protocolo/evento no Conta Azul e cria um registro `FinancialReleases` (status inicial conforme `StatusFinancialEnum`: `ABERTO`, `PENDENTE`, `PAGO`, `QUITADO`, `ATRASADO`).
2. `app:validate-financial-command` (cron `0 11,16 * * *`) verifica no Conta Azul todo lançamento `PENDENTE`/`ATRASADO`: ao ficar `QUITADO`, atualiza o registro, envia e-mail ao beneficiário (`FinancialReleasesService::sendEmailBeneficiary`, resolvido via relações do card no Pipefy, e-mail `SendEmailOficina` enfileirado) e move o card no Pipefy para uma de duas fases hardcoded dependendo da presença de anexo de NF-e; quando `ATRASADO`, apenas move o card para a fase "Atrasado".
3. `PeripheralFinancialReleasesService` é uma tabela de acompanhamento separada e mais simples (`peripheral_financial_releases`), indexada por `id_card_pipefy` / `txid_efi` — usada no fluxo periférico relacionado ao Efí, não ligada ao polling do Conta Azul acima.

### Convenções de autenticação e API

- Todas as rotas `/api/*`, exceto `auth-user/login` e `webhook/pix`, exigem `auth:sanctum` + `throttle:sanctum` (rate limit por token, definido em `SANCTUM_RATE_LIMIT_PER_MINUTE`). O login tem seu próprio limiter `throttle:login`, chaveado por e-mail+IP (`SANCTUM_LOGIN_RATE_LIMIT_PER_MINUTE`). Ambos os limiters são definidos em `AppServiceProvider::boot`.
- O formato de erro JSON para `api/*` é normalizado em `bootstrap/app.php` (`withExceptions`): 401/404/429 sempre retornam `{status, codigo, message}`.
- Existem duas "bases" do Conta Azul (pares de client id/secret) coexistindo — `CONTA_AZUL_CLIENT_ID`/`_SECRET` (Meu Veículo) e os sufixados com `_CE` (Cobertura Total). Qualquer novo código de integração com o Conta Azul deve aceitar/propagar um valor `base_integration` e despachar com base em `TypeIntegrationContaAzulEnum`.

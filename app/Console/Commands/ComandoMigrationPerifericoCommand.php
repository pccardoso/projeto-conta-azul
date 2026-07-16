<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Service\PipefyService;
use Illuminate\Support\Facades\Log;

#[Signature('app:comando-migration-periferico-command')]
#[Description('Command description')]
class ComandoMigrationPerifericoCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PipefyService $pipefyService)
    {
        $listCNPJValido = require app_path('Array/ListCNPJValido.php');

        $total = count($listCNPJValido);

        Log::info("Iniciando migração periférico: {$total} registros a importar.");

        foreach ($listCNPJValido as $index => $cnpjValido) {

            $fields = [
                ['field_id' => 'tipo_de_benefici_rio', 'field_value' => 'Pessoa Jurídica'],
                ['field_id' => 'tipo', 'field_value' => 'Prestador de Serviços'],
                ['field_id' => 'base', 'field_value' => 'COBERTURA TOTAL'],
                ['field_id' => 'nome_da_oficina', 'field_value' => $cnpjValido['nome']],
                ['field_id' => 'e_mail', 'field_value' => $cnpjValido['email']],
                ['field_id' => 'estado', 'field_value' => $cnpjValido['estado']],
                ['field_id' => 'cidade', 'field_value' => $cnpjValido['cidade']],
                ['field_id' => 'bairro', 'field_value' => $cnpjValido['bairro']],
                ['field_id' => 'uf', 'field_value' => $cnpjValido['estado']],
                ['field_id' => 'n_mero', 'field_value' => $cnpjValido['numero']],
                ['field_id' => 'cnpj', 'field_value' => $cnpjValido['cnpj']],
            ];

            $telefone = $this->normalizePhoneNumber($cnpjValido['telefone']);

            if ($telefone) {
                $fields[] = ['field_id' => 'telefone', 'field_value' => $telefone];
                $fields[] = ['field_id' => 'whatsapp', 'field_value' => $telefone];
            }

            $numero = $index + 1;

            $card = $pipefyService->createCard([
                'title' => $cnpjValido['nome'],
                'fields' => $fields,
            ]);

            if ($card) {
                Log::info("[{$numero}/{$total}] Card criado para '{$cnpjValido['nome']}' (cnpj: {$cnpjValido['cnpj']}): ".json_encode($card));
                $this->info("[{$numero}/{$total}] Card criado: ".json_encode($card));
            } else {
                Log::error("[{$numero}/{$total}] Falha ao criar card para '{$cnpjValido['nome']}' (cnpj: {$cnpjValido['cnpj']}).");
                $this->error("[{$numero}/{$total}] Falha ao criar card para '{$cnpjValido['nome']}'.");
            }

            if ($numero < $total) {
                sleep(3);
            }

        }

        Log::info('Migração periférico finalizada.');
    }

    /**
     * Remove qualquer caractere não numérico (espaço, +, -, parênteses etc.)
     * e descarta o número caso ele fique incompleto (menos que DDD + 8 dígitos).
     */
    private function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) < 10) {
            return null;
        }

        return $digits;
    }
}

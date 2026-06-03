<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Service\TokensService;

#[Signature('app:refresh-token-command')]
#[Description('Command description')]
class RefreshTokenCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(TokensService $tokensService)
    {
        $tokensService->validateToken();
    }
}

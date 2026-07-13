<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Service\Efi\EfiPixService;
use App\Service\PeripheralFinancialReleases\PeripheralFinancialReleasesService;

class TestController extends Controller
{

    public function __construct(
        protected EfiPixService $efiPixService,
        protected PeripheralFinancialReleasesService $peripheralFinancialReleasesService
    ){}

    public function index()
    {
        Cache::store('redis')->put('user', 'teste', 600);
    }

    public function get()
    {
        $user = Cache::store('redis')->get('user');
        return $user;
    }

    public function testeReqEfi(Request $request)
    {
        $listPix =  $this->efiPixService->getPaymentWebhookData($request->all());

        if(count($listPix)) {

            $dataReturn = $this->peripheralFinancialReleasesService->moveToPipefyPaymentPix($listPix);

            return response()->json([
                "message" => "Dados do Pix recebidos com sucesso!",
                "data" => $dataReturn
            ]);
            
        }
    }

}

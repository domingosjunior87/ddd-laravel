<?php

namespace App\Domain\Endereco\ValueObjects;

use Illuminate\Support\Facades\Http;

class Cep
{
    public static function validarSeEhDoAmazonas(int $cep) : bool
    {
        $response = Http::withOptions([
            'verify' => false,
        ])->get('https://viacep.com.br/ws/' . $cep . '/json/');

        if (!$response->ok()) {
            return false;
        }

        $endereco = json_decode($response->body());

        return $endereco->uf === 'AM';
    }
}

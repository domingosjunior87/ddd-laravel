<?php

namespace App\Domain\Endereco\Models;

use App\Domain\Endereco\ValueObjects\Cep;
use App\Domain\Usuario\Exceptions\UserNotValidatedException;
use Illuminate\Support\Facades\Validator;

class EnderecoObserver
{
    public function saving(Endereco $endereco)
    {
        $validator = Validator::make(
            $endereco->toArray(),
            [
                'identificacao' => 'required',
                'logradouro' => 'required',
                'numero' => 'required',
                'bairro' => 'required',
                'cidade' => 'required',
                'cep' => [
                    'required',
                    'numeric',
                    'digits:8',
                    function ($attribute, $value, $fail) {
                        if (!Cep::validarSeEhDoAmazonas($value)) {
                            $fail('Cep tem que ser do estado do Amazonas');
                        }
                    }
                ]
            ],
            [
                'identificacao.required' => 'Informe a identificação do endereço',
                'cep.required' => 'Informe o CEP',
                'cep.numeric' => 'CEP incorreto',
                'cep.digits' => 'Informe o CEP corretamente',
                'logradouro.required' => 'Informe o logradouro',
                'numero.required' => 'Informe o número do endereço',
                'bairro.required' => 'Informe o bairro',
                'cidade.required' => 'Informe a cidade'
            ]
        );

        if ($validator->fails()) {
            throw new UserNotValidatedException($validator);
        }
    }
}

<?php

namespace App\Domain\Endereco\Models;

use App\Application\Exceptions\ModelNotSavedException;
use App\Application\Exceptions\RecordNotFoundException;
use App\Domain\Endereco\Exceptions\EnderecoNotValidatedException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'identificacao',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'referencia'
    ];

    /**
     * @throws EnderecoNotValidatedException
     */
    public function cadastrar(
        int $userId,
        string $identificacao,
        string $cep,
        string $logradouro,
        string $numero,
        ?string $complemento,
        string $bairro,
        string $cidade,
        ?string $referencia
    ) : Endereco {
        return Endereco::create([
            'user_id'       => $userId,
            'identificacao' => $identificacao,
            'cep'           => $cep,
            'logradouro'    => $logradouro,
            'numero'        => $numero,
            'complemento'   => $complemento,
            'bairro'        => $bairro,
            'cidade'        => $cidade,
            'referencia'    => $referencia
        ]);
    }

    /**
     * @throws RecordNotFoundException
     * @throws ModelNotSavedException
     */
    public function atualizar(
        int $userId,
        string $identificacao,
        string $cep,
        string $logradouro,
        string $numero,
        ?string $complemento,
        string $bairro,
        string $cidade,
        ?string $referencia
    ) : Endereco
    {
        $endereco = Endereco::where('user_id', $userId)->first();

        if ($endereco === null) {
            throw new RecordNotFoundException('Endereço não encontrado');
        }

        $endereco->identificacao = $identificacao;
        $endereco->cep = $cep;
        $endereco->logradouro = $logradouro;
        $endereco->numero = $numero;
        $endereco->complemento = $complemento;
        $endereco->bairro = $bairro;
        $endereco->cidade = $cidade;
        $endereco->referencia = $referencia;

        if (!$endereco->save()) {
            throw new ModelNotSavedException('Não foi possível atualizar os dados do endereço');
        }

        return $endereco;
    }
}

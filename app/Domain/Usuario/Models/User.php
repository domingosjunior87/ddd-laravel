<?php

namespace App\Domain\Usuario\Models;

use App\Application\Exceptions\ModelNotSavedException;
use App\Application\Exceptions\ModelNotValidatedException;
use App\Application\Exceptions\RecordNotFoundException;
use App\Domain\Endereco\Models\Endereco;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $nome
 * @property Carbon $data_nascimento
 * @property string $sexo
 * @property string $cpf
 * @property string $rg
 * @property string $telefone
 * @property string $celular
 * @property string $email
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 * @property Endereco $endereco
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'data_nascimento',
        'sexo',
        'cpf',
        'rg',
        'telefone',
        'celular',
        'email',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'data_nascimento' => 'datetime'
    ];

    public function endereco()
    {
        return $this->hasOne(Endereco::class);
    }

    /**
     * @throws ModelNotValidatedException
     */
    public function cadastrar(
        string $nome,
        string $data_nascimento,
        ?string $sexo,
        string $cpf,
        ?string $rg,
        ?string $telefone,
        string $celular,
        string $email,
        string $password,
        string $identificacao,
        string $cep,
        string $logradouro,
        string $numero,
        ?string $complemento,
        string $bairro,
        string $cidade,
        ?string $referencia
    ) : User {
        DB::beginTransaction();

        try {
            $user = User::create([
                'nome'            => $nome,
                'data_nascimento' => DateTime::createFromFormat('d/m/Y', $data_nascimento)->format('Y-m-d'),
                'sexo'            => $sexo,
                'cpf'             => $cpf,
                'rg'              => $rg,
                'telefone'        => $telefone,
                'celular'         => $celular,
                'email'           => $email,
                'password'        => $password
            ]);

            (new Endereco())->cadastrar(
                $user->id,
                $identificacao,
                $cep,
                $logradouro,
                $numero,
                $complemento,
                $bairro,
                $cidade,
                $referencia
            );

            DB::commit();
        } catch (ModelNotValidatedException $e) {
            DB::rollBack();
            throw $e;
        }

        return $user;
    }

    /**
     * @throws RecordNotFoundException
     * @throws ModelNotSavedException
     */
    public function atualizar(
        int $id,
        string $nome,
        string $data_nascimento,
        ?string $sexo,
        string $cpf,
        ?string $rg,
        ?string $telefone,
        string $celular,
        string $email,
        string $identificacao,
        string $cep,
        string $logradouro,
        string $numero,
        ?string $complemento,
        string $bairro,
        string $cidade,
        ?string $referencia
    ) : User
    {
        $usuario = User::find($id);

        if ($usuario === null) {
            throw new RecordNotFoundException('Usuário não encontrado');
        }

        $usuario->nome = $nome;
        $usuario->data_nascimento = DateTime::createFromFormat('d/m/Y', $data_nascimento)->format('Y-m-d');
        $usuario->sexo = $sexo;
        $usuario->cpf = $cpf;
        $usuario->rg = $rg;
        $usuario->telefone = $telefone;
        $usuario->celular = $celular;
        $usuario->email = $email;

        DB::beginTransaction();

        if (!$usuario->save()) {
            DB::rollBack();
            throw new ModelNotSavedException('Não foi possível atualizar os dados');
        }

        try {
            (new Endereco())->atualizar(
                $usuario->id,
                $identificacao,
                $cep,
                $logradouro,
                $numero,
                $complemento,
                $bairro,
                $cidade,
                $referencia
            );
        } catch (RecordNotFoundException | ModelNotSavedException $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();

        return $usuario;
    }
}

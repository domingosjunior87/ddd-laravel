<?php

namespace App\Domain\Usuario\Models;

use App\Domain\Usuario\Exceptions\UserNotValidatedException;
use App\Domain\Usuario\ValueObjects\Sexo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserObserver
{
    public function creating(User $user)
    {
        $dados = $user->toArray();
        $dados['password'] = $user->password;

        $validator = Validator::make(
            $dados,
            [
                'cpf' => 'unique:users',
                'email' => 'unique:users',
                'password' => 'required'
            ],
            [
                'cpf.unique' => 'CPF já cadastrado',
                'email.unique' => 'Email já cadastrado',
                'password.required' => 'Informe a senha'
            ]
        );

        if ($validator->fails()) {
            throw new UserNotValidatedException($validator);
        }

        $user->password = Hash::make($user->password);
    }

    public function saving(User $user)
    {
        $validator = Validator::make(
            $user->toArray(),
            [
                'nome' => 'required',
                'data_nascimento' => 'required|before_or_equal:-18 years',
                'cpf' => 'required|cpf',
                'telefone' => 'nullable|numeric|digits:10',
                'celular' => 'required|numeric|digits:11',
                'email' => 'required|email',
                'sexo' => [
                    'nullable',
                    'size:1',
                    function ($attribute, $value, $fail) {
                        if (!Sexo::exists($value)) {
                            $fail('Sexo inválido');
                        }
                    },
                ]
            ],
            [
                'nome.required' => 'Informe o nome',
                'data_nascimento.required' => 'Informe a data de nascimento',
                'data_nascimento.before_or_equal' => 'Apenas pessoas com 18 anos ou mais podem se cadastrar',
                'cpf.required' => 'Informe o CPF',
                'telefone.numeric' => 'Telefone incorreto',
                'telefone.digits' => 'Telefone incorreto, informe o DDD e o Telefone',
                'celular.required' => 'Informe o celular',
                'celular.numeric' => 'Celular incorreto',
                'celular.digits' => 'Celular incorreto, informe o DDD e o Celular',
                'email.required' => 'Informe o email',
                'email.email' => 'Informe um email válido',
                'sexo.size' => 'Informe o sexo corretamente'
            ]
        );

        if ($validator->fails()) {
            throw new UserNotValidatedException($validator);
        }
    }
}

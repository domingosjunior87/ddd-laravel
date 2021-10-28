<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
}

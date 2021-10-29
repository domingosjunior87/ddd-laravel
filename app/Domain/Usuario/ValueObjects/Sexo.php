<?php

namespace App\Domain\Usuario\ValueObjects;

use App\Infrastructure\Contracts\Enum;

class Sexo implements Enum
{
    const MASCULINO = 'M';
    const FEMININO = 'F';
    const OUTROS = 'O';

    public static function toArray() : array
    {
        return [
            self::MASCULINO => 'Masculino',
            self::FEMININO => 'Feminino',
            self::OUTROS => 'Outros'
        ];
    }

    public static function exists($valor): bool
    {
        return in_array($valor, [
            self::MASCULINO,
            self::FEMININO,
            self::OUTROS
        ]);
    }

    public static function descricao($valor): string
    {
        return self::toArray()[$valor];
    }
}

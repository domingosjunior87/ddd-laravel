<?php
namespace App\Infrastructure\Contracts;

interface Enum
{
    public static function exists($valor) : bool;

    public static function descricao($valor) : string;

    public static function toArray() : array;
}

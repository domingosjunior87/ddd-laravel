<?php

namespace App\Application\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\Validator;

class ModelNotValidatedException extends Exception
{
    public function __construct(Validator $validator)
    {
        $mensagens = [];
        foreach ($validator->errors()->toArray() as $error) {
            $mensagens[] = implode('. ', $error);
        }

        parent::__construct(implode('. ', $mensagens));
    }
}

<?php

namespace App\UI\Http\Controllers;

use App\Application\Exceptions\ModelNotSavedException;
use App\Application\Exceptions\ModelNotValidatedException;
use App\Application\Exceptions\RecordNotFoundException;
use App\Domain\Usuario\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function registrar(Request $request)
    {
        try {
            (new User())->cadastrar(
                $request->input('nome'),
                $request->input('data_nascimento'),
                $request->input('sexo'),
                $request->input('cpf'),
                $request->input('rg'),
                $request->input('telefone'),
                $request->input('celular'),
                $request->input('email'),
                $request->input('password'),
                $request->input('identificacao'),
                $request->input('cep'),
                $request->input('logradouro'),
                $request->input('numero'),
                $request->input('complemento'),
                $request->input('bairro'),
                $request->input('cidade'),
                $request->input('referencia')
            );
        } catch (ModelNotValidatedException $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response([
                'message' => 'Não foi possível efetuar seu cadastro, verifique se informou todos os campos corretamente. ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response([
            'message' => 'Cadastro efetuado com sucesso!'
        ], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response([
                'message' => 'Usuário inválido'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $token = $user->createToken('token')->plainTextToken;

        $cookie = cookie('jwt', $token, 60 * 24);

        return response([
            'message' => 'sucesso'
        ])->withCookie($cookie);
    }

    public function usuario()
    {
        $usuario = Auth::user()->toArray();

        unset($usuario['id']);
        unset($usuario['created_at']);
        unset($usuario['updated_at']);

        $usuario['data_nascimento'] = date('d/m/Y', strtotime($usuario['data_nascimento']));

        $endereco = Auth::user()->endereco->toArray();

        unset($endereco['id']);
        unset($endereco['user_id']);
        unset($endereco['created_at']);
        unset($endereco['updated_at']);

        return $usuario + $endereco;
    }

    public function atualizar(Request $request)
    {
        $usuario = Auth::user();

        try {
            (new User())->atualizar(
                $usuario->id,
                $request->input('nome'),
                $request->input('data_nascimento'),
                $request->input('sexo'),
                $request->input('cpf'),
                $request->input('rg'),
                $request->input('telefone'),
                $request->input('celular'),
                $request->input('email'),
                $request->input('identificacao'),
                $request->input('cep'),
                $request->input('logradouro'),
                $request->input('numero'),
                $request->input('complemento'),
                $request->input('bairro'),
                $request->input('cidade'),
                $request->input('referencia')
            );
        } catch (RecordNotFoundException $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (ModelNotSavedException $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response([
                'message' => 'Não foi possível efetuar seu cadastro, verifique se informou todos os campos corretamente. ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response([
            'message' => 'Cadastro atualizado com sucesso!'
        ], Response::HTTP_ACCEPTED);
    }

    public function logout()
    {
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'sucesso'
        ])->withCookie($cookie);
    }
}

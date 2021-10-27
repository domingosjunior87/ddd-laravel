<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @throws InvalidArgumentException
     */
    protected function validarUsuario(Request $request, bool $validarCadastro = true) : bool
    {
        $validacoes = [
            'nome' => 'required',
            'data_nascimento' => 'required|date_format:d/m/Y|before_or_equal:-18 years',
            'cpf' => 'required|cpf',
            'telefone' => 'nullable|numeric|digits:10',
            'celular' => 'required|numeric|digits:11',
            'email' => 'required|email'
        ];

        if ($validarCadastro) {
            $validacoes['cpf'] .= '|unique:users';
            $validacoes['email'] .= '|unique:users';
            $validacoes['password'] = 'required';
            $validacoes['confirm_password'] = 'required';
        }

        try {
            $request->validate($validacoes, [
                'nome.required' => 'Informe o nome',
                'data_nascimento.required' => 'Informe a data de nascimento',
                'data_nascimento.date_format' => 'Data incorreta',
                'data_nascimento.before_or_equal' => 'Apenas pessoas com 18 anos ou mais podem se cadastrar',
                'cpf.required' => 'Informe o CPF',
                'cpf.unique' => 'CPF já cadastrado',
                'telefone.numeric' => 'Telefone incorreto',
                'telefone.digits' => 'Telefone incorreto, informe o DDD e o Telefone',
                'celular.required' => 'Informe o celular',
                'celular.numeric' => 'Celular incorreto',
                'celular.digits' => 'Celular incorreto, informe o DDD e o Celular',
                'email.required' => 'Informe o email',
                'email.email' => 'Informe um email válido',
                'email.unique' => 'Email já cadastrado',
                'password.required' => 'Informe a senha',
                'confirm_password.required' => 'Informe a confirmação de senha'
            ]);
        } catch (ValidationException $e) {
            $mensagens = [];
            foreach ($e->errors() as $error) {
                $mensagens[] = implode('. ', $error);
            }

            throw new InvalidArgumentException(implode('. ', $mensagens));
        }

        if ($validarCadastro && $request->input('password') !== $request->input('confirm_password')) {
            throw new InvalidArgumentException('As senhas devem ser iguais');
        }

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validarEndereco(Request $request) : bool
    {
        try {
            $request->validate([
                'identificacao' => 'required',
                'cep' => 'required|numeric|digits:8',
                'logradouro' => 'required',
                'numero' => 'required',
                'bairro' => 'required',
                'cidade' => 'required'
            ], [
                'identificacao.required' => 'Informe a identificação do endereço',
                'cep.required' => 'Informe o CEP',
                'cep.numeric' => 'CEP incorreto',
                'cep.digits' => 'Informe o CEP corretamente',
                'logradouro.required' => 'Informe o logradouro',
                'numero.required' => 'Informe o número do endereço',
                'bairro.required' => 'Informe o bairro',
                'cidade.required' => 'Informe a cidade'
            ]);
        } catch (ValidationException $e) {
            $mensagens = [];
            foreach ($e->errors() as $error) {
                $mensagens[] = implode('. ', $error);
            }

            throw new InvalidArgumentException(implode('. ', $mensagens));
        }

        $response = Http::withOptions([
            'verify' => false,
        ])->get('https://viacep.com.br/ws/' . $request->input('cep') . '/json/');

        if (!$response->ok()) {
            throw new InvalidArgumentException('Verifique se informou o CEP corretamente');
        }

        $cep = json_decode($response->body());

        if ($cep->uf !== 'AM') {
            throw new InvalidArgumentException('Endereço tem que ser do estado do Amazonas');
        }

        return true;
    }

    protected function cadastrarUsuario(Request $request) : User
    {
        return User::create([
            'nome' => $request->input('nome'),
            'data_nascimento' => DateTime::createFromFormat('d/m/Y', $request->input('data_nascimento'))->format('Y-m-d'),
            'sexo' => $request->input('sexo'),
            'cpf' => $request->input('cpf'),
            'rg' => $request->input('rg'),
            'telefone' => $request->input('telefone'),
            'celular' => $request->input('celular'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password'))
        ]);
    }

    protected function cadastrarEndereco(Request $request, int $userId) : Endereco
    {
        return Endereco::create([
            'user_id' => $userId,
            'identificacao' => $request->input('identificacao'),
            'cep' => $request->input('cep'),
            'logradouro' => $request->input('logradouro'),
            'numero' => $request->input('numero'),
            'complemento' => $request->input('complemento'),
            'bairro' => $request->input('bairro'),
            'cidade' => $request->input('cidade'),
            'referencia' => $request->input('referencia')
        ]);
    }

    protected function atualizarUsuario(Request $request) : User
    {
        /** @var User $usuario */
        $usuario = Auth::user();

        $usuario->nome = $request->input('nome');
        $usuario->data_nascimento = DateTime::createFromFormat('d/m/Y', $request->input('data_nascimento'))->format('Y-m-d');
        $usuario->sexo = $request->input('sexo');
        $usuario->cpf = $request->input('cpf');
        $usuario->rg = $request->input('rg');
        $usuario->telefone = $request->input('telefone');
        $usuario->celular = $request->input('celular');
        $usuario->email = $request->input('email');

        if (!$usuario->save()) {
            throw new InvalidArgumentException('Não foi possível atualizar os dados');
        }

        return $usuario;
    }

    protected function atualizarEndereco(Request $request, int $userId) : Endereco
    {
        $endereco = Endereco::where('user_id', $userId)->first();

        $endereco->identificacao = $request->input('identificacao');
        $endereco->cep = $request->input('cep');
        $endereco->logradouro = $request->input('logradouro');
        $endereco->numero = $request->input('numero');
        $endereco->complemento = $request->input('complemento');
        $endereco->bairro = $request->input('bairro');
        $endereco->cidade = $request->input('cidade');
        $endereco->referencia = $request->input('referencia');

        if (!$endereco->save()) {
            throw new InvalidArgumentException('Não foi possível atualizar os dados do endereço');
        }

        return $endereco;
    }

    public function registrar(Request $request)
    {
        try {
            $this->validarUsuario($request);
            $this->validarEndereco($request);
        } catch (InvalidArgumentException $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->cadastrarUsuario($request);
            $this->cadastrarEndereco($request, $user->id);
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

        return $usuario;
    }

    public function atualizar(Request $request)
    {
        try {
            $this->validarUsuario($request, false);
            $this->validarEndereco($request);
        } catch (InvalidArgumentException $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->atualizarUsuario($request);
            $this->atualizarEndereco($request, $user->id);
        } catch (\Exception $e) {
            return response([
                'message' => 'Não foi possível efetuar seu cadastro, verifique se informou todos os campos corretamente. ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response([
            'message' => 'Cadastro atualizado com sucesso!'
        ], Response::HTTP_CREATED);
    }

    public function logout()
    {
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'sucesso'
        ])->withCookie($cookie);
    }
}

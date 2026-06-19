<?php

    namespace App\Service;

    use App\Models\User;
    use Illuminate\Validation\ValidationException;
    use Illuminate\Support\Facades\Hash;

    class AuthUserService
    {
        public function __construct()
        {
            //
        }

        public function login(array $payload): array
        {
            $user = User::where('email', $payload['email'])->first();

            if (!$user || !Hash::check($payload['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciais inválidas.'],
                ]);
            }

            $user->tokens()->delete();

            $token = $user->createToken('api-token')->plainTextToken;

            return [
                'token' => $token,
                'user' => $user,
            ];
        }


    }
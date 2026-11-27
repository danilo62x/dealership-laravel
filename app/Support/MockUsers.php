<?php

namespace App\Support;

/**
 * Autenticação fictícia (espelha dealership/react src/stores/auth.ts).
 *
 * Qualquer e-mail/senha não vazios entram. A conta sugerida na tela de login
 * é gerente@garagem.com.br / demo123.
 */
class MockUsers
{
    public const DEMO_EMAIL = 'gerente@garagem.com.br';

    public const DEMO_PASSWORD = 'demo123';

    /**
     * @return array{id:int,name:string,email:string,role:string,organization:string,store:string}|null
     */
    public function authenticate(string $email, string $password): ?array
    {
        if ($email === '' || $password === '') {
            return null;
        }

        $isDemo = $email === self::DEMO_EMAIL;
        $handle = explode('@', $email)[0] ?: 'Gerente';
        $raw = trim((string) preg_replace('/[._-]/', ' ', $handle)) ?: 'Gerente';
        $name = $isDemo ? 'Marcelo Tavares' : mb_strtoupper(mb_substr($raw, 0, 1)).mb_substr($raw, 1);

        return [
            'id' => 1,
            'name' => $name,
            'email' => $email,
            'role' => 'Gerente de vendas',
            'organization' => 'Garagem Veículos',
            'store' => 'Faria Lima',
        ];
    }

    /** @return array<string,mixed> */
    public function register(string $name, string $email): array
    {
        return [
            'id' => 1,
            'name' => $name,
            'email' => $email,
            'role' => 'Vendedor',
            'organization' => 'Garagem Veículos',
            'store' => '',
        ];
    }
}

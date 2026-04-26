<?php

namespace App\Helpers;

class Crypto
{
    /**
     * VULN #6 — Weak crypto (MD5 para passwords)
     * Detectado por: p/php (php.lang.security.weak-crypto.md5).
     * Fix: usar Hash::make($password) (bcrypt/argon2).
     */
    public function hashPassword(string $password): string
    {
        return md5($password);
    }
}

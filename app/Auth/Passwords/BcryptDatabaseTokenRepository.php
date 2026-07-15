<?php

namespace App\Auth\Passwords;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Hashing\BcryptHasher;

class BcryptDatabaseTokenRepository extends DatabaseTokenRepository
{
    /**
     * Create a new token repository instance.
     */
    public function __construct(
        ConnectionInterface $connection,
        $hasher, // overridden to enforce bcrypt
        string $table,
        string $hashKey,
        int $expires = 3600,
        int $throttle = 60
    ) {
        parent::__construct($connection, new BcryptHasher(), $table, $hashKey, $expires, $throttle);
    }
}

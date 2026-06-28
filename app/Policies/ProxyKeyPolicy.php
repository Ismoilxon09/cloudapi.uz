<?php

namespace App\Policies;

use App\Models\ProxyKey;
use App\Models\User;

class ProxyKeyPolicy {
    public function view(User $user, ProxyKey $key): bool {
        return $user->id === $key->user_id || $user->isAdmin();
    }

    public function update(User $user, ProxyKey $key): bool {
        return $user->id === $key->user_id || $user->isAdmin();
    }

    public function delete(User $user, ProxyKey $key): bool {
        return $user->id === $key->user_id || $user->isAdmin();
    }
}
<?php

namespace App\Policies;

use App\Models\Peticione;
use App\Models\User;

class PeticionePolicy
{
    public function update(User $user, Peticione $peticion)
    {
        return $user->id === $peticion->user_id;
    }

    public function delete(User $user, Peticione $peticion)
    {
        return $user->id === $peticion->user_id;
    }
}

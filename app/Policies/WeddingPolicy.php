<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Auth\Access\Response;

class WeddingPolicy
{
    /** Undangan milik customer lain dibalas 404 agar keberadaannya tidak bocor. */
    private function owns(User $user, Wedding $wedding): Response
    {
        return $user->id === $wedding->user_id ? Response::allow() : Response::denyAsNotFound();
    }

    public function view(User $user, Wedding $wedding): Response
    {
        return $this->owns($user, $wedding);
    }

    public function update(User $user, Wedding $wedding): Response
    {
        return $this->owns($user, $wedding);
    }

    public function publish(User $user, Wedding $wedding): Response
    {
        return $this->owns($user, $wedding);
    }

    public function delete(User $user, Wedding $wedding): Response
    {
        return $this->owns($user, $wedding);
    }
}

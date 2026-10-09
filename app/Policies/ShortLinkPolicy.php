<?php

namespace App\Policies;

use App\Models\ShortLink;
use App\Models\User;

class ShortLinkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ShortLink $shortLink): bool
    {
        return $user->id === $shortLink->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ShortLink $shortLink): bool
    {
        return $this->view($user, $shortLink) && $this->create($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShortLink $shortLink): bool
    {
        return $this->update($user, $shortLink);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ShortLink $shortLink): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ShortLink $shortLink): bool
    {
        return false;
    }
}

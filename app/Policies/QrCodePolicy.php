<?php

namespace App\Policies;

use App\Models\QrCode;
use App\Models\User;

class QrCodePolicy
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
    public function view(User $user, QrCode $qrCode): bool
    {
        return $user->id === $qrCode->user_id;
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
    public function update(User $user, QrCode $qrCode): bool
    {
        return $this->view($user, $qrCode) && $this->create($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QrCode $qrCode): bool
    {
        return $this->update($user, $qrCode);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QrCode $qrCode): bool
    {
        return $this->update($user, $qrCode);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QrCode $qrCode): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\SavedWheel;
use App\Models\User;

class SavedWheelPolicy
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
    public function view(User $user, SavedWheel $savedWheel): bool
    {
        return $user->id === $savedWheel->user_id;
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
    public function update(User $user, SavedWheel $savedWheel): bool
    {
        return $this->view($user, $savedWheel) && $this->create($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SavedWheel $savedWheel): bool
    {
        return $this->update($user, $savedWheel);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SavedWheel $savedWheel): bool
    {
        return $this->update($user, $savedWheel);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SavedWheel $savedWheel): bool
    {
        return false;
    }
}

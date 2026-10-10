<?php

namespace App\Policies;

use App\Models\SmartBoard;
use App\Models\User;

class SmartBoardPolicy
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
    public function view(User $user, SmartBoard $smartBoard): bool
    {
        return $user->id === $smartBoard->user_id;
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
    public function update(User $user, SmartBoard $smartBoard): bool
    {
        return $this->view($user, $smartBoard) && $this->create($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SmartBoard $smartBoard): bool
    {
        return $this->update($user, $smartBoard);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SmartBoard $smartBoard): bool
    {
        return $this->update($user, $smartBoard);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SmartBoard $smartBoard): bool
    {
        return false;
    }
}

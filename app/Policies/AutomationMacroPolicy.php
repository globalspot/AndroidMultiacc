<?php

namespace App\Policies;

use App\Models\AutomationMacro;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AutomationMacroPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view macros list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AutomationMacro $automationMacro): bool
    {
        return $user->id === $automationMacro->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create macros
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AutomationMacro $automationMacro): bool
    {
        return $user->id === $automationMacro->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AutomationMacro $automationMacro): bool
    {
        return $user->id === $automationMacro->user_id;
    }

    /**
     * Determine whether the user can execute the model.
     */
    public function execute(User $user, AutomationMacro $automationMacro): bool
    {
        return $user->id === $automationMacro->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AutomationMacro $automationMacro): bool
    {
        return $user->id === $automationMacro->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AutomationMacro $automationMacro): bool
    {
        return $user->id === $automationMacro->user_id;
    }
}

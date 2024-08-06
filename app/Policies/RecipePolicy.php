<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecipePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the recipe.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Recipe  $recipe
     * @return bool
     */
    public function update(User $user, Recipe $recipe)
    {
        return $user->id === $recipe->user_id;
    }

    /**
     * Determine whether the user can delete the recipe.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Recipe  $recipe
     * @return bool
     */
    public function delete(User $user, Recipe $recipe)
    {
        return $user->id === $recipe->user_id;
    }
}

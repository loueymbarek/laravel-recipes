<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Category;
use App\Models\Recipe;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Resources\RecipeResource;
class RecipeController extends Controller
{
    /**
     * Display a listing of the recipes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $recipes = Recipe::with('user:id,name', 'category','comments.user')->get();
        return response()->json(RecipeResource::collection($recipes));
    }

    /**
     * Store a newly created recipe in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRecipeRequest $request)
    {
        try {
            // Find or create the category
            $category = Category::firstOrCreate(['name' => $request->category]);
    
            // Create the recipe with the validated data and category ID
            $recipe = auth()->user()->recipes()->create(array_merge(
                $request->validated(),
                ['category_id' => $category->id]
            ));
    
            // Load the necessary relationships
            $recipe->load('user:id,name', 'category');
    
            // Return the recipe resource with a success message
            return (new RecipeResource($recipe))->additional(['message' => 'Recipe created successfully']);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Failed to create recipe'], 500);
        }
    }

    
    /**
     * Display the specified recipe.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Recipe $recipe)
    {
        /* use ressource to chose the data*/
        $recipe->load('user:id,name','category','comments.user');
        return response()->json(new RecipeResource($recipe));
    }

    /**
     * Update the specified recipe in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\JsonResponse
     */
   

    public function update(UpdateRecipeRequest $request, Recipe $recipe)
{
    try {
        Gate::authorize('update', $recipe);
    } catch (AuthorizationException $e) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    // Updating the fields if they are provided in the request
    if ($request->filled('title')) {
        $recipe->title = $request->title;
    }
    if ($request->filled('description')) {
        $recipe->description = $request->description;
    }
    if ($request->filled('ingredients')) {
        $recipe->ingredients = $request->ingredients;
    }
    if ($request->filled('steps')) {
        $recipe->steps = $request->steps;
    }
    if ($request->filled('category')) {
        // Find or create the category
        $category = Category::firstOrCreate(['name' => $request->category]);
        $recipe->category_id = $category->id;
    }
    if ($request->filled('preparation_time')) {
        $recipe->preparation_time = $request->preparation_time;
    }
    
    // Save the updated recipe
    $recipe->save();
    
    // Load the necessary relationships
    $recipe->load('category','comments.user');
    
    // Return the updated recipe resource with a success message
    return (new RecipeResource($recipe))->additional(['message' => 'Recipe updated successfully']);
}

    
    /**
     * Remove the specified recipe from storage.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Recipe $recipe)
    {
        try {
            // Authorize the request
            Gate::authorize('delete', $recipe);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted successfully'], 200);
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $minPreparationTime = $request->input('min_preparation_time', 0);
        $maxPreparationTime = $request->input('max_preparation_time', PHP_INT_MAX);
    
        try {
            // Perform the search using Laravel Scout with Typesense
            $results = Recipe::search($query)->get();
            $filteredResults = $results->filter(function ($recipe) use ($minPreparationTime, $maxPreparationTime) {
                return $recipe->preparation_time >= $minPreparationTime && $recipe->preparation_time <= $maxPreparationTime;
            });
    
            $filteredResults->load('category:id,name','comments.user');
            
            // Return the search results as JSON
            return RecipeResource::collection($filteredResults);
        } catch (\Exception $e) {
            // Handle any exceptions (e.g., Typesense server issues)
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Search failed. Please try again later.'], 500);
        }
    }
    
    

}

<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Recipe;
use Illuminate\Http\Request;
use App\Http\Resources\CommentResource;
use Validator;
use Auth;

class CommentController extends Controller
{
    /**
     * Ajouter un commentaire à une recette.
     */
    public function store(Request $request, Recipe $recipe)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'recipe_id' => $recipe->id,
            'content' => $request->content,
        ]);

        return new CommentResource($comment);
    }

    /**
     * Afficher les commentaires d'une recette.
     */
    public function index(Recipe $recipe)
    {
        $comments = $recipe->comments()->with('user')->get();

        return CommentResource::collection($comments);
    }

    /**
     * Modifier un commentaire.
     */
    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $comment->content = $request->content;
        $comment->save();

        return new CommentResource($comment);
    }

    /**
     * Supprimer un commentaire.
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}

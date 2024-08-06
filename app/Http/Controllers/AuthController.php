<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Password;
use App\Http\Resources\UserResource;

use App\Http\Resources\UserCollection;



class AuthController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inscription des nouveaux utilisateurs avec validation des données.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function register() {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        $user = User::create([
            'name' => request()->name,
            'email' => request()->email,
            'password' => bcrypt(request()->password),
        ]);
    
        return new UserResource($user, 201);
    }
    

    /**
     * Authentification des utilisateurs enregistrés avec JWT pour la gestion des sessions.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Récupérer l'utilisateur authentifié.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
{
    // Eager load the recipes relationship
    $user = auth()->user();
 $recipes = $user->recipes()->with('category')->get();
    // Return user data with recipes
    return response()->json([
        
        'name' => $user->name,
        'email' => $user->email,
        'recipes' => RecipeResource::collection($recipes),
    ]);
}

    /**
     * Déconnexion de l'utilisateur (invalider le jeton).
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Rafraîchir un jeton.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Structure de réponse du jeton.
     * 
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Mise à jour du profil de l'utilisateur authentifié.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' =>new UserResource($user),
        ]);
    }

    /**
     * Changer le mot de passe de l'utilisateur authentifié.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        $token = auth()->refresh();

        return response()->json([
            'message' => 'Password updated successfully',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Réinitialisation du mot de passe via un lien envoyé par email.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent to your email.'], 200)
            : response()->json(['error' => trans($response)], 400);
    }

    /**
     * Obtenir le broker de mot de passe pour le contrôleur.
     * 
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * Réinitialiser le mot de passe de l'utilisateur.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $response = Password::reset($request->only('email', 'token', 'password', 'password_confirmation'), function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        return $response == Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully'])
            : response()->json(['message' => 'Failed to reset password'], 400);
    }

    /**
     * Supprimer le compte de l'utilisateur authentifié.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(Request $request)
    {
        $user = auth()->user();
        $this->authorize('update', $user);

        try {
            $user->delete();
            auth()->logout();

            return response()->json(['message' => 'Account deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete account'], 500);
        }
    }

    /**
     * Afficher le profil d'un autre utilisateur.
     * 
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOtherUserProfile(User $user)
    {
        // Use UserResource to transform user data
        $userResource = new UserResource($user->load('recipes'));
        $recipes = $user->recipes()->with('category')->get();
        // Include a custom message in the response
        $username = $user->name;

        return response()->json([
            'message' => "{$username} profile",
            'user' => $userResource,
            'recipes'=>RecipeResource::collection($recipes),
        ]);
    }
    public function index()
    {
        // Eager load recipes with categories for all users
        $users = User::with('recipes.category')->get();
    
        return response()->json($users->map(function ($user) {
            // Transform user data using UserResource
            $userResource = new UserResource($user);
    
            
            // Get recipes with categories for the user
            $recipes = $user->recipes()->with('category')->get();
    
            return [
               
                'user' => $userResource,
                'recipes' => RecipeResource::collection($recipes),
            ];
        }));
    }
    
    
    
    
}

<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

// use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'mot de passe ou utilisateur incorrecte', 'credentials' => $credentials], 401);
        }
        $user = User::find(Auth::user()->id);
        $user->connecter();
        $user->save();
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = User::with('roles')->find(Auth::user()->id);
        if (empty($user->hasRole('Super Admin'))) {
            $permissions = $user->getPermissionsViaRoles();
        } else {
            $permissions = Permission::get();
        }
        $permissionsNames = array_column($permissions->all(), 'name');
        $userInfos = [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'status' => $user->status,
            'roles' => $user->roles,
            'permissions' => $permissionsNames,
        ];
        return response()->json($userInfos);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $user = User::find(Auth::user()->id);
        $user->deconnecter();
        $user->save();
        Auth::logout();
        return response()->json(['message' => 'Vous ếtes bien déconnecté.']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ]);
    }
}

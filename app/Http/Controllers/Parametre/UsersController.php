<?php
namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function getAll()
    {
        $users = User::with('roles')->get();
        return response()->json(['users' => $users]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, User::REGISTER_RULES);
        $user = new User($request->all());
        $user->password = Hash::make($request->password);
        $user->deconnecter();
        $user->save();
        $user->syncRoles($request->roles);
        $message = "L'utilisateur $user->name a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function getOne(int $id)
    {
        $user = User::with('roles')->find($id);
        return response()->json(['user' => $user]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, User::editRules($id));
        $user = User::find($id);
        if (!Hash::check($request->oldPassword, $user->password)) {
            return response()->json(['error' => "Ancien  mot de passe ou Email incorrecte de l'utilisateur."], 401);
        } else {
            $user->name = $request->name;
            $user->email = $request->email;
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
            $user->syncRoles($request->roles);
            $message = "Utilisateur a été modifié avec succes.";
            return response()->json(['message' => $message]);
        }
    }

    public function delete(int $id)
    {
        $user = User::find($id);
        $user->delete();
        $message = "L'utilisateur $user->name a été supprimée avec succès.";
        return response()->json(['message' => $message]);
    }
}

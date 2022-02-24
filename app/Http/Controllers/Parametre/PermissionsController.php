<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    public function getAll()
    {
        $permissions = Permission::get();
        return response()->json(['permissions' => $permissions]);
    }

    public function getOne(int $id)
    {
        $permission = Permission::find($id);
        return response()->json(['permission' => $permission]);
    }

    public function insert(Request $request)
    {
        $rules = ['name' => 'required|unique:permissions,name'];
        $this->validate($request, $rules);
        Permission::create($request->all());
        $message = "La permission $request->name a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $rules = ['name' => 'required|unique:permissions,name,' . $id];
        $this->validate($request, $rules);
        $permission = Permission::find($id);
        $permission->name = $request->name;
        $permission->save();
        $message = "La permission a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $permission = Permission::find($id);
        $permission->delete();
        $message = "La permission $permission->name a été supprimé avec succès.";
        return response()->json(['message' => $message]);
    }
}

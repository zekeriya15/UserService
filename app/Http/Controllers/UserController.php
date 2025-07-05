<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
    public function me() 
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('division');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'division' => $user->division ? [
                'id' => $user->division->id,
                'name' => $user->division->name,
            ] : null,
            'role' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);

    }
}

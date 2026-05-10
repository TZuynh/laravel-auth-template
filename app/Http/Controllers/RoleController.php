<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\RoleRepositoryInterface;

class RoleController extends Controller
{
    public function index(RoleRepositoryInterface $roles): \Illuminate\Contracts\View\View
    {
        return view('roles.index', [
            'matrix' => $roles->permissionMatrix(),
        ]);
    }
}

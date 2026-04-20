<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request, UserRepositoryInterface $userRepository)
    {
        $q = trim((string) $request->query('q', ''));
        $users = $userRepository->paginateBySearch($q, 10);

        return view('users.index', compact('users', 'q'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request, UserRepositoryInterface $userRepository)
    {
        $userRepository->create($request->validated());

        return redirect()->route('users.index')->with('success', 'Created!');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user, UserRepositoryInterface $userRepository)
    {
        $userRepository->update($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy(User $user, UserRepositoryInterface $userRepository)
    {
        $userRepository->delete($user);

        return back()->with('success', 'Deleted!');
    }
}

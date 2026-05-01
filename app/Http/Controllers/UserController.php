<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ActivityNotificationService;
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

    public function store(StoreUserRequest $request, UserRepositoryInterface $userRepository, ActivityNotificationService $activityNotificationService)
    {
        $createdUser = $userRepository->create($request->validated());
        $activityNotificationService->log($request->user(), 'created', 'user', $createdUser->id, $createdUser->name);

        return redirect()->route('users.index')->with('success', __('messages.user_created'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user, UserRepositoryInterface $userRepository, ActivityNotificationService $activityNotificationService)
    {
        $updatedUser = $userRepository->update($user, $request->validated());
        $activityNotificationService->log($request->user(), 'updated', 'user', $updatedUser->id, $updatedUser->name);

        return redirect()->route('users.index')->with('success', __('messages.user_updated'));
    }

    public function destroy(User $user, UserRepositoryInterface $userRepository, ActivityNotificationService $activityNotificationService)
    {
        $deletedUserName = $user->name;
        $deletedUserId = $user->id;
        $userRepository->delete($user);
        $activityNotificationService->log(request()->user(), 'deleted', 'user', $deletedUserId, $deletedUserName);

        return back()->with('success', __('messages.user_deleted'));
    }
}

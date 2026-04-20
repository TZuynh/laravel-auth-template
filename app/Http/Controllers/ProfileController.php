<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Repositories\Contracts\ProfileRepositoryInterface;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profiles.my_profile', [
            'user' => auth()->user(),
            'title' => 'Chỉnh sửa hồ sơ',
        ]);
    }

    public function update(ProfileUpdateRequest $request, ProfileRepositoryInterface $profileRepository)
    {
        $profileRepository->update(
            $request->user(),
            $request->validated(),
            $request->file('avatar')
        );

        return back()->with('success', 'Hồ sơ của bạn đã được làm mới thành công! ✨');
    }
}

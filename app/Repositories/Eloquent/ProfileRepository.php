<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function update(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        if ($avatar !== null) {
            if ($user->avatar && ! str_starts_with($user->avatar, 'http://') && ! str_starts_with($user->avatar, 'https://')) {
                $oldAvatar = str_starts_with($user->avatar, 'storage/')
                    ? substr($user->avatar, strlen('storage/'))
                    : $user->avatar;

                Storage::disk('public')->delete($oldAvatar);
            }

            $user->avatar = $avatar->store('avatars', 'public');
        }

        $user->save();

        return $user;
    }
}

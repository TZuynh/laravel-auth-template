<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
   public function index(Request $request)
{
    $q = trim((string) $request->query('q', ''));

    $usersQuery = User::query();

    if ($q !== '') {
        $qLower = mb_strtolower($q);
        $roleFilter = null;
        
        if (in_array($qLower, ['admin', 'administrator'], true)) {
            $roleFilter = 'administrator';
        } elseif ($qLower === 'staff') {
            $roleFilter = 'staff';
        }

        $usersQuery->where(function ($query) use ($q, $roleFilter) {
            // Tìm theo ID nếu là số
            if (ctype_digit($q)) {
                $query->orWhere('id', (int) $q);
            }

            // Tìm theo tên hoặc email
            $query->orWhere('name', 'like', '%' . $q . '%')
                  ->orWhere('email', 'like', '%' . $q . '%');

            // Tìm theo vai trò nếu khớp từ khóa
            if ($roleFilter) {
                $query->orWhere('role', $roleFilter);
            }
        });
    }

    // Sắp xếp theo ID giảm dần (ID lớn nhất lên đầu)
    // Nếu muốn ID nhỏ nhất lên đầu, hãy đổi 'desc' thành 'asc'
    $users = $usersQuery->orderBy('id', 'desc')
                        ->paginate(10)
                        ->withQueryString();

    return view('users.index', compact('users', 'q'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::in(['administrator', 'staff'])],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'Created!');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

   public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['administrator', 'staff'])],
            'password' => 'nullable|min:6|confirmed', 
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Chỉ cập nhật mật khẩu nếu người dùng có nhập vào
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Deleted!');
    }
}

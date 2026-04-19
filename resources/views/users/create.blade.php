<x-layouts.app title="Create User">

@if ($errors->any())
    <div class="max-w-xl mx-auto mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-red-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
            </svg>
            <h3 class="text-sm font-bold text-red-800">Kiểm tra lại thông tin bên dưới:</h3>
        </div>
        <div class="mt-2 text-sm text-red-700 ml-7">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="max-w-xl mx-auto mb-4">
    <a href="{{ route('users.index') }}" class="inline-flex items-center text-gray-500 hover:text-indigo-600 font-medium transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Quay lại danh sách
    </a>
</div>

<form method="POST" action="{{ route('users.store') }}" class="max-w-xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
    @csrf

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Tạo người dùng mới</h2>
        <p class="text-sm text-gray-500">Điền thông tin để đăng ký thành viên mới vào hệ thống.</p>
    </div>

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và tên</label>
            <input name="name" value="{{ old('name') }}" placeholder="Nhập tên đầy đủ..." 
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Địa chỉ Email</label>
            <input name="email" type="email" value="{{ old('email') }}" placeholder="vi-du@gmail.com" 
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Vai trò</label>
            <select name="role"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all"
                required>
                <option value="administrator" @selected(old('role', 'staff') === 'administrator')>Administrator</option>
                <option value="staff" @selected(old('role', 'staff') === 'staff')>Staff</option>
            </select>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mật khẩu</label>
                <input type="password" name="password" placeholder="••••••••" 
                    class="w-full p-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Xác nhận</label>
                <input type="password" name="password_confirmation" placeholder="••••••••" 
                    class="w-full p-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-indigo-200">
            Tạo tài khoản ngay
        </button>
    </div>
</form>

</x-layouts.app>

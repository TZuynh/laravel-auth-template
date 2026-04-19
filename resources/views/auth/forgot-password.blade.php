<x-layouts.auth title="Forgot Password">
    <div class="max-w-md mx-auto">
        <div class="bg-white/95 rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Forgot your password?</h2>
            <p class="text-gray-600 text-sm text-center mb-6">Enter your email and we’ll send a reset link.</p>

            @if (session('status'))
                <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 text-sm text-red-600">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required
                           class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-medium">Send Reset Link</button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Back to login</a>
            </p>
        </div>
    </div>
</x-layouts.auth>

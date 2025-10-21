<x-layouts.app title="Dashboard">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-blue-100 to-blue-200 p-6">
        <div class="w-full max-w-3xl bg-white/90 backdrop-blur-xl shadow-2xl rounded-3xl p-10 border border-white/40">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-800">Dashboard</h1>
                    <p class="text-gray-500 mt-1">Welcome back, <strong class="text-indigo-600">{{ auth()->user()->name }}</strong> 👋</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="px-4 py-2 bg-gradient-to-r from-red-500 to-pink-500 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200">
                        Logout
                    </button>
                </form>
            </div>

            <!-- Stats Section -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-500 text-white rounded-2xl p-5 shadow-lg">
                    <h2 class="text-sm uppercase opacity-80">Projects</h2>
                    <p class="text-3xl font-bold mt-2">12</p>
                    <p class="text-xs opacity-70 mt-1">Active</p>
                </div>
                <div class="bg-gradient-to-br from-green-400 to-emerald-500 text-white rounded-2xl p-5 shadow-lg">
                    <h2 class="text-sm uppercase opacity-80">Notifications</h2>
                    <p class="text-3xl font-bold mt-2">5</p>
                    <p class="text-xs opacity-70 mt-1">Unread</p>
                </div>
                <div class="bg-gradient-to-br from-amber-400 to-orange-500 text-white rounded-2xl p-5 shadow-lg">
                    <h2 class="text-sm uppercase opacity-80">Tasks</h2>
                    <p class="text-3xl font-bold mt-2">8</p>
                    <p class="text-xs opacity-70 mt-1">To complete</p>
                </div>
            </div>

            <!-- Content Section -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h2>

                <ul class="space-y-3">
                    <li class="flex items-start space-x-3">
                        <div class="h-3 w-3 bg-blue-500 rounded-full mt-2"></div>
                        <p class="text-gray-600"><strong>You</strong> logged in at <span class="text-sm text-gray-400">{{ now()->format('H:i d/m/Y') }}</span>.</p>
                    </li>
                    <li class="flex items-start space-x-3">
                        <div class="h-3 w-3 bg-green-500 rounded-full mt-2"></div>
                        <p class="text-gray-600">Completed <strong>2 tasks</strong> today.</p>
                    </li>
                    <li class="flex items-start space-x-3">
                        <div class="h-3 w-3 bg-amber-400 rounded-full mt-2"></div>
                        <p class="text-gray-600">Awaiting feedback from the project management team.</p>
                    </li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="text-center text-gray-400 text-sm mt-8">
                © {{ date('Y') }} Laravel Auth Template · Designed by <span class="text-indigo-500 font-medium">You</span>
            </div>
        </div>
    </div>
</x-layouts.app>

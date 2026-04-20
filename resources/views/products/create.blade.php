<x-layouts.app :title="__('messages.products.create_page_title')">
    <div class="mx-auto max-w-4xl rounded-[2rem] border border-slate-200 bg-white/90 p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
        <h2 class="mb-6 text-2xl font-black text-slate-900 dark:text-slate-100">{{ __('messages.products.create_heading') }}</h2>

        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('products._form')
            <button class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white transition-colors hover:bg-indigo-700">
                {{ __('messages.products.save') }}
            </button>
        </form>
    </div>
</x-layouts.app>

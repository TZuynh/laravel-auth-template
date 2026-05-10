<x-layouts.app title="Scene Editor">
    <x-marketing.studio-layout active="scenes" title="Scene Editor" eyebrow="AI Storyboard Engine">
        <div class="grid gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
            <x-marketing.glass-card title="Project control" subtitle="{{ $sceneEditor['project']['product'] }}">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Project</p>
                        <h2 class="mt-2 text-xl font-black text-white">{{ $sceneEditor['project']['title'] }}</h2>
                        <span class="mt-4 inline-flex rounded-full bg-violet-500/15 px-3 py-1 text-[10px] font-black uppercase text-violet-200">{{ $sceneEditor['project']['status'] }}</span>
                    </div>

                    <button type="button" class="w-full rounded-2xl bg-blue-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/25">Tạo lại 4 cảnh AI</button>
                    <button type="button" class="w-full rounded-2xl bg-white/10 px-5 py-3 text-sm font-black text-white">Tối ưu camera prompts</button>
                    @if ($sceneEditor['project']['id'])
                        <form method="POST" action="{{ route('marketing.projects.render', $sceneEditor['project']['id']) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-fuchsia-500 to-violet-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-fuchsia-500/25">Render timeline</button>
                        </form>
                    @else
                        <button type="button" class="w-full rounded-2xl bg-gradient-to-r from-fuchsia-500 to-violet-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-fuchsia-500/25">Render timeline</button>
                    @endif
                </div>
            </x-marketing.glass-card>

            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($sceneEditor['scenes'] as $scene)
                    <x-marketing.scene-card :scene="$scene" />
                @endforeach
            </div>
        </div>
    </x-marketing.studio-layout>
</x-layouts.app>

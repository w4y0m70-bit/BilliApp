<x-guest-layout>
    <div class="max-w-4xl mx-auto py-10 text-gray-800">

        <h1 class="text-2xl font-bold mb-6">
            Help 定義一覧（開発用）
        </h1>

        @foreach($helps as $role => $screens)
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">
                    {{ strtoupper($role) }}
                </h2>

                @foreach($screens as $screen => $items)

                    @if(isset($items['title']))
                        {{-- 1階層 help --}}
                        <div class="border rounded p-4 mb-4 bg-white">
                            <p class="text-sm text-gray-500 mb-1">
                                key: {{ $role }}.{{ $screen }}
                            </p>

                            <h3 class="font-semibold">
                                {{ $items['title'] }}
                            </h3>

                            <p class="text-sm mt-1">
                                {{ $items['body'] }}
                            </p>
                        </div>
                    @else
                        {{-- 2階層 help --}}
                        @foreach($items as $action => $help)
                            <div class="border rounded p-4 mb-4 bg-white">
                                <p class="text-sm text-gray-500 mb-1">
                                    key: {{ $role }}.{{ $screen }}.{{ $action }}
                                </p>

                                <h3 class="font-semibold">
                                    {{ $help['title'] }}
                                </h3>

                                <p class="text-sm mt-1">
                                    {{ $help['body'] }}
                                </p>
                            </div>
                        @endforeach
                    @endif

                @endforeach
            </div>
        @endforeach

    </div>
</x-guest-layout>

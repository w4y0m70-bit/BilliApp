<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">プラン編集: {{ $plan->display_name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('master.plans.update', $plan) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium">プラン表示名</label>
                        <input type="text" name="display_name" value="{{ old('display_name', $plan->display_name) }}" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:text-white rounded-md">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">価格 (円)</label>
                            <input type="number" name="price" value="{{ old('price', $plan->price) }}" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">定員上限</label>
                            <input type="number" name="max_capacity" value="{{ old('max_capacity', $plan->max_capacity) }}" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 rounded-md">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">説明文</label>
                        <textarea name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 rounded-md">{{ old('description', $plan->description) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <a href="{{ route('master.plans.index') }}" class="text-gray-600 hover:underline text-sm">戻る</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-md shadow">
                            更新する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-master-layout>
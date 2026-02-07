@extends('admin.layouts.app')
@section('title', 'グループ編集')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <h2 class="text-2xl font-bold mb-6">グループの編集</h2>

    <form action="{{ route('admin.groups.update', $group->id) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="block text-sm font-bold text-gray-700 mb-2">グループ名</label>
            <input type="text" name="name" value="{{ old('name', $group->name) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-admin focus:ring-admin" required>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">参加条件（ユーザーに公開されます）</label>
            <textarea name="description" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-admin focus:ring-admin">{{ old('description', $group->description) }}</textarea>
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('admin.groups.applications') }}" class="text-gray-500 hover:underline text-sm">戻る</a>
            <button type="submit" class="bg-admin text-white px-6 py-2 rounded-lg font-bold">更新する</button>
        </div>
    </form>
</div>
@endsection
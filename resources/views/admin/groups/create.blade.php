@extends('admin.layouts.app')
@section('title', 'グループ作成')

@section('content')
<div class="px-4 py-2">
    <div class="flex items-center space-x-2">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">グループ作成<x-help help-key="admin.groups.create" /></h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 max-w-2xl">
        <form action="{{ route('admin.groups.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- グループ名 --}}
            <div>
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">
                    グループ名<x-help help-key="admin.groups.group_name" />
                </label>
                <input type="text" name="name" id="name" 
                    class="form-control w-full border-gray-300 rounded-lg focus:ring-admin focus:border-admin" 
                    placeholder="例：店舗名、スクール生　など" required>
            </div>

            {{-- 説明文 --}}
            <div>
                <label for="description" class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">
                    参加条件（ユーザーに公開されます）<x-help help-key="admin.groups.description" />
                </label>
                <textarea name="description" id="description" rows="4"
                    class="form-control w-full border-gray-300 rounded-lg focus:ring-admin focus:border-admin" 
                    placeholder="例：当店の常連様、カイルンメンバー　など" required></textarea>
            </div>

            {{-- 非表示パラメータ（ランク） --}}
            <input type="hidden" name="rank" value="1">
            <input type="hidden" name="rank_name" value="一般">

            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.groups.applications') }}" class="text-gray-500 hover:text-gray-700 font-medium">
                    キャンセル
                </a>
                <button type="submit" 
                    class="bg-admin text-white px-8 py-2 rounded-lg hover:bg-admin-dark transition whitespace-nowrap font-bold">
                    グループを作成する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
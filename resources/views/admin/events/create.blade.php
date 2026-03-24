@extends('admin.layouts.app')

@section('title', '新規イベント作成')

@section('content')

    <x-form.section title="新規イベント作成" type="admin" :errors="$errors" maxWidth="max-w-2xl">

        <form id="event-form" action="{{ route('admin.events.confirm') }}" method="POST">
            @csrf

            {{-- 共通パーツ --}}
            @include('admin.events.partials.form-fields', [
                'isLimited' => false,
                'isReplicate' => false,
            ])

            {{-- 下部ボタンエリア --}}
            <div class="mt-8 pt-6 border-t border-gray-100 flex gap-4">
                <button type="submit" class="bg-admin text-white px-8 py-2.5 rounded shadow hover:bg-admin-dark transition">
                    確認画面へ
                </button>
                <a href="{{ route('admin.events.index') }}"
                    class="bg-gray-400 text-white px-8 py-2.5 rounded shadow hover:bg-gray-500 transition">
                    キャンセル
                </a>
            </div>
        </form>

    </x-form.section>

    @include('admin.events.partials.form-scripts')
@endsection

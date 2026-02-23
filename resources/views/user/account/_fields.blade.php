<div class="space-y-1">
{{-- 住所自動入力用 --}}
<span class="p-country-name" style="display:none;">Japan</span>

{{-- 1. 氏名・フリガナセクション --}}
<div class="space-y-1"> {{-- 行間をさらにタイトに --}}
    <label class="block text-sm font-semibold text-gray-700">氏名 <span class="text-red-500">*</span></label>
    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="last_name" :value="old('last_name', $user->last_name ?? '')" placeholder="姓" required />
        <x-form.input name="first_name" :value="old('first_name', $user->first_name ?? '')" placeholder="名" required />
    </div>
</div>

<div class="space-y-1 mt-2">
    <label class="block text-sm font-semibold text-gray-700">フリガナ <span class="text-red-500">*</span></label>
    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="last_name_kana" :value="old('last_name_kana', $user->last_name_kana ?? '')" placeholder="セイ" required pattern="^[ァ-ヶー]+$" />
        <x-form.input name="first_name_kana" :value="old('first_name_kana', $user->first_name_kana ?? '')" placeholder="メイ" required pattern="^[ァ-ヶー]+$" />
    </div>
</div>

{{-- 2. 性別・生年月日セクション --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
    {{-- 性別 --}}
    <div class="flex flex-col">
        <label class="block text-sm font-semibold text-gray-700">性別 <span class="text-red-500">*</span></label>
        <div class="flex gap-4 mt-2 mb-1"> {{-- 縦に並んだ際、下の項目とくっつきすぎないよう調整 --}}
            @foreach(['男性' => '男性', '女性' => '女性', '未回答' => '未回答'] as $val => $label)
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="gender" value="{{ $val }}" 
                        @checked(old('gender', $user->gender ?? '') === $val) 
                        class="rounded-full border-gray-300 text-user focus:ring-user w-4 h-4" required>
                    <span class="ml-1 text-sm text-gray-600">{{ $label === '未回答' ? '未回答' : $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- 生年月日 --}}
    <div>
        <x-form.input type="date" name="birthday" label="生年月日" :value="old('birthday', $user->birthday ?? '')" required class="mb-0" />
    </div>
</div>

{{-- クラス選択（住所の前に持ってきた方が収まりが良い場合があります） --}}
@php
    $currentClass = old('class', (isset($user) && $user->class instanceof \App\Enums\PlayerClass) ? $user->class->value : ($user->class ?? ''));
@endphp
<div class="mt-2">
    <x-form.select name="class" label="クラス" required class="mb-0">
        <option value="" @selected($currentClass === '') disabled>選択してください</option>
        @foreach(\App\Enums\PlayerClass::cases() as $classOption)
            <option value="{{ $classOption->value }}" @selected($currentClass === $classOption->value)>
                {{ $classOption->label() }}
            </option>
        @endforeach
    </x-form.select>
</div>
</div>
@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h2 class="text-lg font-bold mb-4">{{ $event->title }} の参加者一覧0</h2>

    <!-- ゲスト追加フォーム -->
<div class="mb-4">
    <form id="guest-form" class="flex gap-2">
        @csrf
        <input type="text" name="name" placeholder="ゲスト名" class="border p-2 rounded w-48" required>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            追加
        </button>
    </form>
</div>

    <!-- 参加者リスト -->
    <div id="participants-area">
        <p>読み込み中...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const area = document.getElementById('participants-area');
    const eventId = {{ $event->id }};
    const csrf = document.querySelector('input[name=_token]').value;

    // 参加者一覧を取得して描画
    async function loadParticipants() {
        const res = await fetch(`/admin/events/${eventId}/participants/json`);
        const list = await res.json();

        if (list.length === 0) {
            area.innerHTML = `<p class="text-gray-500">参加者はいません。</p>`;
            return;
        }

        area.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                ${list.map(p => `
                    <div class="p-3 rounded shadow ${p.status === 'waitlist' ? 'bg-yellow-100' : 'bg-white'}">
                        <p class="font-medium text-center">${p.name ?? (p.user?.name ?? 'ゲスト')}</p>
                        <button data-id="${p.id}" class="cancel-btn text-red-600 text-sm mt-2 hover:underline block mx-auto">
                            キャンセル
                        </button>
                    </div>
                `).join('')}
            </div>
        `;

        // キャンセルボタンにイベント付与
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                if (!confirm('キャンセルしますか？')) return;

                await fetch(`/admin/events/${eventId}/participants/${id}/cancel`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });
                loadParticipants();
            });
        });
    }

    // ゲスト登録フォーム送信（status自動判定）
    document.getElementById('guest-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        await fetch(`/admin/events/${eventId}/participants`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: formData
        });

        form.reset();
        loadParticipants();
    });

    loadParticipants();
});
</script>
@endsection

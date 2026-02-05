<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventForm = document.getElementById('event-form');
    if (!eventForm) return;

    // 要素の取得
    const eventInput = document.getElementById('event_date');
    const deadlineInput = document.getElementById('entry_deadline');
    const publishedInput = document.getElementById('published_at');
    const maxParticipantsInput = document.getElementById('max_participants');
    const ticketSelect = document.querySelector('select[name="ticket_id"]');

    const pad = num => num.toString().padStart(2, '0');
    const toDatetimeLocal = date => {
        if (!date) return '';
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + 
               'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    };

    /**
     * 1. 初期値のセット（新規作成 or 公開日時が空の複製時のみ）
     */
    if (publishedInput && !publishedInput.value) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(12, 0, 0, 0);
        publishedInput.value = toDatetimeLocal(tomorrow);
    }

    /**
     * 2. 開催日時変更時の「締め切り」自動計算
     * readonlyでない場合のみ動作
     */
    if (eventInput && !eventInput.readOnly) {
        eventInput.addEventListener('change', function() {
            const eventDate = new Date(this.value);
            if (!isNaN(eventDate)) {
                let deadline = new Date(eventDate.getTime() - 24 * 60 * 60 * 1000);
                const now = new Date();
                if (deadline < now) deadline = now;
                deadlineInput.value = toDatetimeLocal(deadline);
            }
        });
    }

    /**
     * 3. 送信時バリデーション
     */
    eventForm.onsubmit = function(e) {
        // A. チケット定員チェック (max_participants と ticket の連動)
        if (ticketSelect && maxParticipantsInput) {
            const selectedOption = ticketSelect.options[ticketSelect.selectedIndex];
            if (selectedOption && selectedOption.value !== "") {
                const capacity = Number(selectedOption.getAttribute('data-capacity'));
                const inputVal = Number(maxParticipantsInput.value);
                if (!isNaN(capacity) && inputVal > capacity) {
                    alert('【定員オーバー】\n選択したチケットの定員（' + capacity + '名）を超えています。');
                    return false;
                }
            }
        }

        // B. 日付整合性チェック
        if (eventInput && deadlineInput) {
            const eventDate = new Date(eventInput.value);
            const deadline = new Date(deadlineInput.value);
            if (!isNaN(eventDate) && !isNaN(deadline) && deadline > eventDate) {
                alert('エントリー締め切りは開催日時より前にしてください');
                return false;
            }
        }

        return true;
    };
});
</script>
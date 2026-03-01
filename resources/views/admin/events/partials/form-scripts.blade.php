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

function fillDefaultDescription() {
    const textarea = document.getElementById('event-description');
    const defaultText = `【種目】ナインボール（セットマッチ）
【試合形式】予選：ダブルイリミネーション／決勝（ベスト８）：シングルイリミネーション
【ルール】ランダムラック／勝者ブレイク／スリーポイントルール採用／プッシュアウトあり／ダブルヒットなし
【ショットクロック】採用：◯分・時間切れ＞1ショット40秒・エクステンション（1ラック1回40秒）
【ハンデ】P=6／A=5／B=4／C=3
【参加費】◯円
【賞典】◯円分の商品券
【注意事項】時間厳守（遅れる場合は事前に店舗までご連絡お願いいたします）
【お店より】和気あいあいと楽しく行うトーナメントです。奮ってご参加ください！
エントリー入力画面から所属店舗の入力をお願いいたします`;

    // テキストエリアに既に値があるか確認
    if (textarea.value.trim() !== "") {
        const result = confirm("既にテキストが入力されています。上書きしてもよろしいですか？");
        if (!result) {
            return; // キャンセルした場合は何もしない
        }
    }

    // テキストをセット
    textarea.value = defaultText;
}

document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.class-checkbox');
    const noneCheckbox = document.querySelector('.class-checkbox[data-is-none="true"]');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.dataset.isNone === 'true') {
                // 「指定なし」がチェックされた場合、他の全てを外す
                if (this.checked) {
                    checkboxes.forEach(cb => {
                        if (cb !== noneCheckbox) cb.checked = false;
                    });
                }
            } else {
                // 「指定なし」以外がチェックされた場合、「指定なし」を外す
                if (this.checked) {
                    noneCheckbox.checked = false;
                }
            }
        });
    });
});
/**
 * クラスのチェックボックスを一括操作する
 * @param {boolean} checked - trueなら全選択、falseなら全解除
 */
function selectAllClasses(checked) {
    // class-checkboxというクラスを持つ全てのinput要素を取得
    const checkboxes = document.querySelectorAll('.class-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
}
// チケットの定員上限と募集人数の整合性をリアルタイムでチェック
// document.addEventListener('change', function() {
//     const ticketSelect = document.getElementById('ticket_id');
//     const maxEntries = document.getElementById('max_entries');
//     const teamSize = document.querySelector('input[name="max_team_size"]:checked');
    
//     if (ticketSelect && maxEntries && teamSize) {
//         const capacity = ticketSelect.options[ticketSelect.selectedIndex].dataset.capacity;
//         const total = maxEntries.value * teamSize.value;
        
//         if (capacity && total > capacity) {
//             alert(`警告：総人数(${total}名)がチケットの上限(${capacity}名)を超えています。`);
//             maxEntries.classList.add('border-red-500');
//         } else {
//             maxEntries.classList.remove('border-red-500');
//         }
//     }
// });
</script>
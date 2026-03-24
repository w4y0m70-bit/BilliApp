<script>
    document.addEventListener('DOMContentLoaded', function() {
        const eventForm = document.getElementById('event-form');
        if (!eventForm) return;

        // 要素の取得
        const eventInput = document.getElementById('event_date');
        const deadlineInput = document.getElementById('entry_deadline');
        const publishedInput = document.getElementById('published_at');
        const ticketSelect = document.querySelector('select[name="ticket_id"]');
        const teamSizeSelect = document.getElementById('max_team_size'); // 修正：IDで取得
        const maxEntriesInput = document.getElementById('max_entries');

        const pad = num => num.toString().padStart(2, '0');
        const toDatetimeLocal = date => {
            if (!date) return '';
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) +
                'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
        };

        /**
         * 1. 初期値のセット
         */
        if (publishedInput && !publishedInput.value) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow.setHours(12, 0, 0, 0);
            publishedInput.value = toDatetimeLocal(tomorrow);
        }

        /**
         * 2. 開催日時変更時の「締め切り」自動計算
         */
        if (eventInput) {
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
            if (ticketSelect && maxEntriesInput && teamSizeSelect) {
                const selectedOption = ticketSelect.options[ticketSelect.selectedIndex];

                if (selectedOption && selectedOption.value !== "") {
                    const capacity = Number(selectedOption.getAttribute('data-capacity'));
                    const entries = Number(maxEntriesInput.value);
                    const teamSize = Number(teamSizeSelect.value); // 修正：セレクトボックスから取得
                    const totalParticipants = entries * teamSize;

                    if (!isNaN(capacity) && totalParticipants > capacity) {
                        alert('【定員オーバー】\n' +
                            '総人数 (' + totalParticipants + '名) が、選択したチケットのプラン上限 (' + capacity +
                            '名) を超えています。\n' +
                            'チーム数または1チームあたりの人数を調整してください。');
                        return false;
                    }
                }
            }

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

        /**
         * 4. 募集クラスの連動制御
         */
        const checkboxes = document.querySelectorAll('.class-checkbox');
        const noneCheckbox = document.querySelector('.class-checkbox[data-is-none="true"]');

        if (noneCheckbox) {
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.dataset.isNone === 'true') {
                        if (this.checked) {
                            checkboxes.forEach(cb => {
                                if (cb !== noneCheckbox) cb.checked = false;
                            });
                        }
                    } else {
                        if (this.checked) {
                            noneCheckbox.checked = false;
                        }
                    }
                });
            });
        }
    }); // ここで最初の DOMContentLoaded がしっかり閉じています

    /**
     * 入力例ボタン用（グローバルスコープ）
     */
    function fillDefaultDescription() {
        const textarea = document.getElementById('event-description');
        if (!textarea) return;

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

        if (textarea.value.trim() !== "") {
            if (!confirm("既にテキストが入力されています。上書きしてもよろしいですか？")) {
                return;
            }
        }
        textarea.value = defaultText;
    }

    /**
     * 全選択・全解除用（グローバルスコープ）
     */
    function selectAllClasses(checked) {
        const checkboxes = document.querySelectorAll('.class-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
    }
</script>

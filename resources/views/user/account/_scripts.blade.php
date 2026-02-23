<script>
function openEmailModal() {
    document.getElementById('emailChangeModal').classList.remove('hidden');
    document.getElementById('modal_error').classList.add('hidden');
    document.getElementById('new_email_input').focus();
}

function closeEmailModal() {
    document.getElementById('emailChangeModal').classList.add('hidden');
}

function submitEmailChange() {
    const newEmail = document.getElementById('new_email_input').value;
    const errorDiv = document.getElementById('modal_error');
    const submitBtn = document.getElementById('submitBtn');

    if (!newEmail) {
        errorDiv.textContent = 'メールアドレスを入力してください。';
        errorDiv.classList.remove('hidden');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="animate-spin mr-2">↻</span>送信中...';

    fetch("{{ route('user.account.email.request') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ new_email: newEmail })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === 'success') {
            alert('確認メールを送信しました。メール内のリンクをクリックして完了させてください。');
            location.reload();
        } else {
            errorDiv.textContent = data.errors ? Object.values(data.errors)[0] : '送信に失敗しました。';
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = '送信する';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = '通信エラーが発生しました。';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = '送信する';
    });
}
</script>
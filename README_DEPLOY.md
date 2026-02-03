📝 メモ：メール送信とキューの設定について
1. 概要
本アプリではメール送信の遅延を防ぎ、ユーザー体験を向上させるために**「非同期送信（キュー）」**を採用している。

2. 仕組み
送信方式: Mail::to()->queue() を使用。

ドライバー: database （.env の QUEUE_CONNECTION=database）。

実行環境: Ubuntuサーバー上で Supervisor を使い、バックグラウンドで php artisan queue:work を常駐させている。

3. サーバー設定 (Supervisor)
設定ファイルの場所: /etc/supervisor/conf.d/laravel-worker.conf

Ini, TOML
[program:laravel-worker]
command=php /var/www/billiardapp/artisan queue:work --tries=3
user=www-data
autostart=true
autorestart=true

4. デプロイ時の注意点（重要）
プログラム（Mailableクラスやロジック）を修正してデプロイした後は、必ずキューワーカーを再起動する必要がある。再起動しないと、ワーカーが古いコードをメモリに保持したままになり、新機能が反映されなかったりエラーの原因になったりする。

再起動コマンド: sudo supervisorctl restart laravel-worker:*

※ 現在の deploy.sh に組み込み済み。

5. トラブルシューティング
メールが届かない・遅い場合:

サーバーで sudo supervisorctl status を実行し、RUNNING になっているか確認。

storage/logs/laravel.log または worker.log にエラーがないか確認。

データベースの jobs テーブルに未処理のデータが溜まっていないか確認。
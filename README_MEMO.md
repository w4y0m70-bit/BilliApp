
-----ライブラリ関係-----
sentry
エラー監視
https://jiyuhonpo.sentry.io/

spatie
操作ログ
composer require spatie/laravel-activitylog

-----cronをサーバで設定する-----
crontab -e
* * * * * cd /var/www/billiardapp && php artisan schedule:run >> /dev/null 2>&1
//上記1行を追加

-----緊急メンテHP閉鎖-----
//メンテナンスモードへ
php artisan down --refresh=15 --secret="my-debug-key"
//自分だけ表示させるURL
https://billents.com/my-debug-key
//メンテナンスモード解除
php artisan up

//メンテナンスにかかる時間表示
15分で終わる軽微な修正なら： php artisan down --refresh=900 （900秒 = 15分） → 画面には 「約 15 分程度」 と表示されます。
1時間かかる大規模メンテなら： php artisan down --refresh=3600 （3600秒 = 60分） → 画面には 「約 60 分程度」 と表示されます。
時間が読めない緊急事態なら： php artisan down （秒数指定なし） → 画面には 「しばらく経ってから」 と表示されます。

-----　パスワード設定　-----
// ユーザーを探す
$a = App\Models\Admin::where('email', 'あなたのメールアドレス')->first();

// パスワードを強制上書き（Hash::makeを忘れずに！）
$a->password = Hash::make('password123');
$a->save();
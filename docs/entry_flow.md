```mermaid
graph TD
    %% ユーザー操作 (Controller)
    Start((開始)) --> Q1[① 申込ボタン押下]
    Q1 --> Method1{entry メソッド}
    
    %% 判定ロジック (Service)
    Method1 --> Q2{② 定員チェック}
    Q2 -- 満員 & 待機不可 --> End1([エントリー不可])
    Q2 -- 許可 --> Q3[③ ステータス設定<br/>pending / entry]
    
    %% チーム・招待ロジック
    Q3 --> Q4{④ チーム戦?}
    Q4 -- Yes --> Q5[⑤ チームメイト招待送信]
    Q4 -- No --> Finish
    
    %% 招待への回答
    Q5 --> Q6{⑥ 招待受諾ボタン<br/>respond メソッド}
    Q6 -- 拒否 --> Q5
    Q6 -- 全員受諾 --> Finish[⑦ エントリー確定処理]
    
    %% 自動処理
    Cron[定期実行 Cron<br/>handleExpiredWaitlist] --> Q2
    Cron --> Q6
    
    Finish --> Success((完了))

    %% 色分けで見やすく
    style Method1 fill:#e3f2fd,stroke:#2196f3
    style Q6 fill:#e3f2fd,stroke:#2196f3
    style Cron fill:#f3e5f5,stroke:#9c27b0
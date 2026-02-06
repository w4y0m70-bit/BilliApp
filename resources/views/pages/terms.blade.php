<x-guest-layout>
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-2xl p-8 text-gray-700">
        {{-- タイトルを柔らかく --}}
        <div class="text-center mb-10">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">ベータ版ご利用にあたって</h1>
            <!-- <p class="text-sm text-gray-500">Billentsを一緒に育ててくださり、ありがとうございます。</p> -->
        </div>

        <div class="space-y-8">
            {{-- はじめに --}}
            <section>
                <h2 class="text-lg font-bold border-l-4 border-blue-400 pl-3 mb-3 text-gray-800"> はじめに</h2>
                <p class="leading-relaxed text-sm">
                    本サービスは現在、正式公開に向けた「準備中（ベータ版）」です。店舗様とビリヤードプレイヤーの皆様により良いシステムを提供できるよう、日々修正を重ねております。
                </p>
            </section>

            {{-- お願い --}}
            <section>
                <h2 class="text-lg font-bold border-l-4 border-blue-400 pl-3 mb-3 text-gray-800"> 不具合が起きた際のお願い</h2>
                <p class="leading-relaxed text-sm">
                    細心の注意を払っておりますが、データの誤表示や一時的な停止が発生する可能性があります。<br><br>
                    万が一、操作ミスや不具合でデータが消えてしまった場合、<span class="font-bold">可能な限り復旧に努めますが、完全な復元をお約束できない場合がございます。</span>
                    大切なイベントの際は、念のため紙のメモなどのバックアップを併用いただけますと幸いです。
                </p>
            </section>

            {{-- データの取り扱い --}}
            <section>
                <h2 class="text-lg font-bold border-l-4 border-blue-400 pl-3 mb-3 text-gray-800"> データの取り扱いについて</h2>
                <p class="leading-relaxed text-sm">
                    原則、ベータ版で作成されたデータは保存され、継続して利用できますが、今後の大幅なアップデートに伴い、やむを得ずこれまでの登録データを一度リセットさせていただく可能性がございます。
                </p>
            </section>

            {{-- フィードバック --}}
            <section class="bg-blue-50 p-5 rounded-xl border border-blue-100">
                <h2 class="text-lg font-bold mb-2 text-blue-800">ぜひご意見をください！</h2>
                <p class="text-sm text-blue-700 leading-relaxed">
                    「ここが使いにくい」「こんな機能がほしい」といったお声が、開発の何よりの励みになります。不具合を見つけた際も、お気軽にLINEなどでお知らせください。
                </p>
            </section>
        </div>

        <div class="mt-12 pt-6 border-t text-center text-xs text-gray-400">
            <p>2026年2月</p>
            <a href="{{ url('/') }}" class="inline-block mt-6 text-blue-500 hover:text-blue-700">トップページへ戻る</a>
        </div>
    </div>
</div>
</x-guest-layout>
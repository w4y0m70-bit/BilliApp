<x-mail::message>
@component('mail::message')
# 新しいイベントが公開されました

イベント名: {{ $event->title }}  
開催日: {{ $event->event_date->format('Y-m-d H:i') }}

@component('mail::button', ['url' => url('/user/events/'.$event->id)])
イベントを見る
@endcomponent

@endcomponent
</x-mail::message>

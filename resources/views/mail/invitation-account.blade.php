<x-mail::message>

    # {{ __('invitation-account.subject', ['app' => config('app.name')]) }} 🎉

    {{ __('invitation-account.greeting', ['name' => $email]) }}

    {{ __('invitation-account.body', ['app' => config('app.name')]) }}

    <x-mail::button :url="$user->link_accept" color="primary">
        {{ __('invitation-account.accept') }}
    </x-mail::button>

    <x-mail::button :url="$user->link_refuse" color="primary">
        {{ __('invitation-account.refuse') }}
    </x-mail::button>

    {{ __('invitation-account.footer') }}

    {{ __('invitation-account.salutation') }}<br>
    <span style="color: #e91e63;">{{ config('app.name') }}</span>
</x-mail::message>

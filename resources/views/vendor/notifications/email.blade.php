@component('mail::message', ['partner'  => $partner])
    {{-- Greeting --}}
    @if (! empty($greeting))
        <h1>{{ $greeting }}</h1>
    @else
        @if ($level == 'error')
            <h1>Whoops!</h1>
        @else
            <h1>Hello!</h1>
        @endif
    @endif

    {{-- Intro Lines --}}
    @foreach ($introLines as $line)
        <p>{{ $line }}</p>

    @endforeach

    {{-- Action Button --}}
    @isset($actionText)
        <?php
        switch ($level) {
            case 'success':
                $color = 'green';
                break;
            case 'error':
                $color = 'red';
                break;
            default:
                $color = 'blue';
        }
        ?>
        @component('mail::button', ['url' => $actionUrl, 'color' => $color])
            {{ $actionText }}
        @endcomponent
    @endisset

    {{-- Outro Lines --}}
    @foreach ($outroLines as $line)
        <p>{{ $line }}</p>

    @endforeach

    {{-- Salutation --}}
    <p>
        @if (! empty($salutation))
            {{ $salutation }}
        @else
            The {{ !empty($app['product_name']) ? $app['product_name'] : 'Dorcas' }} Team<br>
        @endif
    </p>

    {{-- Subcopy --}}
    @isset($actionText)
        @component('mail::subcopy')
            If you’re having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below
            into your web browser: [{{ $actionUrl }}]({{ $actionUrl }})
        @endcomponent
    @endisset
@endcomponent

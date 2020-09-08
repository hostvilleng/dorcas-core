@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => '#'])
            <img width="137" src="{{ !empty($partner) && !empty($partner->logo) ? $partner->logo : cdn('images/dorcas.jpeg') }}" alt="logo">
        @endcomponent
    @endslot

    {{-- Body --}}
    {!! $slot !!}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            <p class="sub align-center">
                The Dorcas Hub is an all-in-one productivity software platform that helps you run your entire business better.
            </p>
            <!-- <p class="sub align-center">
                <br>
                E: <a href="mailto:{{ config('dorcas-api.support.email') }}">{{ config('dorcas-api.support.email') }}</a> or T: <a href="tel:{{ config('dorcas-api.support.phone') }}">{{ config('dorcas-api.support.phone') }}</a> (9am-5pm, WAT)
            </p>
            <p class="sub align-center">
                {{ config('dorcas-api.info.address') }}
                <br/>{{ config('dorcas-api.info.registration') }}
            </p>
            <p class="sub align-center">&copy; {{ date('Y') }} {{ !empty($app['product_name']) ? $app['product_name'] : 'Dorcas' }}. All rights reserved.</p> -->
        @endcomponent
    @endslot
@endcomponent

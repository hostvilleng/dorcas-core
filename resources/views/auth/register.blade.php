@extends('layouts.auth')
@section('body')
    <div class="form">
        <div id="register">
            <h1>Create an Account</h1>
            <form action="{{ url('/register') }}" method="post">
                <div class="top-row">
                    <div class="field-wrap">
                        <label for="firstname">
                            First Name<span class="req">*</span>
                        </label>
                        <input type="text" name="firstname" id="firstname" required autocomplete="off" />
                    </div>
                    <div class="field-wrap">
                        <label for="lastname">
                            Last Name<span class="req">*</span>
                        </label>
                        <input type="text" name="lastname" id="lastname" required autocomplete="off"/>
                    </div>
                </div>
                <div class="top-row">
                    <div class="field-wrap">
                        <label for="email-reg">
                            Email Address<span class="req">*</span>
                        </label>
                        <input type="email" name="email" id="email-reg" required autocomplete="off"/>
                    </div>
                    <div class="field-wrap">
                        <label for="password-reg">
                            Set A Password<span class="req">*</span>
                        </label>
                        <input type="password" name="password" id="password-reg" required autocomplete="off"/>
                    </div>
                </div>
                <div class="top-row">
                    <div class="field-wrap">
                        <label for="company">
                            Company<span class="req">*</span>
                        </label>
                        <input type="text" name="company" id="company" required autocomplete="off"/>
                    </div>
                    <div class="field-wrap">
                        <label for="phone">
                            Phone<span class="req">*</span>
                        </label>
                        <input type="text" name="phone" id="phone" required autocomplete="off"/>
                    </div>
                </div>
                <p class="forgot"><a href="{{ url('/login') }}">Have an account? Log in instead</a></p>
                <button type="submit" class="button button-block">Get Started</button>
            </form>

        </div>
    </div> <!-- /form -->
@endsection
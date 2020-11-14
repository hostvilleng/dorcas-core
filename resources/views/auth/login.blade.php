@extends('layouts.auth')
@section('body')
    <div class="form">
        <ul class="tab-group">
            <li class="tab active"><a href="#login">Log In</a></li>
            <li class="tab"><a href="#register">Register</a></li>
        </ul>
        <div class="tab-content">
            <div id="login">
                <h1>Welcome Back!</h1>
                <form action="{{ url('/oauth/token') }}" method="post">
                    <div class="field-wrap">
                        <label for="username">
                            Email Address<span class="req">*</span>
                        </label>
                        <input type="email" name="username" id="username" required autocomplete="off" autofocus />
                    </div>
                    <div class="field-wrap">
                        <label for="password">
                            Password<span class="req">*</span>
                        </label>
                        <input type="password" name="password" id="password" required autocomplete="off"/>
                    </div>
                    <input type="hidden" name="grant_type" value="password" />
                    <input type="hidden" name="client_id" value="{{ $client->id }}" />
                    <input type="hidden" name="client_secret" value="{{ $client->secret }}" />
                    <input type="hidden" name="scope" value="*" />
                    <p class="forgot"><a href="{{ url('/forgot-password') }}">Forgot Password?</a></p>
                    <button type="submit" class="button button-block">Log In</button>
                </form>
            </div>
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
                    <button type="submit" class="button button-block">Get Started</button>
                </form>

            </div>
        </div><!-- tab-content -->

    </div> <!-- /form -->
@endsection
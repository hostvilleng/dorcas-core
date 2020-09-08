<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle or 'Payment Confirmation' }}</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ web_url('css/pay-form-style.css') }}">
    <meta name="robots" content="noindex,follow" />
</head>
<body>
<form method="post" action="{{ $config['post_url'] }}">
    <div class="checkout-panel">
        <div class="panel-body">
            <h2 class="title">Checkout</h2>
            <div class="progress-bar">
                <div class="step active"></div>
                <div class="step active"></div>
                <div class="step"></div>
                <div class="step"></div>
            </div>
            <!--<div class="payment-method">
                <label for="card" class="method card">
                    <div class="card-logos">
                        <img src="img/visa_logo.png"/>
                        <img src="img/mastercard_logo.png"/>
                    </div>

                    <div class="radio-input">
                        <input id="card" type="radio" name="payment">
                        Pay £340.00 with credit card
                    </div>
                </label>

                <label for="paypal" class="method paypal">
                    <img src="img/paypal_logo.png"/>
                    <div class="radio-input">
                        <input id="paypal" type="radio" name="payment">
                        Pay £340.00 with PayPal
                    </div>
                </label>
            </div>-->

            <div class="input-fields">
                <div class="column-1">
                    <label for="recipient">Recipient</label>
                    <input type="text" id="recipient" name="recipient" value="{{ $company->name }}" readonly />
                    <div class="small-inputs">
                        <div>
                            <label for="date">Amount</label>
                            <input type="text" name="amount" id="amount" value="{{ $order->amount }}"  readonly />
                            <input type="hidden" name="currency" id="currency" value="{{ $order->currency }}" />
                        </div>
                        <div>
                            <!--<label for="verification">CVV / CVC *</label>
                            <input type="password" id="verification"/>-->
                        </div>
                    </div>
                </div>
                <div class="column-2">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" value="{{ $customer->email }}"/>
                    <span class="info">&nbsp;</span>
                </div>
            </div>
        </div>

        <div class="panel-footer">
            <!--<button class="btn back-btn">Back</button>-->
            <input type="hidden" name="PBFPubKey" id="PBFPubKey" value="{{ $config['public_key']['value'] }}" />
            <input type="hidden" name="txref" id="txref" value="{{ $config['txref'] }}" />
            <input type="hidden" name="redirect_url" id="redirect_url" value="{{ $config['redirect_url'] }}" />
            <button class="btn next-btn" type="submit">Next Step</button>
        </div>
    </div>
</form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="{{ web_url('js/payment-form.js') }}"></script>
</body>
</html>


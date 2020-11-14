<?php

use App\Dorcas\Utilities\Gravatar;
use App\Exceptions\RecordNotFoundException;
use App\Models\AccountingAccount;
use App\Models\Company;
use App\Models\DomainIssuance;
use App\Models\Order;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\ClientRepository;
use League\Fractal\ParamBag;
use Yabacon\Paystack;

/**
 * Gets the limit clause from the ParamBag.
 * This ParamBag is provided in requests processed by Fractal.
 *
 * @param ParamBag|null $params
 * @param int           $offset
 * @param int           $limit
 *
 * @return array
 */
function parse_fractal_params(ParamBag $params = null, int $offset = 0, int $limit = 10): array
{
    if (empty($params)) {
        return [$limit, $offset];
    }
    $arguments = $params->get('limit') ?: [$limit, $offset];
    return $arguments;
}

/**
 * A simpler way to generate the gravatar
 *
 * @param string $email
 * @param bool   $secure
 * @param int    $width
 * @param string $default
 * @param string $rating
 *
 * @return string
 */
function gravatar(
    string $email,
    bool $secure = true,
    int $width = 400,
    string $default = Gravatar::DEFAULT_IMG_RETRO,
    string $rating = Gravatar::RATED_G
): string {
    return Gravatar::getGravatar($email, $secure, $width, $default, $rating);
}

/**
 * @param string        $base
 * @param string|null   $path
 * @param array|null    $parameters
 * @param bool          $secure
 *
 * @return string
 */
function custom_url(string $base, string $path = null, array $parameters = null, bool $secure = true): string
{
    $uri = new \GuzzleHttp\Psr7\Uri($base);
    # create the URI
    $path = $path ?: '/';
    # for situations where NULL was passed as the path - we assume the base
    if (!empty($path) && !(is_string($path) || is_array($path))) {
        throw new InvalidArgumentException('path should either be a string or an array');
    }
    if (!empty($path)) {
        $path = is_string($path) ? $path : implode('/', $path);
        $uri = $uri->withPath(starts_with($path, '/') ? $path : '/'.$path);
    }
    if (!empty($parameters)) {
        $uri = $uri->withQuery(http_build_query($parameters));
    }
    if ($secure) {
        $uri = $uri->withScheme('https');
    }
    return (string) $uri;
}

/**
 * Generates an asset URL for static content, taking the CDN into consideration
 *
 * @param string $path
 * @param bool   $secure
 *
 * @return string
 */
function cdn(string $path, bool $secure = true)
{
    $base = config('app.cdn_url', config('app.url'));
    # we get the base URL first
    $secure = app()->environment() === 'production' ? $secure : false;
    # on local, we turn secure mode off
    return custom_url($base, $path, null, $secure);
}

/**
 * @param string|array $path
 * @param array $parameters
 * @param bool  $secure
 *
 * @return string
 */
function web_url($path = null, array $parameters = [], bool $secure = true): string
{
    $base = config('app.url', 'http://localhost');
    # we get the base URL first
    return custom_url($base, $path, $parameters, $secure);
}

/**
 * @param null  $path
 * @param array $parameters
 * @param bool  $secure
 *
 * @return string
 */
function site_url($path = null, array $parameters = [], bool $secure = true): string
{
    $base = config('app.site_url', config('app.url'));
    # we get the base URL first
    return custom_url($base, $path, $parameters, $secure);
}

/**
 * @param DomainIssuance $subdomain
 * @param                $path
 * @param array          $parameters
 * @param bool           $secure
 *
 * @return string
 */
function url_from_domain_issuance(DomainIssuance $subdomain, $path = '', array $parameters = [], bool $secure = true): string
{
    $defaultDomain = 'dorcas.' . (app()->environment() === 'production' ? 'ng' : 'local');
    $domain = $subdomain->domain;
    $base = 'http://' . $subdomain->prefix . '.' . (empty($domain) ? $defaultDomain : $domain->domain);
    $secure = app()->environment() === 'production' ? $secure : false;
    # on local, we turn secure mode off
    return custom_url($base, $path, $parameters, $secure);
}

/**
 * @param Company $company
 * @param string  $path
 * @param array   $parameters
 * @param bool    $secure
 *
 * @return string
 */
function url_from_company(Company $company, $path = '', array $parameters = [], bool $secure = true): string
{
    $issuance = $company->domainIssuances->first();
    if (!empty($issuance)) {
        return url_from_domain_issuance($issuance, $path, $parameters, $secure);
    }
    $secure = app()->environment() === 'production' ? $secure : false;
    # on local, we turn secure mode off
    $hashId = app(\Hashids\Hashids::class);
    $prefix = $hashId->encode($company->id);
    $base = 'http://' . $prefix . '.dorcas.' . (app()->environment() === 'production' ? 'io' : 'local');
    return custom_url($base, $path, $parameters, $secure);
}

/**
 * Converts a validation exception to the appropriate human-understandable text.
 *
 * @param ValidationException $exception
 *
 * @return array
 */
function validation_errors_to_messages(ValidationException $exception)
{
    $dependentFieldChecks = [
        'required_if',
        'required_unless',
        'required_with',
        'required_with_all',
        'required_without',
        'required_without_all'
    ];
    # checks that have dependent fields
    $messages = [];
    $errors = [];
    foreach ($exception->validator->failed() as $field => $failures) {
        foreach ($failures as $rule => $data) {
            $errors[$field][$rule] = is_array($data) ? implode(', ', $data) : $data;
        }
    }
    foreach ($exception->errors() as $field => $failures) {
        $fieldErrors = $errors[$field] ?? [];
        # get the specific errors for the field -- we'll need this to get additional validation data
        foreach ($failures as $id) {
            $components = explode('.', $id);
            # split up the id
            $checkName = count($components) > 1 ? $components[1] : $components[0];
            # we parse out the check name from the id
            $errorKey = str_replace('_', '', title_case($checkName));
            # now we see if we got it
            $attributes = [
                'attribute' => $field,
                'values' => $fieldErrors[$errorKey],
                strtolower($checkName) => $fieldErrors[$errorKey]
            ];
            if (in_array($checkName, $dependentFieldChecks)) {
                # this field is one of those that has dependencies
                $split = explode(',', $fieldErrors[$errorKey]);
                $additional = ['other' => $split[0]];
                if (count($split) === 2) {
                    $additional['value'] = trim($split[1]);
                } elseif (count($split) > 2) {
                    unset($split[0]);
                    $additional['value'] = implode(',', $split);
                }
                $attributes = array_merge($attributes, $additional);
            }
            # we set the attributes
            if (isset($messages[$field]) && !is_array($messages[$field])) {
                $messages[$field] = (array) $messages[$field];
            }
            if (!empty($messages[$field]) && is_array($messages[$field])) {
                $messages[$field][] = trans($id, $attributes, 'en');
            } else {
                $messages[$field] = trans($id, $attributes, 'en');
            }
        }
    }
    return $messages;
}

/**
 * Converts an image at the specified path/url to a base64-encoded version.
 *
 * @param string $url   URL or path of the image file.
 *
 * @return null|string
 */
function image_to_base64(string $url)
{
    $specialFiles = ['svg' => 'svg+xml'];
    # an array of special extension to type list
    $type = pathinfo($url, PATHINFO_EXTENSION);
    $type = !array_key_exists($type, $specialFiles) ? $type : $specialFiles[$type];
    # update the type
    $encoded = 'data:image/'.$type.';base64,';
    if (($rawContent = file_get_contents($url)) === false) {
        return null;
    }
    return $encoded . base64_encode($rawContent);
}

if (!function_exists('config_path')) {
    /**
     * Returns the path where config files are stored.
     *
     * @param string|null $path
     *
     * @return string
     */
    function config_path(string $path = null)
    {
        $components = [base_path('config')];
        return implode(DIRECTORY_SEPARATOR, !empty($path) ? array_merge($components, [$path]) : $components);
    }
}

/**
 * Verifies that a valid client is sending this request
 *
 * @param \Illuminate\Http\Request $request
 *
 * @return bool
 */
function validate_api_client(\Illuminate\Http\Request $request): bool
{
    $client = (new ClientRepository())->find($request->input('client_id'));
    # find the OAuth client
    if (empty($client)) {
        throw new RecordNotFoundException('We could not identify your application client.');
    }
    if ($client->secret !== $request->input('client_secret')) {
        throw new \UnexpectedValueException(
            'The provided client_secret is not correct for the provided client.'
        );
    }
    return true;
}

/**
 * Verifies a Paystack transaction information, and returns a transaction object that can be saved to the database.
 *
 * @param string            $secretKey
 * @param string            $reference
 * @param Order $order
 *
 * @return array
 * @throws HttpException
 * @throws UnexpectedValueException
 * @throws Exception
 */
function payment_verify_paystack(string $secretKey, string $reference, Order $order)
{
    $paystack = new Paystack($secretKey);
    $response = $paystack->transaction->verify(['reference' => $reference]);
    if (!$response->status) {
        throw new \HttpException($response->message);
    }
    if ($order->id !== $response->data->metadata->cart_id) {
        throw new UnexpectedValueException('We could not verify that the transaction is for your current order.');
    }
    return [
        'channel' => 'paystack',
        'reference' => $reference,
        'amount' => $response->data->amount / 100,
        'response_code' => $response->data->status === 'success' ? '00' : 'EE',
        'response_description' => $response->data->gateway_response,
        'json_payload' => [
            'transaction_date' => $response->data->transaction_date,
            'fee' => ($response->data->fees / 100),
            'card' => [
                'last4' => $response->data->authorization->last4,
                'exp_month' => $response->data->authorization->exp_month,
                'exp_year' => $response->data->authorization->exp_year,
                'channel' => $response->data->authorization->channel,
            ]
        ],
        'is_successful' => $response->data->status === 'success'
    ];
}

/**
 * Creates the HTTP client for communicating with Rave.
 *
 * @param string $env
 *
 * @return \GuzzleHttp\Client
 */
function get_rave_http_client(string $env = 'test'): \GuzzleHttp\Client
{
    $url = strtolower($env) === 'live' ? 'https://api.ravepay.co' : 'https://ravesandboxapi.flutterwave.com';
    return new \GuzzleHttp\Client(['base_uri' => $url, RequestOptions::VERIFY => false]);
}

/**
 * Initialises the transaction.
 *
 * @param string $env
 * @param array  $configuration
 *
 * @return array
 */
function rave_init_transaction(string $env, array $configuration = []): array
{
    $response = get_rave_http_client($env)->post('/flwv3-pug/getpaidx/api/v2/hosted/pay', [
        RequestOptions::JSON => $configuration
    ]);
    # send the request
    $json = json_decode((string) $response->getBody(), true);
    return $json;
}

/**
 * ReQueries the server for the details of a transaction.
 *
 * @param string $env
 * @param string $secretKey
 * @param string $reference
 * @param Order  $order
 *
 * @return array
 */
function payment_verify_rave(string $env, string $secretKey, string $reference, Order $order): array
{
    $response = get_rave_http_client($env)->post('/flwv3-pug/getpaidx/api/xrequery', [
        RequestOptions::JSON => [
            'SECKEY' => $secretKey,
            'txref' => $reference,
            'include_payment_entity' => '1'
        ]
    ]);
    # send the request
    $json = json_decode((string) $response->getBody(), true);
    if (empty($json['data'])) {
        throw new RuntimeException('Could not verify the details of the payment. Contact support for assistance.');
    }
    return [
        'channel' => 'rave',
        'reference' => $reference,
        'amount' => $json['data']['amount'],
        'response_code' => $json['data']['chargecode'],
        'response_description' => '',
        'json_payload' => $json,
        'is_successful' => $json['data']['chargecode'] == '00' || $json['data']['chargecode'] == '0'
    ];
}

 function isAccountGrandFather($account_id) : bool {
    //returns true if the parameter account id is a grand father created account
    return  AccountingAccount::where(['id'=>$account_id,'parent_account_id'=>null])->exists();
}
function isNotLastBorn($account_id)  {
    //returns true if account is not a lastborn
    return  AccountingAccount::where('parent_account_id',$account_id)->exists();
}

function prefixGenerator(){
    //generates random prefix for the company
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    return substr(str_shuffle($permitted_chars), 0, 5);
}
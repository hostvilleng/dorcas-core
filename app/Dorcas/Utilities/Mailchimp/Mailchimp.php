<?php

namespace App\Dorcas\Utilities\Mailchimp;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * Wrapper for the MailChimp v3.0 API
 *
 * @package App\BrassPay\Utilities\Mailchimp
 * @link https://developer.mailchimp.com/documentation/mailchimp/guides/get-started-with-mailchimp-api-3/
 */
class Mailchimp
{
    /**
     * The HTTP scheme for the API
     */
    const SCHEME = 'https';

    /**
     * Base API URL for the client
     */
    const BASE_URL = '.api.mailchimp.com/3.0/';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $dataCentre;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $username;

    /**
     * @var Mailchimp
     */
    private static $instance = null;

    /**
     * MailChimp constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->dataCentre = (string) $config['dc'] ?? 'us16';
        $this->username = (string) $config['username'] ?? 'client';
        $this->apiKey = (string) $config['key'] ?? '';
        $this->httpClient = new Client([
            'base_uri' => self::SCHEME.'://'.$this->dataCentre.self::BASE_URL,
            RequestOptions::AUTH => [$this->username, $this->apiKey],
            RequestOptions::TIMEOUT => 60.0,
            RequestOptions::CONNECT_TIMEOUT => 60.0,
            RequestOptions::HTTP_ERRORS => false
        ]);
    }

    /**
     * Maintains a singleton instance of the class
     *
     * @param array $config
     *
     * @return Mailchimp
     */
    public static function instance(array $config = []): Mailchimp
    {
        if (self::$instance === null) {
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    /**
     * Makes a request to the MailChimp API
     *
     * @param string $endpoint
     * @param array  $data
     * @param string $method
     *
     * @return array|mixed
     */
    public function request(string $endpoint, array $data = [], string $method = 'GET')
    {
        $payloadKey = strtolower($method) === 'get' ? 'query' : 'json';
        $response = $this->httpClient->request($method, $endpoint, [
            $payloadKey => $data
        ]);
        if ($response->getStatusCode() >= 400) {
            # an error response of some form
            $body =  (string) $response->getBody();
            return [
                'error' => [
                    'code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase()
                ],
                'body' => !empty($body) ? json_decode($body, true) : $body
            ];
        }
        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Creates the subscriber id for identifying an email address against the API
     *
     * @param string $email
     *
     * @return string
     */
    private function getSubscriberId(string $email): string
    {
        return hash('md5', strtolower($email));
    }

    /**
     * Cleans the subscriber status, and guarantees it's one of the allowed values
     *
     * @param string      $status
     * @param string|null $default
     *
     * @return string
     */
    private function subscriberCleanStatus(string $status, string $default = null): string
    {
        $statuses = ['subscribed', 'pending', 'unsubscribed', 'cleaned'];
        $status = strtolower($status);
        return in_array($status, $statuses) ? $status : $default;
    }

    /**
     * Checks the subscription status for an email on a list
     * 
     * @param string $listId
     * @param string $email
     *
     * @return array|mixed
     * @link https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/
     */
    public function subscriberCheckStatus(string $listId, string $email)
    {
        $endpoint = 'lists/'.$listId.'/members/'.$this->getSubscriberId($email);
        return $this->request($endpoint);
    }

    /**
     * Adds a new subscriber to the list
     *
     * @param string $listId
     * @param string $email
     * @param string $status
     * @param array  $mergeFields
     *
     * @return array|mixed
     * @link https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/
     */
    public function subscriberAdd(string $listId, string $email, string $status, array $mergeFields = [])
    {
        $endpoint = 'lists/'.$listId.'/members/';
        $data = [
            'email_address' => $email,
            'status' => $this->subscriberCleanStatus($status, 'unsubscribed'),
            'merge_fields' => $mergeFields
        ];
        return $this->request($endpoint, $data, 'POST');
    }

    /**
     * Updates a subscribers details
     *
     * @param string      $listId
     * @param string      $email
     * @param string|null $status
     * @param array       $mergeFields
     *
     * @return array|mixed
     * @throws \InvalidArgumentException
     * @link https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/
     */
    public function subscriberUpdate(string $listId, string $email, string $status = null, array $mergeFields = [])
    {
        $endpoint = 'lists/'.$listId.'/members/'.$this->getSubscriberId($email);
        $data = [];
        if (!empty($status)) {
            $data['status'] = $this->subscriberCleanStatus($status, 'unsubscribed');
        }
        if (!empty($mergeFields)) {
            $data['merge_fields'] = $mergeFields;
        }
        if (empty($data)) {
            throw new \InvalidArgumentException('You have not passed any fields to be updated!');
        }
        return $this->request($endpoint, $data, 'PATCH');
    }

    /**
     * Deletes a subscriber from the list
     *
     * @param string $listId
     * @param string $email
     *
     * @return array|mixed
     * @link https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/
     */
    public function subscriberDelete(string $listId, string $email)
    {
        $endpoint = 'lists/'.$listId.'/members/'.$this->getSubscriberId($email);
        return $this->request($endpoint, [], 'DELETE');
    }
}
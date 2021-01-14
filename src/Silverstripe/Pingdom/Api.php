<?php

namespace Silverstripe\Pingdom;

use stdClass;

class Api
{
    const ENDPOINT = 'https://api.pingdom.com/api/3.1';

    /**
     * The Pingdom API key.
     *
     * @var string
     */
    private $apiKey;

    /**
     * Indicates whether requests should use gzip compression.
     *
     * @var bool
     */
    private $gzip;

    /**
     * @param string $api_key The Pingdom API key
     * @param bool   $gzip    false if responses from Pingdom should not use gzip compression
     */
    public function __construct($api_key, $gzip = true)
    {
        $this->apiKey = $api_key;
        $this->gzip = $gzip;
    }

    /**
     * Fetches the list of domains being monitored in Pingdom.
     *
     * @throws MissingCredentialsException
     *
     * @return array An array of domains, indexed by check ID
     */
    public function getDomains()
    {
        $domains = [];
        $checks = $this->getChecks();
        foreach ($checks as $check) {
            $domains[$check->id] = $check->hostname;
        }

        return $domains;
    }

    /**
     * Retrieves a list of checks.
     *
     * @param int $limit  Limits the number of returned checks to the specified quantity (max is 25000)
     * @param int $offset Offset for listing (requires limit)
     *
     * @throws MissingCredentialsException
     *
     * @return array An indexed array of checks
     */
    public function getChecks($limit = null, $offset = null)
    {
        $parameters = [];
        if (!empty($limit)) {
            $parameters['limit'] = $limit;
            if (!empty($offset)) {
                $parameters['offset'] = $offset;
            }
        }
        $data = $this->request('GET', 'checks', $parameters);

        return $data->checks;
    }

    /**
     * Retrieves detailed information about a specified check.
     *
     * @param int $check_id The ID of the check to retrieve
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return array An array of information about the check
     */
    public function getCheck($check_id)
    {
        $this->ensureParameters(['check_id' => $check_id], __METHOD__);
        $data = $this->request('GET', "checks/${check_id}");

        return $data->check;
    }

    /**
     * Adds a new check.
     *
     * @param array $check    An array representing the check to create. The only required properties
     *                        are "name" and "host", default values for the other properties will be
     *                        assumed if not explicitly provided.
     * @param array $defaults An array of default settings for the check
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return stdClass - the response object
     */
    public function addCheck(array $check, array $defaults = [])
    {
        $this->ensureParameters([
            'name' => $check['name'],
            'host' => $check['host'],
            'url' => $check['url'],
        ], __METHOD__);
        $check += $defaults;

        return $this->request('POST', 'checks', $check);
    }

    /**
     * Pauses a check.
     *
     * @param int $check_id The ID of the check to pause
     *
     * @throws MissingParameterException
     * @throws MissingCredentialsException
     *
     * @return string The returned response message
     */
    public function pauseCheck($check_id)
    {
        $this->ensureParameters(['check_id' => $check_id], __METHOD__);
        $check = [
            'paused' => true,
        ];

        return $this->modifyCheck($check_id, $check);
    }

    /**
     * Unpauses a check.
     *
     * @param int $check_id The ID of the check to pause
     *
     * @throws MissingParameterException
     * @throws MissingCredentialsException
     *
     * @return string The returned response message
     */
    public function unpauseCheck($check_id)
    {
        $this->ensureParameters(['check_id' => $check_id], __METHOD__);
        $check = [
            'paused' => false,
        ];

        return $this->modifyCheck($check_id, $check);
    }

    /**
     * Pauses multiple checks.
     *
     * @param array $check_ids An array of check IDs to pause
     *
     * @throws MissingParameterException
     * @throws MissingCredentialsException
     *
     * @return string The returned response message
     */
    public function pauseChecks($check_ids)
    {
        $this->ensureParameters(['check_ids' => $check_ids], __METHOD__);
        $parameters = [
            'paused' => true,
        ];

        return $this->modifyChecks($check_ids, $parameters);
    }

    /**
     * Unpauses multiple checks.
     *
     * @param array $check_ids An array of check IDs to unpause
     *
     * @throws MissingParameterException
     * @throws MissingCredentialsException
     *
     * @return string The returned response message
     */
    public function unpauseChecks($check_ids)
    {
        $this->ensureParameters(['check_ids' => $check_ids], __METHOD__);

        return $this->modifyChecks($check_ids, ['paused' => false]);
    }

    /**
     * Modifies a check.
     *
     * @param int   $check_id   The ID of the check to modify
     * @param array $parameters An array of settings by which to modify the check
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return string The returned response message
     */
    public function modifyCheck($check_id, $parameters)
    {
        $this->ensureParameters([
            'check_id' => $check_id,
            'parameters' => $parameters,
        ], __METHOD__);

        $data = $this->request('PUT', "checks/${check_id}", $parameters);

        return $data->message;
    }

    /**
     * Modifies multiple checks.
     *
     * Pingdom allows all checks to be modified at once when the "checkids"
     * parameter is not supplied but since that is a very destructive operation we
     * require the check IDs to be explicitly specified. See modifyAllChecks() if
     * you need to modify all checks at once.
     *
     * @param array $check_ids  An array of check IDs to modify
     * @param array $parameters An array of parameters by which to modify the given checks:
     *                          - paused: TRUE for paused; FALSE for unpaused.
     *                          - resolution: An integer specifying the check frequency.
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return string The returned response message
     */
    public function modifyChecks($check_ids, $parameters)
    {
        $this->ensureParameters([
            'check_ids' => $check_ids,
            'parameters' => $parameters,
        ], __METHOD__);
        $parameters['checkids'] = implode(',', $check_ids);
        $data = $this->request('PUT', 'checks', $parameters);

        return $data->message;
    }

    /**
     * Modifies all checks.
     *
     * This method can be used to modify all checks at once. Check modification by
     * this method is limited to adjusting the paused status and check frequency.
     * This is a relatively destructive operation so please be careful that you
     * intend to modify all checks before calling this method.
     *
     * @param array $parameters An array of parameters by which to modify the given checks:
     *                          - paused: TRUE for paused; FALSE for unpaused.
     *                          - resolution: An integer specifying the check frequency.
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return string The returned response message
     */
    public function modifyAllChecks($parameters)
    {
        $this->ensureParameters(['parameters' => $parameters], __METHOD__);
        $data = $this->request('PUT', 'checks', $parameters);

        return $data->message;
    }

    /**
     * Removes a check.
     *
     * @param int $check_id The ID of the check to remove
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return string The returned response message
     */
    public function removeCheck($check_id)
    {
        $this->ensureParameters(['check_id' => $check_id], __METHOD__);
        $data = $this->request('DELETE', "checks/${check_id}");

        return $data->message;
    }

    /**
     * Removes multiple checks.
     *
     * @param array $check_ids An array of check IDs to remove
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return string The returned response message
     */
    public function removeChecks(array $check_ids)
    {
        $this->ensureParameters(['check_ids' => $check_ids], __METHOD__);
        $check_ids = implode(',', $check_ids);
        $parameters = [
            'delcheckids' => $check_ids,
        ];
        $data = $this->request('DELETE', 'checks', $parameters);

        return $data->message;
    }

    /**
     * Fetches a report about remaining account credits.
     *
     * @throws MissingCredentialsException
     *
     * @return string The returned response message
     */
    public function getCredits()
    {
        $data = $this->request('GET', 'credits');

        return $data->credits;
    }

    /**
     * Fetches a list of actions (alerts) that have been generated.
     *
     * @throws MissingCredentialsException
     *
     * @return string The returned response message
     */
    public function getActions()
    {
        $data = $this->request('GET', 'actions');

        return $data->actions;
    }

    /**
     * Fetches the latest root cause analysis results for a specified check.
     *
     * @param int   $check_id   The ID of the check
     * @param array $parameters An array of parameters for the request
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return string
     */
    public function getAnalysis($check_id, $parameters = [])
    {
        $this->ensureParameters(['check_id' => $check_id], __METHOD__);
        $data = $this->request('GET', "analysis/${check_id}", $parameters);

        return $data->analysis;
    }

    /**
     * Fetches the raw root cause analysis for a specified check.
     *
     * @param int   $check_id    The ID of the check
     * @param int   $analysis_id The analysis ID
     * @param array $parameters  An array of parameters for the request
     *
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return stdClass The returned response message
     */
    public function getRawAnalysis($check_id, $analysis_id, $parameters = [])
    {
        $this->ensureParameters([
            'check_id' => $check_id,
            'analysis_id' => $analysis_id,
        ], __METHOD__);
        $data = $this->request('GET', "analysis/{$check_id}/{$analysis_id}", $parameters);

        return $data;
    }

    /**
     * Fetches all users.
     *
     * @throws MissingCredentialsException
     *
     * @return stdClass
     */
    public function getUsers()
    {
        $data = $this->request('GET', 'users');

        return $data->users;
    }

    /**
     * @throws MissingCredentialsException
     * @throws MissingParameterException
     *
     * @return stdClass
     */
    public function getSingle(array $check, array $defaults = [])
    {
        $this->ensureParameters([
            'host	' => $check['host'],
            'type' => $check['type'],
        ], __METHOD__);

        $check += $defaults;
        $data = $this->request('GET', 'single', $check);

        return $data->result;
    }

    /**
     * Checks that required parameters were provided.
     *
     * PHP only triggers a warning for missing parameters and continues with
     * program execution. To avoid calling the Pingdom API with known malformed
     * data, we throw an exception if we find that something required is missing.
     *
     * @param array  $parameters An array of parameters to check, keyed by parameter name with the parameter itself as the value
     * @param string $method     The calling method's name
     *
     * @throws MissingParameterException
     */
    public function ensureParameters($parameters, $method)
    {
        if (empty($parameters) || empty($method)) {
            throw new MissingParameterException(sprintf('%s called without required parameters.', __METHOD__));
        }
        foreach ($parameters as $parameter => $value) {
            if (!isset($value)) {
                throw new MissingParameterException(sprintf('Missing required %s parameter in %s', $parameter, $method));
            }
        }
    }

    /**
     * Makes a request to the Pingdom REST API.
     *
     * @param string $method     The HTTP request method e.g. GET, POST, and PUT.
     * @param string $resource   The resource location e.g. checks/{checkid}.
     * @param array  $parameters The request parameters, if any are required. This is used to build the URL query string.
     * @param array  $headers    Additional request headers, if any are required
     * @param mixed  $body       Data to use for the body of the request when using POST or PUT methods.
     *                           This can be a JSON string literal or something that json_encode() accepts.
     *
     * @throws MissingCredentialsException
     * @throws CurlErrorException
     *
     * @return stdClass An object containing the response data
     */
    public function request($method, $resource, $parameters = [], $headers = [], $body = null)
    {
        $handle = curl_init();

        if (!is_resource($handle)) {
            throw new CurlErrorException('curl_init() failed to create a resource');
        }

        $headers[] = 'Content-Type: application/json; charset=utf-8';

        if (empty($this->apiKey)) {
            throw new MissingCredentialsException('Missing Pingdom credentials. Please supply the api_key parameter.');
        }
        $headers[] = 'Authorization: Bearer '.$this->apiKey;
        if (!empty($body)) {
            if (!is_string($body)) {
                $body = json_encode($body);
            }
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
            $headers[] = 'Content-Length: '.strlen($body);
        }

        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($handle, CURLOPT_URL, $this->buildRequestUrl($resource, $parameters));
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_MAXREDIRS, 10);
        curl_setopt($handle, CURLOPT_USERAGENT, 'PingdomApi/1.0');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, 10);
        $gzip = !empty($this->gzip) ? 'gzip' : '';
        curl_setopt($handle, CURLOPT_ENCODING, $gzip);

        $response = curl_exec($handle);
        if (curl_errno($handle) > 0) {
            $curl_error = sprintf('Curl error: %s', curl_error($handle));
            $errno = curl_errno($handle);
            curl_close($handle);

            throw new CurlErrorException($curl_error, $errno);
        }

        $data = json_decode($response);
        $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $status_class = (int) floor($status / 100);
        if ($status_class === 4 || $status_class === 5) {
            $message = $this->getError($data, $status);
            switch ($status_class) {
                case 4:
                    throw new ClientErrorException(sprintf('Client error: %s', $message), $status);
                case 5:
                    throw new ServerErrorException(sprintf('Server error: %s', $message), $status);
            }
        }

        return $data;
    }

    /**
     * Builds the request URL.
     *
     * The Pingdom API requires boolean values to be transmitted as "true" and
     * "false" string representations. To preserve the convenience of using the
     * boolean types we will convert them here.
     *
     * @param string $resource   The resource path part of the URL, without leading or trailing slashes
     * @param array  $parameters An array of query string parameters to append to the URL
     *
     * @return string The fully-formed request URI
     */
    public function buildRequestUrl($resource, $parameters = [])
    {
        foreach ($parameters as $property => $value) {
            if (is_bool($value)) {
                $parameters[$property] = $value ? 'true' : 'false';
            }
        }
        $query = empty($parameters) ? '' : '?'.http_build_query($parameters, null, '&');

        return sprintf('%s/%s%s', self::ENDPOINT, $resource, $query);
    }

    /**
     * Gets the human-readable error message for a failed request.
     *
     * @param object $response_data The object containing the response data
     * @param int    $status        The HTTP status code
     *
     * @return string The error message
     */
    protected function getError($response_data, $status)
    {
        if (!empty($response_data->error)) {
            $error = $response_data->error;
            $message = sprintf(
                '%s %s: %s',
                $error->statuscode,
                $error->statusdesc,
                $error->errormessage
            );
        } else {
            $message = sprintf('Error code: %s. No reason was given by Pingdom for the error.', $status);
        }

        return $message;
    }

}

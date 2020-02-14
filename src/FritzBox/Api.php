<?php

namespace blacksenator\FritzBox;

use blacksenator\Http\ClientTrait;

/**
 * Extended version of https://github.com/andig/carddav2fb/blob/master/src/FritzBox/Api.php
 *
 * @author Andreas Götz <cpuidle@gmx.de>
 * @author Volker Püschel <knuffy@anasco.de>
 * @copyright Andreas Götz
 * @license MIT
 */

define('EMPTY_SID', '0000000000000000');

/**
 * Copyright (c) 2019 Andreas Götz
 * @license MIT
 */
class Api
{
    use ClientTrait;

    /** @var  string */
    protected $url;

    /** @var  string */
    protected $sid = EMPTY_SID;

    /**
     * Execute fb login
     *
     * @access public
     */
    public function __construct(string $url = 'https://fritz.box')
    {
        $this->url = rtrim($url, '/');
    }

    /**
     * Get session ID
     *
     * @return string SID
     */
    public function getSID(): string
    {
        return $this->sid;

    }

    /**
     * get data from FRITZ!Box websites
     * http://docs.guzzlephp.org/en/stable/quickstart.html#query-string-parameters
     * specified the query by using the query request option as an array
     *
     * @param string $path
     * @return string GET response
     * @throws \Exception
     */
    public function getData($path, array $define = null)
    {
        $url = $this->url . $path;
        $params = [
            'query' => [
                'sid' => $this->sid,
                $define,
            ]
        ];
        $resp = $this->getClient()->request('GET', $url, $params);

        return $resp->getBody();
    }

    /**
     * data upload to FRITZ!Box. Guzzle's multipart option does not work on
     * some FRITZ!Box interfaces.
     * http://docs.guzzlephp.org/en/stable/quickstart.html#uploading-data
     * Sending application/x-www-form-urlencoded POST requests requires
     * specified POST fields as an array in the form_params request options
     *
     * @param array $params form_params
     * @param string $path
     * @return string POST response
     * @throws \Exception
     */
    public function postData(array $params, $path): string
    {
        $url = $this->url . $path;
        // sid must be first parameter
        $params['form_params']['sid'] = $this->sid;
        $resp = $this->getClient()->request('POST', $url, $params);

        return (string)$resp->getBody();
    }

    /**
     * Multi-part file uploads
     * http://docs.guzzlephp.org/en/stable/request-options.html#multipart
     *
     * @param array $formFields
     * @param array $fileFields
     * @param string $path
     * @return string POST response
     * @throws \Exception
     */
    public function postFile(array $formFields, array $fileFields, string $path = '/cgi-bin/firmwarecfg'): string
    {
        $multipart = [];

        // sid must be first parameter
        $formFields = array_merge(['sid' => $this->sid], $formFields);

        foreach ($formFields as $key => $val) {
            $multipart[] = [
                'name' => $key,
                'contents' => $val,
            ];

        }

        foreach ($fileFields as $name => $file) {
            $multipart[] = [
                'name' => $name,
                'filename' => $file['filename'],
                'contents' => $file['content'],
                'headers' => [
                    'Content-Type' => $file['type'],
                ],
            ];
        }

        $url = $this->url . $path;
        $resp = $this->getClient()->request('POST', $url, [
            'multipart' => $multipart,
        ]);

        return (string)$resp->getBody();
    }

    /**
     * image upload to FRITZ!Box. Guzzle's multipart option does not work on
     * this interface. If this changes, this function can be deleted
     *
     * @param string $body
     * @return string POST response
     * @throws \Exception
     */
    public function postImage($body): string
    {
        $url = $this->url . '/cgi-bin/firmwarecfg';

        $resp = $this->getClient()->request('POST', $url, [
            'body' => $body,
        ]);

        return (string)$resp->getBody();
    }

    /**
     * Login, throws on failure
     *
     * @throws \Exception
     */
    public function login()
    {
        $url = $this->url . '/login_sid.lua';

        // read the current status
        $resp = $this->getClient()->request('GET', $url);
        $xml = simplexml_load_string((string)$resp->getBody());
        if ($xml->SID != EMPTY_SID) {
            $this->sid = (string)$xml->SID;
            return;
        }

        // the challenge-response magic, pay attention to the mb_convert_encoding()
        $response = $xml->Challenge . '-' . md5(mb_convert_encoding($xml->Challenge . '-' . $this->password, "UCS-2LE", "UTF-8"));

        // login
        $resp = $this->getClient()->request('GET', $url, [
            'query' => [
                'username' => $this->username,
                'response' => $response,
            ]
        ]);

        // retrieve SID from response
        $xml = simplexml_load_string((string)$resp->getBody());
        if ($xml->SID != EMPTY_SID) {
            $this->sid = (string)$xml->SID;
            return;
        }

        throw new \Exception('Login failed with an unknown response - please check credentials.');
    }
}

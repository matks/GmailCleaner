<?php

namespace Matks\GmailCleaner;

use Google_Client;
use Exception;

/**
 * APIAuthenticator
 *
 * Authenticate Gmail Cleaner to enable API usage
 * Authentication token is stored in session
 */
class APIAuthenticator
{
    /**
     * @var Google_Client
     */
    protected $client;

    /**
     * @param Google_Client $client
     */
    public function __construct(Google_Client $client)
    {
        $this->client = $client;
    }

    public function authenticate()
    {
        session_start();

        // when code is given, we can get the token
        if (isset($_GET['code'])) {
            $this->getTokenAndSetItInSessionThenRedirect();
        }

        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
        } else {
            $authUrl = $this->client->createAuthUrl();
        }

        if ($this->client->getAccessToken()) {
            $_SESSION['access_token'] = $this->client->getAccessToken();

            return true;
        }

        if (isset ($authUrl)) {
            echo '<a href=' . $authUrl . '>Connect</a>';
        }
    }

    private function getTokenAndSetItInSessionThenRedirect()
    {
        $this->client->authenticate($_GET['code']);

        $_SESSION['access_token'] = $this->client->getAccessToken();

        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }
}

<?php

namespace Matks\GmailCleaner;

use Google_Auth_AssertionCredentials;
use Google_Client;
use Google_Service_Gmail;
use Symfony\Component\Yaml\Yaml;

use Exception;

/**
 * GmailCleaner Launcher
 *
 * This class setups the GmailCleaner tool
 */
class Launcher
{

    /**
     * Main
     *
     * @param string $filepath
     */
    public static function main()
    {
        $launcher = new Launcher();
        $launcher->run();
    }

    public function run()
    {
        try {
            $gmailCleaner  = $this->setup();
            $configuration = $this->getConfiguration();

            $from   = new \DateTime($configuration['parameters']['from']);
            $to     = new \DateTime($configuration['parameters']['to']);
            $delete = true;
        } catch (Exception $e) {
            print($e->getMessage());
            die(1);
        }

        try {
            $gmailCleaner->clean($from, $to, $delete);
        } catch (Exception $e) {
            print($e->getMessage());
            unset($_SESSION['access_token']);
        }

        die(0);
    }

    /**
     * Construct GmailCleaner instance with its dependencies
     *
     * @return GmailCleaner
     */
    private function setup()
    {
        $configuration   = $this->getConfiguration();

        $googleAPIClient = $this->setupGoogleAPIClient($configuration['parameters']['google_api_key']);

        $authenticator = new APIAuthenticator($googleAPIClient);

        $isAuthenticated = $authenticator->authenticate();

        if (!$isAuthenticated) {
            die("<br/>Not authenticated");
        }

        $gmailService = new Google_Service_Gmail($googleAPIClient);
        $gmailCleaner = new GmailCleaner($gmailService);

        return $gmailCleaner;
    }

    /**
     * @return array
     */
    private function getConfiguration()
    {
        $configurationFilepath = $this->getConfigurationFilepath();

        if (!file_exists($configurationFilepath)) {
            throw new Exception("Configuration file $configurationFilepath does not exist");
        }

        $configuration = Yaml::parse($configurationFilepath);

        $this->validateConfiguration($configuration);

        return $configuration;
    }

    /**
     * @return string
     */
    private function getConfigurationFilepath()
    {
        return __DIR__ . '/../../../config/parameters.yml';
    }

    /**
     * @param array $configuration
     *
     * @throws Exception
     */
    private function validateConfiguration(array $configuration)
    {
        if (empty($configuration)) {
            throw new Exception('Empty configuration file');
        }

        if (!isset($configuration['parameters']['google_api_key'])) {
            throw new Exception('Missing configuration parameter: google_api_key');
        }

        if (!isset($configuration['parameters']['from'])) {
            throw new Exception('Missing configuration parameter: from');
        }

        if (!isset($configuration['parameters']['to'])) {
            throw new Exception('Missing configuration parameter: to');
        }
    }

    /**
     * @param string $apiKey
     *
     * @return Google_Client
     */
    private function setupGoogleAPIClient($jsonKeyFilename)
    {
        $client = new Google_Client();

        $requiredScopes = [
            Google_Service_Gmail::MAIL_GOOGLE_COM // 'deletion' scope
        ];

        $client->setApplicationName('Gmail Cleaner');
        $client->setScopes(implode(' ', $requiredScopes));

        $client->setAuthConfigFile($this->getKeyFilepath($jsonKeyFilename));
        $client->setAccessType('offline');

        return $client;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function getKeyFilepath($filename)
    {
        $configDirectory = __DIR__ . '/../../../config/auth/';
        $path            = realpath($configDirectory . $filename);

        if (!file_exists($path)) {
            throw new Exception("Key filepath is bad: $path");
        }

        if (!is_readable($path)) {
            throw new Exception("Given key file is not readable");
        }

        return $path;
    }
}

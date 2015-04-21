<?php

namespace Matks\GmailCleaner;

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
        $gmailCleaner = $this->setup();
        $gmailCleaner->clean();

        die(0);
    }

    /**
     * Construct GmailCleaner instance with its dependencies
     *
     * @return GmailCleaner
     */
    private function setup()
    {
        $configurationFilepath = $this->getConfigurationFilepath();

        if (!file_exists($configurationFilepath)) {
            throw new Exception("Configuration file $configurationFilepath does not exist");
        }

        $configuration = Yaml::parse($configurationFilepath);

        $this->validateConfiguration($configuration);

        $googleAPIClient = $this->setupGoogleAPIClient($configuration['parameters']['google_api_key']);
        $gmailService    = new Google_Service_Gmail($googleAPIClient);

        $gmailCleaner = new GmailCleaner($gmailService);

        return $gmailCleaner;
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
    }

    /**
     * @param string $apiKey
     *
     * @return Google_Client
     */
    private function setupGoogleAPIClient($apiKey)
    {
        $client = new Google_Client();
        $client->setApplicationName("Gmail Cleaner");
        $client->setDeveloperKey($apiKey);

        return $client;
    }
}

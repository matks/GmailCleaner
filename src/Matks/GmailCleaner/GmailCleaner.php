<?php

namespace Matks\GmailCleaner;

use Google_Service_Gmail;

/**
 * GmailCleaner
 */
class GmailCleaner
{
    /**
     * @var Google_Service_Gmail
     */
    protected $gmailService;

    /**
     * @param Google_Service_Gmail $gmailService
     */
    public function __construct(Google_Service_Gmail $gmailService)
    {
        $this->gmailService = $gmailService;
    }

    public function clean()
    {
    }
}

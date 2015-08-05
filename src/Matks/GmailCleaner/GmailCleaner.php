<?php

namespace Matks\GmailCleaner;

use Google_Service_Gmail;
use DateTime;
use Exception;

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

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param bool     $doDelete set to true to proceed to deletion
     */
    public function clean(DateTime $from, DateTime $to, $doDelete = false)
    {
        $query = $this->getGmailQueryFromTo($from, $to);

        try {

            $runs = 10;

            for ($i = 1; $i <= $runs; $i++) {

                echo "<br />Run " . $i;


                $msgList   = $this->findMessagesFromTo($from, $to);
                $msgLength = count($msgList);

                echo "<br />Found " . $msgLength . " messages from query " . $query;

                if (false === $doDelete) {
                    die();
                }

                $successCount = 0;
                foreach ($msgList as $message) {
                    $msgId = $message->getId();

                    $success = $this->sendMessageToTrash($msgId);

                    if ($success) {
                        $successCount++;
                    }
                }
                echo "<br />Deleted " . $successCount . " messages";
            }


        } catch (Exception $e) {
            print($e->getMessage());
            unset($_SESSION['access_token']);
        }
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return \Google_Service_Gmail_Message[]
     */
    private function findMessagesFromTo(DateTime $from, DateTime $to)
    {
        $query = $this->getGmailQueryFromTo($from, $to);

        $optParams = [
            'labelIds' => 'INBOX',
            'q'        => $query,
        ];
        $messages  = $this->gmailService->users_messages->listUsersMessages('me', $optParams);

        $list = $messages->getMessages();

        return $list;
    }

    /**
     * @param string|int $messageId
     *
     * @return bool
     */
    private function sendMessageToTrash($messageId)
    {
        try {
            $this->gmailService->users_messages->trash('me', $messageId);

            return true;
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return string
     */
    private function getGmailQueryFromTo(DateTime $from, DateTime $to)
    {
        $query = sprintf(
            'before:%s after:%s',
            $to->format('Y-m-d'),
            $from->format('Y-m-d')
        );

        return $query;
    }
}

<?php

namespace Remp\MailerModule\Hermes;

use Remp\MailerModule\Repository\LogsRepository;
use Remp\MailerModule\Repository\MailTypesRepository;
use Remp\MailerModule\Repository\UserSubscriptionsRepository;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\MessageInterface;

class UnsubscribeDroppedHandler implements HandlerInterface
{
    private $threshold = 3;

    private $logsRepository;

    private $mailTypesRepository;

    private $userSubscriptionsRepository;

    public function __construct(
        LogsRepository $logsRepository,
        MailTypesRepository $mailTypesRepository,
        UserSubscriptionsRepository $userSubscriptionsRepository
    ) {
        $this->logsRepository = $logsRepository;
        $this->mailTypesRepository = $mailTypesRepository;
        $this->userSubscriptionsRepository = $userSubscriptionsRepository;
    }
    
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
    }

    public function handle(MessageInterface $message): bool
    {
        $payload = $message->getPayload();

        $lastEmails = $this->logsRepository
            ->getEmailLogs($payload['email'])
            ->limit($this->threshold)
            ->fetchPairs('id', 'dropped_at');

        $droppedEmails = array_filter($lastEmails);
        if (count($droppedEmails) < $this->threshold) {
            return true;
        }

        $types = $this->mailTypesRepository->all();
        foreach ($types as $type) {
            if ($type->locked) {
                continue;
            }
            $this->userSubscriptionsRepository->unsubscribeEmail($type, $payload['email']);
        }

        return true;
    }
}

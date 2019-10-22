<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Notification;

use ErrorException;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use SubitoPuntoItAlert\Database\Model\Notification;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;
use SubitoPuntoItAlert\Exception\MissingSubscriptionException;

class Sender
{
    /**
     * @var WebPush
     */
    private $webPush;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var ResearchRepository
     */
    private $researchRepository;

    /**
     * @var AnnouncementRepository
     */
    private $announcementRepository;

    /**
     * @throws ErrorException
     */
    public function __construct()
    {
        $this->webPush = new WebPush($this->getAuth());
        $this->subscriptionRepository = New SubscriptionRepository();
        $this->researchRepository = New ResearchRepository();
        $this->announcementRepository = New AnnouncementRepository();
    }

    /**
     * @param Notification $notification
     * @throws ErrorException
     */
    public function send(Notification $notification): void
    {
        $endpoint = $notification->getEndpoint();
        try {
            $subscriptionModel = $this->subscriptionRepository->getSubscription($endpoint);
            $subscription = new Subscription(
                $endpoint,
                $subscriptionModel->getPublicKey(),
                $subscriptionModel->getAuthToken(),
                $subscriptionModel->getContentEncoding()
            );
            $this->webPush->sendNotification(
                $subscription,
                $notification->getMessage()
            );
        } catch (MissingSubscriptionException $e) {
            $this->researchRepository->deleteByEndpoint($endpoint);
            $this->announcementRepository->deleteByEndpoint($endpoint);
        }
    }

    /**
     * @throws ErrorException
     */
    public function flushReports(): void
    {
        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSubscriptionExpired()) {
                $this->subscriptionRepository->delete($endpoint);
                $this->researchRepository->deleteByEndpoint($endpoint);
                $this->announcementRepository->deleteByEndpoint($endpoint);
            }
        }
    }

    /**
     * @return array
     */
    private function getAuth(): array
    {
        return [
            'VAPID' => [
                'subject' => 'https://github.com/lamasfoker/subitopuntoitalert',
                'publicKey' => getenv('PUBLIC_KEY'),
                'privateKey' => getenv('PRIVATE_KEY'),
            ],
        ];
    }
}

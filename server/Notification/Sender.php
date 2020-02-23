<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Notification;

use ErrorException;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use SubitoPuntoItAlert\Database\SearchCriteria;
use SubitoPuntoItAlert\Database\Model\Notification;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;
use SubitoPuntoItAlert\Exception\InvalidFilterConditionException;
use SubitoPuntoItAlert\Exception\InvalidFilterParameterException;
use SubitoPuntoItAlert\Exception\NoSuchEntityException;

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
     * @throws InvalidFilterConditionException
     * @throws InvalidFilterParameterException
     */
    public function send(Notification $notification): void
    {
        $endpoint = $notification->getEndpoint();
        try {
            $subscriptionModel = $this->subscriptionRepository->getByEndpoint($endpoint);
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
        } catch (NoSuchEntityException $e) {
            $searchCriteria = new SearchCriteria();
            $searchCriteria->setParameterName('endpoint')
                ->setCondition('eq')
                ->setParameterValue($endpoint);
            $this->researchRepository->delete($searchCriteria);
            $this->announcementRepository->delete($searchCriteria);
        }
    }

    /**
     * @throws ErrorException
     * @throws InvalidFilterConditionException
     * @throws InvalidFilterParameterException
     */
    public function flushReports(): void
    {
        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSubscriptionExpired()) {
                $searchCriteria = new SearchCriteria();
                $searchCriteria->setParameterName('endpoint')
                    ->setCondition('eq')
                    ->setParameterValue($endpoint);
                $this->subscriptionRepository->delete($searchCriteria);
                $this->researchRepository->delete($searchCriteria);
                $this->announcementRepository->delete($searchCriteria);
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

<?php declare(strict_types=1);

namespace ExampleEventEntity\Core\Example\Events;

use ExampleEventEntity\Core\Example\ExampleDefinition;
use ExampleEventEntity\Core\Example\ExampleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\MailActionInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class ExampleEvent extends Event implements MailActionInterface
{
    public const EVENT_NAME = 'example.event';

    /**
     * @var SalesChannelContext
     */
    private $context;

    /**
     * @var ExampleEntity
     */
    private $eventEntity;

    public function __construct(SalesChannelContext $context, ExampleEntity $eventEntity)
    {
        $this->context = $context;
        $this->eventEntity = $eventEntity;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('myEntity', new EntityType(ExampleDefinition::class));
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    /**
     * This getter is needed to provide the data
     * See: MailSendSubscriber::getTemplateData
     */
    public function getMyEntity(): ExampleEntity
    {
        return $this->eventEntity;
    }

    public function getMailStruct(): MailRecipientStruct
    {
        return new MailRecipientStruct(['test@example.com' => 'Max']);
    }

    public function getSalesChannelId(): ?string
    {
        return $this->context->getSalesChannel()->getId();
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }
}

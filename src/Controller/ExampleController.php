<?php declare(strict_types=1);

namespace ExampleEventEntity\Controller;

use ExampleEventEntity\Core\Example\Events\ExampleEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @RouteScope(scopes={"storefront"})
 */
class ExampleController extends StorefrontController
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EntityRepositoryInterface
     */
    private $exampleRepository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityRepositoryInterface $exampleRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->exampleRepository = $exampleRepository;
    }

    /**
     * @Route("/example/send", name="frontend.example.send_event", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     */
    public function sendMyEvent(SalesChannelContext $context): Response
    {
        // fetch data for the example event
        $exampleEntities = $this->exampleRepository->search(new Criteria(), $context->getContext());

        // dispatch example event
        $exampleEvent = new ExampleEvent($context, $exampleEntities->first());
        $this->eventDispatcher->dispatch($exampleEvent);

        // redirect to home
        return $this->redirect('/');
    }
}

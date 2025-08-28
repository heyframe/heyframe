<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Storer;

use HeyFrame\Core\Checkout\Document\DocumentCollection;
use HeyFrame\Core\Checkout\Document\DocumentDefinition;
use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\A11yRenderedDocumentAware;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-type A11yDocument array{documentId: string, deepLinkCode: string, fileExtension: string|null}
 */
class A11yRenderedDocumentStorer extends FlowStorer
{
    /**
     * @internal
     *
     * @param EntityRepository<DocumentCollection> $documentRepository
     */
    public function __construct(
        private readonly EntityRepository $documentRepository,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function store(FlowEventAware $event, array $stored): array
    {
        if (!$event instanceof A11yRenderedDocumentAware || isset($stored[A11yRenderedDocumentAware::A11Y_DOCUMENT_IDS])) {
            return $stored;
        }

        $stored[A11yRenderedDocumentAware::A11Y_DOCUMENT_IDS] = $event->getA11yDocumentIds();

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(A11yRenderedDocumentAware::A11Y_DOCUMENT_IDS)) {
            return;
        }

        $storable->setData(A11yRenderedDocumentAware::A11Y_DOCUMENT_IDS, $storable->getStore(A11yRenderedDocumentAware::A11Y_DOCUMENT_IDS));

        $storable->lazy(
            A11yRenderedDocumentAware::A11Y_DOCUMENTS,
            $this->lazyLoad(...)
        );
    }

    /**
     * @return A11yDocument[]
     */
    private function lazyLoad(StorableFlow $storableFlow): array
    {
        $ids = $storableFlow->getStore(A11yRenderedDocumentAware::A11Y_DOCUMENT_IDS);
        if (!\is_array($ids) || empty($ids)) {
            return [];
        }

        $criteria = new Criteria($ids);

        return $this->loadA11yDocuments($criteria, $storableFlow->getContext());
    }

    /**
     * @return A11yDocument[]
     */
    private function loadA11yDocuments(Criteria $criteria, Context $context): array
    {
        $criteria->addAssociation('documentA11yMediaFile');

        $event = new BeforeLoadStorableFlowDataEvent(
            DocumentDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        $documents = $this->documentRepository
            ->search($criteria, $context)
            ->getEntities();

        $a11yDocuments = [];
        foreach ($documents as $document) {
            if ($document->getDocumentA11yMediaFile() === null) {
                continue;
            }

            $a11yDocuments[] = [
                'documentId' => $document->getId(),
                'deepLinkCode' => $document->getDeepLinkCode(),
                'fileExtension' => $document->getDocumentA11yMediaFile()->getFileExtension(),
            ];
        }

        return $a11yDocuments;
    }
}

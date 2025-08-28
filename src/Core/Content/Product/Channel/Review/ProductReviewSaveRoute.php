<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Review;

use HeyFrame\Core\Checkout\Customer\Service\EmailIdnConverter;
use HeyFrame\Core\Content\Product\Aggregate\ProductReview\ProductReviewCollection;
use HeyFrame\Core\Content\Product\Channel\Review\Event\ReviewFormEvent;
use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityNotExists;
use HeyFrame\Core\Framework\Event\EventData\MailRecipientStruct;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\Framework\Validation\Exception\ConstraintViolationException;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\NoContentResponse;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('after-sales')]
class ProductReviewSaveRoute extends AbstractProductReviewSaveRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductReviewCollection> $repository
     */
    public function __construct(
        private readonly EntityRepository $repository,
        private readonly DataValidator $validator,
        private readonly SystemConfigService $config,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getDecorated(): AbstractProductReviewSaveRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/product/{productId}/review', name: 'store-api.product-review.save', methods: ['POST'], defaults: ['_loginRequired' => true])]
    public function save(string $productId, RequestDataBag $data, ChannelContext $context): NoContentResponse
    {
        $channelId = $context->getChannelId();
        if (!$this->config->getBool('core.listing.showReview', $channelId)) {
            throw ProductException::reviewNotActive();
        }

        $customer = $context->getCustomer();
        \assert($customer !== null);

        $customerId = $customer->getId();

        EmailIdnConverter::encodeDataBag($data);
        if (!$data->has('name')) {
            $data->set('name', $customer->getFirstName());
        }

        if (!$data->has('lastName')) {
            $data->set('lastName', $customer->getLastName());
        }

        if (!$data->has('email')) {
            $data->set('email', $customer->getEmail());
        }

        $data->set('customerId', $customerId);
        $data->set('productId', $productId);
        $this->validate($data, $context->getContext());

        $review = [
            'productId' => $productId,
            'customerId' => $customerId,
            'channelId' => $channelId,
            'languageId' => $context->getLanguageId(),
            'externalUser' => $data->get('name'),
            'externalEmail' => $data->get('email'),
            'title' => $data->get('title'),
            'content' => $data->get('content'),
            'points' => $data->get('points'),
            'status' => false,
        ];

        if ($data->get('id')) {
            $review['id'] = $data->get('id');
        }

        $this->repository->upsert([$review], $context->getContext());

        $mail = $review['externalEmail'];
        $mail = \is_string($mail) ? $mail : '';
        $event = new ReviewFormEvent(
            $context->getContext(),
            $channelId,
            new MailRecipientStruct([$mail => $review['externalUser'] . ' ' . $data->get('lastName')]),
            $data,
            $productId,
            $customerId
        );

        $this->eventDispatcher->dispatch(
            $event,
            ReviewFormEvent::EVENT_NAME
        );

        return new NoContentResponse();
    }

    private function validate(DataBag $data, Context $context): void
    {
        $definition = new DataValidationDefinition('product.create_rating');

        $definition->add('name', new NotBlank());
        $definition->add('title', new NotBlank(), new Length(min: 5));
        $definition->add('content', new NotBlank(), new Length(min: 40));

        $definition->add('points', new GreaterThanOrEqual(1), new LessThanOrEqual(5));

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerId', $data->get('customerId')));

        if ($data->get('id')) {
            $criteria->addFilter(new EqualsFilter('id', $data->get('id')));

            $definition->add('id', new EntityExists(
                entity: 'product_review',
                context: $context,
                criteria: $criteria,
            ));
        } else {
            $criteria->addFilter(new EqualsFilter('productId', $data->get('productId')));

            $definition->add('customerId', new EntityNotExists(
                entity: 'product_review',
                context: $context,
                criteria: $criteria,
                primaryProperty: 'customerId',
            ));
        }

        $this->validator->validate($data->all(), $definition);

        $violations = $this->validator->getViolations($data->all(), $definition);

        if (!$violations->count()) {
            return;
        }

        throw new ConstraintViolationException($violations, $data->all());
    }
}

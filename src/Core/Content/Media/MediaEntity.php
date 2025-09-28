<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use HeyFrame\Core\Content\Media\Aggregate\MediaTranslation\MediaTranslationCollection;
use HeyFrame\Core\Content\Media\MediaType\MediaType;
use HeyFrame\Core\Content\Media\MediaType\SpatialObjectType;
use HeyFrame\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductMedia\ProductMediaCollection;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Tag\TagCollection;
use HeyFrame\Core\System\User\UserCollection;
use HeyFrame\Core\System\User\UserEntity;

/**
 * @phpstan-type MediaConfig array{'spatialObject': array{'arReady': bool, 'arPlacement': string}}
 */
#[Package('discovery')]
class MediaEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected ?string $userId = null;

    protected ?string $mimeType = null;

    protected ?string $fileExtension = null;

    protected ?int $fileSize = null;

    protected ?string $title = null;

    protected ?string $metaDataRaw = null;

    /**
     * @internal
     */
    protected ?string $mediaTypeRaw = null;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $metaData = null;

    protected ?MediaType $mediaType = null;

    protected ?\DateTimeInterface $uploadedAt = null;

    protected ?string $alt = null;

    protected string $url = '';

    protected ?string $fileName = null;

    protected ?UserEntity $user = null;

    protected ?MediaTranslationCollection $translations = null;

    protected ?ProductMediaCollection $productMedia = null;

    protected ?UserCollection $avatarUsers = null;

    protected ?CustomerCollection $avatarCustomers = null;

    protected ?MediaThumbnailCollection $thumbnails = null;

    protected ?string $mediaFolderId = null;

    protected ?MediaFolderEntity $mediaFolder = null;

    protected bool $hasFile = false;

    protected bool $private = false;

    protected ?PropertyGroupOptionCollection $propertyGroupOptions = null;

    protected ?TagCollection $tags = null;

    /**
     * @internal
     */
    protected ?string $thumbnailsRo = null;

    protected ?string $path = null;

    protected ?PaymentMethodCollection $paymentMethods = null;

    protected ?ProductConfiguratorSettingCollection $productConfiguratorSettings = null;

    protected ?OrderLineItemCollection $orderLineItems = null;

    /**
     * @experimental stableVersion:v6.8.0 feature:SPATIAL_BASES
     *
     * @var MediaConfig|null
     */
    protected ?array $config = null;

    /**
     * @internal
     */
    protected ?string $fileHash = null;

    public function get(string $property)
    {
        if ($property === 'hasFile') {
            return $this->hasFile();
        }

        return parent::get($property);
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(string $fileExtension): void
    {
        $this->fileExtension = $fileExtension;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    /**
     * @param array<string, mixed> $metaData
     */
    public function setMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function getMediaType(): ?MediaType
    {
        return $this->mediaType;
    }

    public function setMediaType(MediaType $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeInterface $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): void
    {
        $this->alt = $alt;
    }

    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): void
    {
        $this->user = $user;
    }

    public function getTranslations(): ?MediaTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(MediaTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProductMedia(): ?ProductMediaCollection
    {
        return $this->productMedia;
    }

    public function setProductMedia(ProductMediaCollection $productMedia): void
    {
        $this->productMedia = $productMedia;
    }

    public function getAvatarUsers(): ?UserCollection
    {
        return $this->avatarUsers;
    }

    public function setAvatarUsers(UserCollection $avatarUsers): void
    {
        $this->avatarUsers = $avatarUsers;
    }

    public function getThumbnails(): ?MediaThumbnailCollection
    {
        return $this->thumbnails;
    }

    public function setThumbnails(MediaThumbnailCollection $thumbnailCollection): void
    {
        $this->thumbnails = $thumbnailCollection;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function hasFile(): bool
    {
        $hasFile = $this->mimeType !== null && $this->fileExtension !== null && $this->fileName !== null;

        return $this->hasFile = $hasFile || $this->path !== null;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getFileNameIncludingExtension(): ?string
    {
        if ($this->fileName === null || $this->fileExtension === null) {
            return null;
        }

        return \sprintf('%s.%s', $this->fileName, $this->fileExtension);
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getMediaFolderId(): ?string
    {
        return $this->mediaFolderId;
    }

    public function setMediaFolderId(string $mediaFolderId): void
    {
        $this->mediaFolderId = $mediaFolderId;
    }

    public function getMediaFolder(): ?MediaFolderEntity
    {
        return $this->mediaFolder;
    }

    public function setMediaFolder(MediaFolderEntity $mediaFolder): void
    {
        $this->mediaFolder = $mediaFolder;
    }

    public function getPropertyGroupOptions(): ?PropertyGroupOptionCollection
    {
        return $this->propertyGroupOptions;
    }

    public function setPropertyGroupOptions(PropertyGroupOptionCollection $propertyGroupOptions): void
    {
        $this->propertyGroupOptions = $propertyGroupOptions;
    }

    public function getMetaDataRaw(): ?string
    {
        return $this->metaDataRaw;
    }

    public function setMetaDataRaw(string $metaDataRaw): void
    {
        $this->metaDataRaw = $metaDataRaw;
    }

    /**
     * @internal
     */
    public function getMediaTypeRaw(): ?string
    {
        $this->checkIfPropertyAccessIsAllowed('mediaTypeRaw');

        return $this->mediaTypeRaw;
    }

    /**
     * @internal
     */
    public function setMediaTypeRaw(string $mediaTypeRaw): void
    {
        $this->mediaTypeRaw = $mediaTypeRaw;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @internal
     */
    public function getThumbnailsRo(): ?string
    {
        $this->checkIfPropertyAccessIsAllowed('thumbnailsRo');

        return $this->thumbnailsRo;
    }

    /**
     * @internal
     */
    public function setThumbnailsRo(string $thumbnailsRo): void
    {
        $this->thumbnailsRo = $thumbnailsRo;
    }

    public function getPaymentMethods(): ?PaymentMethodCollection
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods(PaymentMethodCollection $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        unset($data['metaDataRaw'], $data['mediaTypeRaw']);
        $data['hasFile'] = $this->hasFile();

        return $data;
    }

    public function getProductConfiguratorSettings(): ?ProductConfiguratorSettingCollection
    {
        return $this->productConfiguratorSettings;
    }

    public function setProductConfiguratorSettings(ProductConfiguratorSettingCollection $productConfiguratorSettings): void
    {
        $this->productConfiguratorSettings = $productConfiguratorSettings;
    }

    public function getOrderLineItems(): ?OrderLineItemCollection
    {
        return $this->orderLineItems;
    }

    public function setOrderLineItems(OrderLineItemCollection $orderLineItems): void
    {
        $this->orderLineItems = $orderLineItems;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    public function hasPath(): bool
    {
        return $this->path !== null;
    }

    public function getPath(): string
    {
        return $this->path ?? '';
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @experimental stableVersion:v6.8.0 feature:SPATIAL_BASES
     *
     * @return MediaConfig|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * @experimental stableVersion:v6.8.0 feature:SPATIAL_BASES
     *
     * @param MediaConfig|null $configuration
     */
    public function setConfig(?array $configuration): void
    {
        $this->config = $configuration;
    }

    /**
     * @experimental stableVersion:v6.8.0 feature:SPATIAL_BASES
     */
    public function isSpatialObject(): bool
    {
        return $this->mediaType instanceof SpatialObjectType;
    }

    /**
     * @internal
     */
    public function getFileHash(): ?string
    {
        return $this->fileHash;
    }

    public function getAvatarCustomers(): ?CustomerCollection
    {
        return $this->avatarCustomers;
    }

    public function setAvatarCustomers(?CustomerCollection $avatarCustomers): void
    {
        $this->avatarCustomers = $avatarCustomers;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Maintenance\Channel\Command;

use HeyFrame\Core\Framework\Api\Util\AccessKeyHelper;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\WriteException;
use HeyFrame\Core\Framework\Test\TestCaseHelper\ReflectionHelper;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\WriteConstraintViolationException;
use HeyFrame\Core\Maintenance\Channel\Command\ChannelCreateCommand;
use HeyFrame\Core\Maintenance\Channel\Service\ChannelCreator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @internal
 */
#[CoversClass(ChannelCreateCommand::class)]
class ChannelCreateCommandTest extends TestCase
{
    /**
     * @param array<string, mixed> $inputMockValues
     */
    #[DataProvider('dataProviderTestExecuteSuccess')]
    public function testExecuteSuccess(array $inputMockValues): void
    {
        $accessKey = AccessKeyHelper::generateAccessKey('channel');

        $channelCreatorMock = $this->createMock(ChannelCreator::class);
        $channelCreatorMock->method('createChannel')
            ->willReturn($accessKey);

        $channelCreateCmd = new ChannelCreateCommand($channelCreatorMock);

        $refMethod = ReflectionHelper::getMethod(ChannelCreateCommand::class, 'execute');

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getOption')
            ->willReturnOnConsecutiveCalls(...array_values($inputMockValues));

        $outputMock = $this->createMock(OutputInterface::class);

        $result = $refMethod->invoke($channelCreateCmd, $inputMock, $outputMock);

        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @param array<string, mixed> $inputMockValues
     */
    #[DataProvider('dataProviderTestExecuteFailure')]
    public function testExecuteFailure(array $inputMockValues): void
    {
        $constraintViolationMock = $this->createMock(ConstraintViolationInterface::class);
        $constraintViolationMock->method('getPropertyPath')
            ->willReturn('Dummy');

        $constraintViolationMock->method('getMessage')
            ->willReturn('Dummy Message');

        $constraintViolationListMock = new ConstraintViolationList([$constraintViolationMock]);

        $writeConstraintViolationExceptionMock = $this->createMock(WriteConstraintViolationException::class);
        $writeConstraintViolationExceptionMock->method('getViolations')
            ->willReturn($constraintViolationListMock);

        $writeExceptionMock = $this->createMock(WriteException::class);
        $writeExceptionMock->method('getExceptions')
            ->willReturn([$writeConstraintViolationExceptionMock]);

        $channelCreatorMock = $this->createMock(ChannelCreator::class);
        $channelCreatorMock->method('createChannel')
            ->willThrowException($writeExceptionMock);

        $channelCreateCmd = new ChannelCreateCommand($channelCreatorMock);

        $refMethod = ReflectionHelper::getMethod(ChannelCreateCommand::class, 'execute');

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getOption')
            ->willReturnOnConsecutiveCalls(...array_values($inputMockValues));

        $outputMock = $this->createMock(OutputInterface::class);

        $result = $refMethod->invoke($channelCreateCmd, $inputMock, $outputMock);

        static::assertSame(Command::SUCCESS, $result);
    }

    public static function dataProviderTestExecuteSuccess(): \Generator
    {
        yield 'Test execute success' => [
            'inputMockValues' => [
                'id' => Uuid::randomHex(),
                'typeId' => Uuid::randomHex(),
                'name' => 'Headless',
                'languageId' => Uuid::randomHex(),
                'currencyId' => Uuid::randomHex(),
                'snippetSetId' => Uuid::randomHex(),
                'paymentMethodId' => Uuid::randomHex(),
                'customerGroupId' => Uuid::randomHex(),
                'navigationId' => Uuid::randomHex(),
            ],
        ];
    }

    public static function dataProviderTestExecuteFailure(): \Generator
    {
        yield 'Test execute failure' => [
            'inputMockValues' => [
                'id' => Uuid::randomHex(),
                'typeId' => Uuid::randomHex(),
                'name' => 'Headless',
                'languageId' => Uuid::randomHex(),
                'currencyId' => Uuid::randomHex(),
                'snippetSetId' => Uuid::randomHex(),
                'paymentMethodId' => Uuid::randomHex(),
                'shippingMethodId' => Uuid::randomHex(),
                'customerGroupId' => Uuid::randomHex(),
                'navigationCategoryId' => Uuid::randomHex(),
            ],
        ];
    }
}

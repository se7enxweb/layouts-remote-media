<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Parameters\ParameterType;

use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType;
use Netgen\Layouts\RemoteMedia\Tests\Validator\RemoteMediaValidatorFactory;
use Netgen\Layouts\Tests\Parameters\ParameterType\ParameterTypeTestTrait;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validation;

final class RemoteMediaTypeTest extends TestCase
{
    use ParameterTypeTestTrait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\API\ProviderInterface
     */
    private MockObject $providerMock;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->type = new RemoteMediaType();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType::getIdentifier
     */
    public function testGetIdentifier(): void
    {
        self::assertSame('remote_media', $this->type::getIdentifier());
    }

    /**
     * @param mixed[] $options
     * @param mixed[] $resolvedOptions
     *
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType::configureOptions
     *
     * @dataProvider validOptionsDataProvider
     */
    public function testValidOptions(array $options, array $resolvedOptions): void
    {
        $parameter = $this->getParameterDefinition($options);
        self::assertSame($resolvedOptions, $parameter->getOptions());
    }

    /**
     * @param mixed[] $options
     *
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType::configureOptions
     *
     * @dataProvider invalidOptionsDataProvider
     */
    public function testInvalidOptions(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getParameterDefinition($options);
    }

    /**
     * Provider for testing valid parameter attributes.
     *
     * @return mixed[]
     */
    public static function validOptionsDataProvider(): array
    {
        return [
            [
                [],
                [
                    'allowed_types' => [],
                ],
            ],
            [
                [
                    'allowed_types' => ['image'],
                ],
                [
                    'allowed_types' => ['image'],
                ],
            ],
        ];
    }

    /**
     * Provider for testing invalid parameter attributes.
     *
     * @return mixed[]
     */
    public static function invalidOptionsDataProvider(): array
    {
        return [
            [
                [
                    'undefined_value' => 'Value',
                ],
            ],
        ];
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType::getValueConstraints
     */
    public function testValidationValid(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with(self::identicalTo('upload|image|folder/test_resource'))
            ->willReturn(new RemoteResource([
                'type' => 'image',
                'remoteId' => 'upload|image|folder/test_resource',
                'url' => 'https://cloudinary.com/test/upload/folder/test_resource',
            ]));

        $parameter = $this->getParameterDefinition([], true);
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RemoteMediaValidatorFactory($this->providerMock))
            ->getValidator();

        $errors = $validator->validate('image|folder|upload%7Cimage%7Cfolder%2Ftest_resource', $this->type->getConstraints($parameter, 'image|folder|test_resource'));
        self::assertCount(0, $errors);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType::getValueConstraints
     */
    public function testValidationValidWithNonRequiredValue(): void
    {
        $this->providerMock
            ->expects(self::never())
            ->method('loadByRemoteId');

        $parameter = $this->getParameterDefinition([], false);
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RemoteMediaValidatorFactory($this->providerMock))
            ->getValidator();

        $errors = $validator->validate(null, $this->type->getConstraints($parameter, null));
        self::assertCount(0, $errors);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType::getValueConstraints
     */
    public function testValidationInvalid(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with(self::identicalTo('upload|image|folder/test_resource'))
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|folder/test_resource'));

        $parameter = $this->getParameterDefinition([], true);
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RemoteMediaValidatorFactory($this->providerMock))
            ->getValidator();

        $errors = $validator->validate('image|folder|upload%7Cimage%7Cfolder%2Ftest_resource', $this->type->getConstraints($parameter, 'image|folder|test_resource'));

        self::assertNotCount(0, $errors);
    }

    /**
     * @param mixed $value
     *
     * @covers \Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType::isValueEmpty
     *
     * @dataProvider emptyDataProvider
     */
    public function testIsValueEmpty($value, bool $isEmpty): void
    {
        self::assertSame($isEmpty, $this->type->isValueEmpty(new ParameterDefinition(), $value));
    }

    /**
     * @return mixed[]
     */
    public static function emptyDataProvider(): array
    {
        return [
            [null, true],
            [
                new RemoteResource([
                    'type' => 'image',
                    'remoteId' => 'upload|image|folder/test_resource',
                    'url' => 'https://cloudinary.com/test/upload/folder/test_resource',
                ]),
                false
            ],
        ];
    }
}

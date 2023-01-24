<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Validator;

use Netgen\Layouts\RemoteMedia\Validator\Constraint\RemoteMedia;
use Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator;
use Netgen\Layouts\Tests\TestCase\ValidatorTestCase;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class RemoteMediaValidatorTest extends ValidatorTestCase
{
    private MockObject $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new RemoteMedia();
    }

    public function getValidator(): ConstraintValidatorInterface
    {
        $this->provider = $this->createMock(ProviderInterface::class);

        return new RemoteMediaValidator($this->provider);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateValid(): void
    {
        $this->provider
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with(self::identicalTo('upload|image|folder/test_resource'))
            ->willReturn(new RemoteResource([
                'type' => RemoteResource::TYPE_IMAGE,
                'remoteId' => 'upload|image|folder/test_resource',
                'url' => 'https://cloudinary.com/test/upload/image/test_resource',
            ]));

        $this->assertValid(true, 'upload||image||folder|test_resource');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateNull(): void
    {
        $this->provider
            ->expects(self::never())
            ->method('loadFromRemote');

        $this->assertValid(true, null);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateNonExisting(): void
    {
        $this->provider
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with(self::identicalTo('upload|image|folder/test_resource2'))
            ->willThrowException(new RemoteResourceNotFoundException('upload|image|folder/test_resource2'));

        $this->assertValid(false, 'upload||image||folder|test_resource2');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\\Layouts\\RemoteMedia\\Validator\\Constraint\\RemoteMedia", "Symfony\\Component\\Validator\\Constraints\\NotBlank" given');

        $this->constraint = new NotBlank();
        $this->assertValid(true, 'value');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "array" given');

        $this->assertValid(true, []);
    }
}

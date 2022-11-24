<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\Configuration::addCacheConfiguration
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testConfigurationValid(): void
    {
        $this->assertConfigurationIsValid(
            [
                'netgen_layouts_remote_media' => [
                    'cache' => [
                        'pool' => 'cache.app',
                        'ttl' => 7200,
                    ],
                ],
            ],
        );
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}

<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_shift;
use function count;
use function explode;
use function implode;
use function json_decode;
use function json_encode;
use function mb_strlen;
use function mb_substr;
use function sprintf;

use const PHP_EOL;

class MigrateDataV1ToV2Command extends Command
{
    private const REMOTE_MEDIA_BLOCK_DEFINITION = 'remote_media';

    private const REMOTE_MEDIA_ITEM_DEFINITION = 'remote_media';

    private const REMOTE_MEDIA_BLOCK_VALUE_PARAMETER_NAME = 'remote_media';

    private const REMOTE_MEDIA_LINK_PREFIX = 'remote-media';

    protected Connection $connection;

    private OutputInterface $output;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('netgen-layouts:remote-media:migrate-v1-to-v2')
            ->setDescription('This command will migrate all the existing blocks and items from v1 to v2');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processBlockTranslations();
        $this->processItems();
        $this->processBlockTranslationsWithLink();
    }

    private function processBlockTranslations(): void
    {
        $blockTranslations = $this->loadBlockTranslations();
        $progressBar = new ProgressBar($this->output, count($blockTranslations));

        $this->output->writeln(sprintf('Processing %d block translations:', count($blockTranslations)));
        $progressBar->start();

        foreach ($progressBar->iterate($blockTranslations) as $blockTranslation) {
            $this->processBlockTranslation($blockTranslation);
        }

        $progressBar->finish();
        $this->output->writeln(PHP_EOL);
    }

    private function processItems(): void
    {
        $items = $this->loadItems();
        $progressBar = new ProgressBar($this->output, count($items));

        $this->output->writeln(sprintf('Processing %d items:', count($items)));
        $progressBar->start();

        foreach ($progressBar->iterate($items) as $item) {
            $this->processItem($item);
        }

        $progressBar->finish();
        $this->output->writeln(PHP_EOL);
    }

    private function processBlockTranslationsWithLink(): void
    {
        $blockTranslationsWithLink = $this->loadBlockTranslationsWithLink();
        $progressBar = new ProgressBar($this->output, count($blockTranslationsWithLink));

        $this->output->writeln(sprintf('Processing %d block translations with links:', count($blockTranslationsWithLink)));
        $progressBar->start();

        foreach ($progressBar->iterate($blockTranslationsWithLink) as $blockTranslationWithLink) {
            $this->processLink($blockTranslationWithLink);
        }

        $progressBar->finish();
    }

    private function loadBlockTranslations(): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('bt.block_id, bt.locale, bt.status, bt.parameters')
            ->from('nglayouts_block', 'b')
            ->innerJoin(
                'b',
                'nglayouts_block_translation',
                'bt',
                $query->expr()->and(
                    $query->expr()->eq('bt.block_id', 'b.id'),
                    $query->expr()->eq('bt.status', 'b.status'),
                ),
            )
            ->where(
                $query->expr()->eq('b.definition_identifier', ':definition_identifier'),
            )
            ->setParameter('definition_identifier', self::REMOTE_MEDIA_BLOCK_DEFINITION, Types::STRING);

        return $query->execute()->fetchAllAssociative();
    }

    private function loadItems(): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('i.id, i.value')
            ->from('nglayouts_collection_item', 'i')
            ->where(
                $query->expr()->eq('i.value_type', ':value_type'),
            )
            ->setParameter('value_type', self::REMOTE_MEDIA_ITEM_DEFINITION, Types::STRING);

        return $query->execute()->fetchAllAssociative();
    }

    private function loadBlockTranslationsWithLink(): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('bt.block_id, bt.locale, bt.status, bt.parameters')
            ->from('nglayouts_block_translation', 'bt')
            ->where(
                $query->expr()->like('bt.parameters', ':query_string'),
            )
            ->setParameter(
                'query_string',
                sprintf('%%"link":"%s:\\\\/\\\\/%%', self::REMOTE_MEDIA_LINK_PREFIX),
                Types::STRING,
            );

        return $query->execute()->fetchAllAssociative();
    }

    private function convertValue(string $value): string
    {
        $valueParts = explode('|', $value);
        $type = 'upload';
        $resourceType = array_shift($valueParts);
        $resourceId = implode('|', $valueParts);

        return implode('||', [$type, $resourceType, $resourceId]);
    }

    private function processBlockTranslation(array $blockTranslation): void
    {
        $blockParameters = json_decode($blockTranslation['parameters'], true);

        $blockParameters[self::REMOTE_MEDIA_BLOCK_VALUE_PARAMETER_NAME] = $this->convertValue(
            $blockParameters[self::REMOTE_MEDIA_BLOCK_VALUE_PARAMETER_NAME],
        );

        $blockTranslation['parameters'] = json_encode($blockParameters);

        $this->updateBlockTranslation($blockTranslation);
    }

    private function processItem(array $item): void
    {
        $item['value'] = $this->convertValue($item['value']);

        $this->updateItem($item);
    }

    private function processLink(array $blockTranslationWithLink): void
    {
        $blockParameters = json_decode($blockTranslationWithLink['parameters'], true);
        $value = $blockParameters['link']['link'];
        $linkValuePrefix = sprintf('%s://', self::REMOTE_MEDIA_LINK_PREFIX);
        $remoteMediaValue = mb_substr($value, mb_strlen($linkValuePrefix));

        $blockParameters['link']['link'] = sprintf('%s%s', $linkValuePrefix, $this->convertValue($remoteMediaValue));
        $blockTranslationWithLink['parameters'] = json_encode($blockParameters);

        $this->updateBlockTranslation($blockTranslationWithLink);
    }

    private function updateBlockTranslation(array $blockTranslation): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('nglayouts_block_translation')
            ->set('parameters', ':parameters')
            ->where(
                $query->expr()->eq('block_id', ':block_id'),
                $query->expr()->eq('locale', ':locale'),
                $query->expr()->eq('status', ':status'),
            )
            ->setParameter('block_id', $blockTranslation['block_id'], Types::INTEGER)
            ->setParameter('locale', $blockTranslation['locale'], Types::STRING)
            ->setParameter('status', $blockTranslation['status'], Types::STRING)
            ->setParameter('parameters', $blockTranslation['parameters'], Types::STRING);

        $query->execute();
    }

    private function updateItem(array $item): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('nglayouts_collection_item')
            ->set('value', ':value')
            ->where(
                $query->expr()->eq('id', ':id'),
            )
            ->setParameter('id', $item['id'], Types::INTEGER)
            ->setParameter('value', $item['value'], Types::STRING);

        $query->execute();
    }
}

<?php

declare(strict_types=1);

namespace Minicli\Plugin\Commands;

use Composer\Command\BaseCommand;
use Composer\Package\PackageInterface;
use Minicli\Plugin\PluginManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DumpCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('mincli:dump-plugins')
            ->setDescription('Dump all installed Minicli plugins to the plugin cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composer = $this->requireComposer();

        $vendorDirectory = $composer
            ->getConfig()
            ->get('vendor-dir');

        $plugins = [];

        $packages = $composer
            ->getRepositoryManager()
            ->getLocalRepository()
            ->getCanonicalPackages();

        $packages[] = $composer->getPackage();

        /** @var PackageInterface $package */
        foreach ($packages as $package) {
            $extra = $package->getExtra();
            // @phpstan-ignore-next-line
            $plugins = array_merge($plugins, $extra['minicli']['plugins'] ?? []);
        }

        file_put_contents(
            implode(DIRECTORY_SEPARATOR, [$vendorDirectory, PluginManager::PLUGIN_CACHE_FILE]),
            json_encode($plugins, JSON_PRETTY_PRINT)
        );

        return 0;
    }
}

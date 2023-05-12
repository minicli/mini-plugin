<?php

declare(strict_types=1);

namespace Minicli\Plugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Minicli\Plugin\Commands\DumpCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class PluginManager implements PluginInterface, EventSubscriberInterface, Capable
{
    public const PLUGIN_CACHE_FILE = 'minicli-plugins.json';

    private Composer $composer;

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
    }

    /**
     * @inheritDoc
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @inheritDoc
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        /** @var string $vendorDirectory */
        $vendorDirectory = $composer->getConfig()->get('vendor-dir');
        $pluginFile = sprintf('%s/%s', $vendorDirectory, self::PLUGIN_CACHE_FILE);

        if (file_exists($pluginFile)) {
            unlink($pluginFile);
        }
    }

    /**
     * @return array<string,string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'post-autoload-dump' => 'registerPlugins',
        ];
    }

    public function registerPlugins(): void
    {
        $cmd = new DumpCommand();
        $cmd->setComposer($this->composer);
        $cmd->run(new ArrayInput([]), new ConsoleOutput(OutputInterface::VERBOSITY_NORMAL, true));
    }

    /**
     * @return array<class-string,class-string>
     */
    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => MinicliCommandProvider::class,
        ];
    }
}

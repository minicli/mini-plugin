<?php

declare(strict_types=1);

namespace Minicli\Plugin;

use Composer\Command\BaseCommand;
use Composer\Plugin\Capability\CommandProvider;
use Minicli\Plugin\Commands\DumpCommand;

final class MinicliCommandProvider implements CommandProvider
{
    /**
     * @return array<int,BaseCommand>
     */
    public function getCommands(): array
    {
        return [
            new DumpCommand(),
        ];
    }
}

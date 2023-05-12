<?php

declare(strict_types=1);

namespace Minicli\Plugin;

use Minicli\DI\Container;
use Minicli\Exception\BindingResolutionException;
use ReflectionException;
use Throwable;

final class PluginLoader
{
    /**
     * @var bool
     */
    private static bool $loaded = false;

    /**
     * @var array<int, object>
     */
    private static array $instances = [];

    /**
     * @param string $interface the interface for the hook to execute
     * @return array<int, object> list of plugins
     * @throws BindingResolutionException|ReflectionException|Throwable
     */
    public static function getPlugins(string $interface): array
    {
        return array_values(
            array_filter(
                self::getPluginInstances(),
                function ($plugin) use ($interface): bool {
                    return $plugin instanceof $interface;
                }
            )
        );
    }

    /**
     * @return void
     */
    public static function reset(): void
    {
        self::$loaded = false;
        self::$instances = [];
    }

    /**
     * @return array<int, object>
     * @throws BindingResolutionException|ReflectionException|Throwable
     */
    private static function getPluginInstances(): array
    {
        if (! self::$loaded) {
            $cachedPlugins = sprintf('%s/vendor/minicli-plugins.json', getcwd());
            $container = Container::getInstance();

            if (! file_exists($cachedPlugins)) {
                return [];
            }

            $content = file_get_contents($cachedPlugins);
            if ($content === false) {
                return [];
            }

            try {
                /** @var array<int, class-string> $pluginClasses */
                $pluginClasses = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                $pluginClasses = [];
            }

            usort($pluginClasses, function (string $pluginA, string $pluginB) {
                $isOfficialPlugin = fn (string $plugin) => str_starts_with($plugin, 'Minicli\\Framework\\Plugins\\');

                return match (true) {
                    $isOfficialPlugin($pluginA) && $isOfficialPlugin($pluginB),
                        ! $isOfficialPlugin($pluginA) && ! $isOfficialPlugin($pluginB) => 0,
                    $isOfficialPlugin($pluginA) => 1,
                    default => -1,
                };
            });

            self::$instances = array_map(
                function ($class) use ($container) {
                    /** @var object $object */
                    $object = $container->get($class);

                    return $object;
                },
                $pluginClasses,
            );

            self::$loaded = true;
        }

        return self::$instances;
    }
}

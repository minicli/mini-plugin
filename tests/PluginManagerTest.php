<?php

declare(strict_types=1);

use Composer\Factory;
use Composer\IO\NullIO;
use Minicli\Plugin\MinicliCommandProvider;
use Minicli\Plugin\PluginManager;

beforeEach(function () {
    $this->manager = new PluginManager();
    $this->io = new NullIO();
    $this->composer = (new Factory())->createComposer($this->io);
});

it('exists')->assertTrue(class_exists(PluginManager::class));

it('removes the cached plugins file on uninstall', function () {
    touch('vendor/minicli-plugins.json');

    $this->manager->uninstall($this->composer, $this->io);

    $this->assertFileDoesNotExist('vendor/minicli-plugins.json');
});

it('should create the cached plugins file', function () {
    $this->manager->activate($this->composer, $this->io);
    $this->manager->registerPlugins();

    $this->assertFileExists('vendor/minicli-plugins.json');
});

it('subscribes for the post-autoload-dump event', function () {
    $this->assertArrayHasKey('post-autoload-dump', $this->manager->getSubscribedEvents());
});

it('matches the capabilities', function () {
    $this->assertContains(MinicliCommandProvider::class, $this->manager->getCapabilities());
});

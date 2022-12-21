<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests;

use Database\Factories\AnimeFactory;
use Database\Factories\AnimeModelFactoryDescriptor;
use Database\Factories\MangaFactory;
use Database\Factories\MangaModelFactoryDescriptor;
use Database\Factories\MediaModelFactoryDescriptor;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // noop
    }

    public function register(): void
    {
        $this->app->when(AnimeFactory::class)
            ->needs(MediaModelFactoryDescriptor::class)
            ->give(AnimeModelFactoryDescriptor::class);

        $this->app->when(MangaFactory::class)
            ->needs(MediaModelFactoryDescriptor::class)
            ->give(MangaModelFactoryDescriptor::class);
    }
}

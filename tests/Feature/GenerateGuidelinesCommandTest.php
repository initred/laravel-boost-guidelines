<?php

declare(strict_types=1);

it('service provider is registered', function () {
    expect(app()->getProviders(\Initred\BoostGuidelines\BoostGuidelinesServiceProvider::class))
        ->toHaveCount(1);
});

it('can list available guidelines', function () {
    $this->artisan('boost:guidelines', ['--list' => true])
        ->assertSuccessful();
});

it('can install all guidelines with force', function () {
    $this->artisan('boost:guidelines', ['--all' => true, '--force' => true])
        ->assertSuccessful();
});

it('guidelines templates exist', function () {
    $path = __DIR__.'/../../.ai/guidelines';

    expect(is_dir($path))->toBeTrue();

    $finder = new \Symfony\Component\Finder\Finder;
    $files = $finder->files()->in($path)->name('*.blade.php');

    expect(iterator_count($files))->toBeGreaterThan(0);
});

it('has inertia-react forms guideline', function () {
    $path = __DIR__.'/../../.ai/guidelines/inertia-react/2/forms.blade.php';

    expect(file_exists($path))->toBeTrue();
});

it('forms guideline includes shadcn button section', function () {
    $path = __DIR__.'/../../.ai/guidelines/inertia-react/2/forms.blade.php';
    $content = file_get_contents($path);

    expect($content)->toContain('shadcn/ui Button Component');
});

it('has tailwindcss v4 guideline', function () {
    $path = __DIR__.'/../../.ai/guidelines/tailwindcss/4/core.blade.php';

    expect(file_exists($path))->toBeTrue();
});

it('installs guidelines to correct directory', function () {
    $targetPath = base_path('.ai/guidelines');

    // Clean up before test
    if (is_dir($targetPath)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($targetPath);
    }

    $this->artisan('boost:guidelines', ['--all' => true, '--force' => true])
        ->assertSuccessful();

    expect(is_dir($targetPath))->toBeTrue();
    expect(file_exists($targetPath.'/tailwindcss/4/core.blade.php'))->toBeTrue();
});

it('shows correct output format in list mode', function () {
    $this->artisan('boost:guidelines', ['--list' => true])
        ->expectsOutputToContain('Available Guidelines')
        ->assertSuccessful();
});

it('creates nested directories for guidelines', function () {
    // tailwindcss/4 has no requirements, so it's always installed
    $targetPath = base_path('.ai/guidelines/tailwindcss/4');

    $this->artisan('boost:guidelines', ['--all' => true, '--force' => true])
        ->assertSuccessful();

    expect(is_dir($targetPath))->toBeTrue();
});

it('can install tailwindcss guidelines only with flag', function () {
    $targetPath = base_path('.ai/guidelines');

    // Clean up before test
    if (is_dir($targetPath)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($targetPath);
    }

    $this->artisan('boost:guidelines', ['--tailwindcss' => true, '--force' => true])
        ->assertSuccessful();

    expect(file_exists($targetPath.'/tailwindcss/4/core.blade.php'))->toBeTrue();
});

it('can install multiple categories with combined flags', function () {
    $targetPath = base_path('.ai/guidelines');

    // Clean up before test
    if (is_dir($targetPath)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($targetPath);
    }

    $this->artisan('boost:guidelines', ['--tailwindcss' => true, '--inertia-react' => true, '--force' => true])
        ->assertSuccessful();

    expect(file_exists($targetPath.'/tailwindcss/4/core.blade.php'))->toBeTrue();
});

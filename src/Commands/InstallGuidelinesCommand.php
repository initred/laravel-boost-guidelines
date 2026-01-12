<?php

declare(strict_types=1);

namespace Initred\BoostGuidelines\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\warning;

class InstallGuidelinesCommand extends Command
{
    protected $signature = 'boost:guidelines
                            {--list : List all available guidelines}
                            {--all : Install all guidelines}
                            {--tailwindcss : Install only Tailwind CSS guidelines}
                            {--inertia-react : Install only Inertia React guidelines}
                            {--force : Overwrite existing files without confirmation}
                            {--no-update : Skip running boost:update after installation}';

    protected $description = 'Install AI guidelines for Laravel Boost';

    protected Filesystem $files;

    protected string $sourcePath;

    protected string $targetPath;

    /**
     * Guideline requirements configuration.
     * Format: 'guideline/path' => ['package' => 'version']
     *
     * @var array<string, array<string, string>>
     */
    protected array $requirements = [
        'inertia-react/2/forms' => ['inertiajs/inertia-laravel' => '^2.0'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->files = new Filesystem;
        $this->sourcePath = __DIR__.'/../../.ai/guidelines';
        $this->targetPath = base_path('.ai/guidelines');
    }

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listGuidelines();
        }

        $guidelines = $this->getAvailableGuidelines();

        if (empty($guidelines)) {
            warning('No guidelines available.');

            return self::FAILURE;
        }

        // Check for category-specific flags
        $categoryFilters = $this->getCategoryFilters();

        if (! empty($categoryFilters)) {
            $selected = $this->getGuidelinesByCategories($guidelines, $categoryFilters);

            if (empty($selected)) {
                warning('No guidelines found for selected categories: '.implode(', ', $categoryFilters));

                return self::FAILURE;
            }

            return $this->installGuidelines($selected);
        }

        if ($this->option('all')) {
            // Filter out disabled guidelines for --all option
            $selected = array_values(array_filter(
                array_keys($guidelines),
                fn (string $key): bool => ! $this->isDisabled($key)
            ));
        } else {
            $options = $this->buildMultiselectOptions($guidelines);
            $disabled = $this->getDisabledGuidelines($guidelines);

            /** @var list<string> $selected */
            $selected = multiselect(
                label: 'Select guidelines to install:',
                options: $options,
                default: array_values(array_filter(
                    array_keys($guidelines),
                    fn (string $key): bool => ! in_array($key, $disabled, true)
                )),
                required: true,
                hint: 'Press Space to select, Enter to confirm',
                scroll: 10,
            );
        }

        if (empty($selected)) {
            info('No guidelines selected.');

            return self::SUCCESS;
        }

        return $this->installGuidelines($selected);
    }

    protected function listGuidelines(): int
    {
        $guidelines = $this->getAvailableGuidelines();

        if (empty($guidelines)) {
            warning('No guidelines available.');

            return self::FAILURE;
        }

        $this->newLine();
        info('Available Guidelines:');
        $this->newLine();

        $grouped = $this->groupGuidelinesByCategory($guidelines);

        foreach ($grouped as $category => $items) {
            $this->line("  <fg=cyan;options=bold>{$category}</>");

            foreach ($items as $path => $name) {
                $installed = $this->isInstalled($path) ? '<fg=green>✓</>' : '<fg=gray>○</>';
                $disabled = $this->isDisabled($path);
                $requirementLabel = $this->getRequirementLabel($path);

                if ($disabled) {
                    $this->line("    {$installed} <fg=gray>{$name}</> <fg=yellow>{$requirementLabel}</>");
                } else {
                    $this->line("    {$installed} {$name}");
                }
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $selected
     */
    protected function installGuidelines(array $selected): int
    {
        $force = $this->option('force');
        /** @var list<string> $installed */
        $installed = [];
        /** @var list<string> $skipped */
        $skipped = [];

        foreach ($selected as $guideline) {
            $sourcePath = $this->sourcePath.'/'.$guideline.'.blade.php';
            $targetPath = $this->targetPath.'/'.$guideline.'.blade.php';

            if (! $this->files->exists($sourcePath)) {
                warning("Source file not found: {$guideline}");

                continue;
            }

            $targetDir = dirname($targetPath);

            if (! $this->files->isDirectory($targetDir)) {
                $this->files->makeDirectory($targetDir, 0755, true);
            }

            if ($this->files->exists($targetPath) && ! $force) {
                $relativePath = str_replace(base_path().'/', '', $targetPath);
                $overwrite = confirm(
                    label: "File already exists: {$relativePath}. Overwrite?",
                    default: false
                );

                if (! $overwrite) {
                    $skipped[] = $guideline;

                    continue;
                }
            }

            $this->files->copy($sourcePath, $targetPath);
            $installed[] = $guideline;
        }

        $this->newLine();

        if (! empty($installed)) {
            info('Installed guidelines:');

            foreach ($installed as $guideline) {
                $this->line("  <fg=green>✓</> {$guideline}");
            }
        }

        if (! empty($skipped)) {
            $this->newLine();
            note('Skipped (already exists):');

            foreach ($skipped as $guideline) {
                $this->line("  <fg=yellow>○</> {$guideline}");
            }
        }

        $this->newLine();
        info('Guidelines installed to .ai/guidelines/');

        if (! empty($installed) && $this->shouldRunBoostUpdate()) {
            $this->newLine();
            $this->call('boost:update');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    protected function getAvailableGuidelines(): array
    {
        if (! $this->files->isDirectory($this->sourcePath)) {
            return [];
        }

        $guidelines = [];
        $finder = new Finder;
        $finder->files()->in($this->sourcePath)->name('*.blade.php');

        foreach ($finder as $file) {
            $relativePath = str_replace(
                [$this->sourcePath.'/', '.blade.php'],
                '',
                $file->getPathname()
            );

            $guidelines[$relativePath] = $this->formatGuidelineName($relativePath);
        }

        ksort($guidelines);

        return $guidelines;
    }

    /**
     * Build multiselect options with requirement labels.
     *
     * @param  array<string, string>  $guidelines
     * @return array<string, string>
     */
    protected function buildMultiselectOptions(array $guidelines): array
    {
        $options = [];

        foreach ($guidelines as $path => $name) {
            $label = $name;

            if ($this->isDisabled($path)) {
                $requirementLabel = $this->getRequirementLabel($path);
                $label = "{$name} {$requirementLabel}";
            }

            $options[$path] = $label;
        }

        return $options;
    }

    /**
     * Get list of disabled guidelines (requirements not met).
     *
     * @param  array<string, string>  $guidelines
     * @return list<string>
     */
    protected function getDisabledGuidelines(array $guidelines): array
    {
        $disabled = [];

        foreach (array_keys($guidelines) as $path) {
            if ($this->isDisabled($path)) {
                $disabled[] = $path;
            }
        }

        return $disabled;
    }

    /**
     * Check if a guideline is disabled (requirements not met).
     */
    protected function isDisabled(string $guideline): bool
    {
        if (! isset($this->requirements[$guideline])) {
            return false;
        }

        foreach ($this->requirements[$guideline] as $package => $version) {
            if (! $this->isPackageInstalled($package, $version)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get requirement label for a guideline.
     */
    protected function getRequirementLabel(string $guideline): string
    {
        if (! isset($this->requirements[$guideline])) {
            return '';
        }

        $labels = [];

        foreach ($this->requirements[$guideline] as $package => $version) {
            // Extract version number for display
            $versionDisplay = preg_replace('/[^0-9.]/', '', $version) ?? '';
            $packageName = basename(str_replace('/', '-', $package));

            if (str_contains($package, 'inertia')) {
                $labels[] = "(requires Inertia v{$versionDisplay})";
            } else {
                $labels[] = "(requires {$packageName} {$version})";
            }
        }

        return implode(' ', $labels);
    }

    /**
     * Check if a package is installed with the required version.
     */
    protected function isPackageInstalled(string $package, string $requiredVersion): bool
    {
        $composerLock = base_path('composer.lock');

        if (! $this->files->exists($composerLock)) {
            return false;
        }

        /** @var array{packages?: list<array{name: string, version: string}>, packages-dev?: list<array{name: string, version: string}>}|null $lockContent */
        $lockContent = json_decode($this->files->get($composerLock), true);

        if (! is_array($lockContent)) {
            return false;
        }

        $packages = array_merge(
            $lockContent['packages'] ?? [],
            $lockContent['packages-dev'] ?? []
        );

        foreach ($packages as $installedPackage) {
            if ($installedPackage['name'] === $package) {
                $installedVersion = $installedPackage['version'];

                return $this->versionSatisfies($installedVersion, $requiredVersion);
            }
        }

        return false;
    }

    /**
     * Check if installed version satisfies the required version.
     */
    protected function versionSatisfies(string $installed, string $required): bool
    {
        // Remove 'v' prefix if present
        $installed = ltrim($installed, 'v');

        // Extract major version from requirement (e.g., ^2.0 -> 2)
        preg_match('/(\d+)/', $required, $matches);
        $requiredMajor = $matches[1] ?? '0';

        // Extract major version from installed
        preg_match('/(\d+)/', $installed, $matches);
        $installedMajor = $matches[1] ?? '0';

        // Check if major version matches
        if (str_starts_with($required, '^')) {
            return (int) $installedMajor >= (int) $requiredMajor;
        }

        return $installedMajor === $requiredMajor;
    }

    protected function formatGuidelineName(string $path): string
    {
        $parts = explode('/', $path);
        $name = array_pop($parts);

        // Format category parts with version detection
        $formattedParts = array_map(function (string $part): string {
            // If it's a version number, prefix with 'v'
            if (is_numeric($part)) {
                return "v{$part}";
            }

            // Format category name (e.g., "inertia-react" -> "Inertia React")
            $formatted = str_replace(['-', '_'], ' ', $part);

            return ucwords($formatted);
        }, $parts);

        $category = implode(' ', $formattedParts);

        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);

        return $category !== '' ? "{$category} - {$name}" : $name;
    }

    /**
     * @param  array<string, string>  $guidelines
     * @return array<string, array<string, string>>
     */
    protected function groupGuidelinesByCategory(array $guidelines): array
    {
        /** @var array<string, array<string, string>> $grouped */
        $grouped = [];

        foreach ($guidelines as $path => $name) {
            $parts = explode('/', $path);
            $category = $parts[0];

            $grouped[$category][$path] = $name;
        }

        return $grouped;
    }

    protected function isInstalled(string $guideline): bool
    {
        return $this->files->exists($this->targetPath.'/'.$guideline.'.blade.php');
    }

    /**
     * Get category filters from command options.
     *
     * @return list<string>
     */
    protected function getCategoryFilters(): array
    {
        $categories = [];

        if ($this->option('tailwindcss')) {
            $categories[] = 'tailwindcss';
        }

        if ($this->option('inertia-react')) {
            $categories[] = 'inertia-react';
        }

        return $categories;
    }

    /**
     * Get guidelines filtered by multiple categories.
     *
     * @param  array<string, string>  $guidelines
     * @param  list<string>  $categories
     * @return list<string>
     */
    protected function getGuidelinesByCategories(array $guidelines, array $categories): array
    {
        $filtered = [];

        foreach (array_keys($guidelines) as $path) {
            foreach ($categories as $category) {
                if (str_starts_with($path, $category.'/') && ! $this->isDisabled($path)) {
                    $filtered[] = $path;

                    break;
                }
            }
        }

        return $filtered;
    }

    /**
     * Ask if user wants to run boost:update command.
     */
    protected function shouldRunBoostUpdate(): bool
    {
        // Skip if --no-update flag is set
        if ($this->option('no-update')) {
            return false;
        }

        // Check if boost:update command exists
        if (! $this->getApplication()?->has('boost:update')) {
            note('Run `php artisan boost:update` to apply the guidelines.');

            return false;
        }

        return confirm(
            label: 'Run `php artisan boost:update` to apply the guidelines now?',
            default: true
        );
    }
}

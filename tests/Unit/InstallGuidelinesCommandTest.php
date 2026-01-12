<?php

declare(strict_types=1);

use Initred\BoostGuidelines\Commands\InstallGuidelinesCommand;

beforeEach(function () {
    $this->command = new InstallGuidelinesCommand;
});

describe('formatGuidelineName', function () {
    it('formats path with version number', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'formatGuidelineName');

        expect($method->invoke($this->command, 'inertia-react/2/forms'))
            ->toBe('Inertia React v2 - Forms');
    });

    it('formats path without version', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'formatGuidelineName');

        expect($method->invoke($this->command, 'tailwindcss/core'))
            ->toBe('Tailwindcss - Core');
    });

    it('formats single segment path', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'formatGuidelineName');

        expect($method->invoke($this->command, 'general'))
            ->toBe('General');
    });

    it('handles multiple version segments', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'formatGuidelineName');

        expect($method->invoke($this->command, 'framework/4/5/feature'))
            ->toBe('Framework v4 v5 - Feature');
    });
});

describe('versionSatisfies', function () {
    it('satisfies caret version when major matches', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'versionSatisfies');

        expect($method->invoke($this->command, '2.1.0', '^2.0'))->toBeTrue();
        expect($method->invoke($this->command, '2.0.0', '^2.0'))->toBeTrue();
        expect($method->invoke($this->command, 'v2.5.3', '^2.0'))->toBeTrue();
    });

    it('satisfies when installed major is greater', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'versionSatisfies');

        expect($method->invoke($this->command, '3.0.0', '^2.0'))->toBeTrue();
    });

    it('fails when installed major is less', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'versionSatisfies');

        expect($method->invoke($this->command, '1.9.0', '^2.0'))->toBeFalse();
    });

    it('handles exact version match', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'versionSatisfies');

        expect($method->invoke($this->command, '2.0.0', '2.0'))->toBeTrue();
        expect($method->invoke($this->command, '3.0.0', '2.0'))->toBeFalse();
    });
});

describe('groupGuidelinesByCategory', function () {
    it('groups guidelines by first path segment', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'groupGuidelinesByCategory');

        $guidelines = [
            'inertia-react/2/forms' => 'Inertia React v2 - Forms',
            'inertia-react/2/routing' => 'Inertia React v2 - Routing',
            'tailwindcss/4/core' => 'Tailwindcss v4 - Core',
        ];

        $result = $method->invoke($this->command, $guidelines);

        expect($result)->toHaveKey('inertia-react');
        expect($result)->toHaveKey('tailwindcss');
        expect($result['inertia-react'])->toHaveCount(2);
        expect($result['tailwindcss'])->toHaveCount(1);
    });
});

describe('getRequirementLabel', function () {
    it('returns empty string for guideline without requirements', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'getRequirementLabel');

        expect($method->invoke($this->command, 'tailwindcss/4/core'))->toBe('');
    });

    it('returns inertia requirement label', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'getRequirementLabel');

        expect($method->invoke($this->command, 'inertia-react/2/forms'))
            ->toContain('requires Inertia v2.0');
    });
});

describe('isDisabled', function () {
    it('returns false for guideline without requirements', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'isDisabled');

        expect($method->invoke($this->command, 'tailwindcss/4/core'))->toBeFalse();
    });
});

describe('getGuidelinesByCategories', function () {
    it('filters guidelines by single category', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'getGuidelinesByCategories');

        $guidelines = [
            'tailwindcss/4/core' => 'Tailwindcss v4 - Core',
            'tailwindcss/4/colors' => 'Tailwindcss v4 - Colors',
            'inertia-react/2/forms' => 'Inertia React v2 - Forms',
        ];

        $result = $method->invoke($this->command, $guidelines, ['tailwindcss']);

        expect($result)->toHaveCount(2);
        expect($result)->toContain('tailwindcss/4/core');
        expect($result)->toContain('tailwindcss/4/colors');
    });

    it('filters guidelines by multiple categories', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'getGuidelinesByCategories');

        $guidelines = [
            'tailwindcss/4/core' => 'Tailwindcss v4 - Core',
            'inertia-react/2/forms' => 'Inertia React v2 - Forms',
            'other/1/something' => 'Other v1 - Something',
        ];

        // Note: inertia-react/2/forms is disabled by default (requires Inertia v2)
        $result = $method->invoke($this->command, $guidelines, ['tailwindcss', 'other']);

        expect($result)->toHaveCount(2);
        expect($result)->toContain('tailwindcss/4/core');
        expect($result)->toContain('other/1/something');
    });

    it('returns empty array when no guidelines match category', function () {
        $method = new ReflectionMethod(InstallGuidelinesCommand::class, 'getGuidelinesByCategories');

        $guidelines = [
            'tailwindcss/4/core' => 'Tailwindcss v4 - Core',
        ];

        $result = $method->invoke($this->command, $guidelines, ['nonexistent']);

        expect($result)->toBeEmpty();
    });
});

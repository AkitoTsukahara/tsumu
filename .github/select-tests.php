<?php

declare(strict_types=1);

namespace Tsumu\Ci;

final class ChangedTestSelector
{
    /**
     * @param  list<array{patterns: list<string>, backend?: list<string>, browser?: list<string>}>|null  $testMap
     */
    public function __construct(
        private readonly string $projectRoot,
        private readonly ?array $testMap = null,
    ) {}

    /**
     * @param  list<string>  $changedFiles
     * @return array{backend: list<string>, browser: list<string>, unmapped: list<string>}
     */
    public function select(array $changedFiles): array
    {
        $backend = [];
        $browser = [];
        $unmapped = [];

        foreach ($changedFiles as $file) {
            $file = trim(str_replace('\\', '/', $file));

            if ($file === '') {
                continue;
            }

            if ($this->matchesAny($file, $this->allTestPatterns())) {
                $backend = [...$backend, 'tests/Unit', 'tests/Feature', 'tests/DbIntegration'];
                $browser[] = 'tests/Browser';

                continue;
            }

            if ($this->matchesAny($file, $this->browserInfrastructurePatterns())) {
                $browser[] = 'tests/Browser';

                continue;
            }

            if (str_starts_with($file, 'tests/')) {
                $this->selectChangedTest($file, $backend, $browser);

                continue;
            }

            $mapped = false;

            foreach ($this->testMap ?? $this->defaultTestMap() as $entry) {
                if (! $this->matchesAny($file, $entry['patterns'])) {
                    continue;
                }

                $mapped = true;
                $backend = [...$backend, ...($entry['backend'] ?? [])];
                $browser = [...$browser, ...($entry['browser'] ?? [])];
            }

            if (! $mapped && $this->requiresExplicitMapping($file)) {
                $unmapped[] = $file;
            }
        }

        return [
            'backend' => $this->uniquePaths($backend),
            'browser' => $this->uniquePaths($browser),
            'unmapped' => $this->uniquePaths($unmapped),
        ];
    }

    /** @return list<string> */
    private function allTestPatterns(): array
    {
        return [
            '.env.example',
            '.github/select-tests.php',
            '.github/workflows/ci.yml',
            'bootstrap/**',
            'composer.json',
            'composer.lock',
            'compose.yaml',
            'config/**',
            'Makefile',
            'phpunit.xml',
            'tests/Pest.php',
            'tests/TestCase.php',
        ];
    }

    /** @return list<string> */
    private function browserInfrastructurePatterns(): array
    {
        return ['package.json', 'package-lock.json', 'vite.config.*'];
    }

    /**
     * @return list<array{patterns: list<string>, backend?: list<string>, browser?: list<string>}>
     */
    private function defaultTestMap(): array
    {
        return [
            [
                'patterns' => [
                    'app/Livewire/Auth/Login.php',
                    'app/Livewire/Forms/LoginForm.php',
                    'resources/views/livewire/auth/login.blade.php',
                ],
                'backend' => ['tests/Feature/Auth/LoginTest.php'],
                'browser' => [
                    'tests/Browser/LoginPageTest.php',
                    'tests/Browser/TodayPageTest.php',
                ],
            ],
            [
                'patterns' => [
                    'app/Http/Controllers/Auth/LogoutController.php',
                    'app/Livewire/Today.php',
                    'resources/views/livewire/today.blade.php',
                ],
                'backend' => [
                    'tests/Feature/Auth/TodayTest.php',
                    'tests/Feature/Equipment/IndexTest.php',
                    'tests/Feature/Exercise/IndexTest.php',
                ],
                'browser' => ['tests/Browser/TodayPageTest.php'],
            ],
            [
                'patterns' => ['resources/views/layouts/app.blade.php'],
                'backend' => ['tests/Feature/Auth/LoginTest.php'],
                'browser' => ['tests/Browser'],
            ],
            [
                'patterns' => ['routes/web.php'],
                'backend' => ['tests/Feature'],
                'browser' => ['tests/Browser'],
            ],
            [
                'patterns' => ['resources/views/welcome.blade.php'],
                'backend' => ['tests/Feature/ExampleTest.php'],
                'browser' => ['tests/Browser/HomePageTest.php'],
            ],
            [
                'patterns' => ['resources/css/app.css', 'resources/js/app.js'],
                'browser' => ['tests/Browser'],
            ],
            [
                'patterns' => [
                    'infra/Persistence/Eloquent/Models/User.php',
                    'database/factories/UserFactory.php',
                    'database/migrations/0001_01_01_000000_create_users_table.php',
                ],
                'backend' => [
                    'tests/DbIntegration/Infra/Persistence/Repositories/UserRepositoryTest.php',
                    'tests/DbIntegration/Infra/UserProviderTest.php',
                    'tests/DbIntegration/Domain/Equipment/EquipmentSchemaTest.php',
                    'tests/DbIntegration/Domain/Exercise/ExerciseSchemaTest.php',
                    'tests/Feature/Console/CreateUserCommandTest.php',
                    'tests/Feature/Service/Command/AuthenticateUserTest.php',
                ],
                'browser' => [
                    'tests/Browser/LoginPageTest.php',
                    'tests/Browser/TodayPageTest.php',
                ],
            ],
            [
                'patterns' => ['app/Console/Commands/CreateUserCommand.php'],
                'backend' => ['tests/Feature/Console/CreateUserCommandTest.php'],
            ],
            [
                'patterns' => ['app/Service/Command/AuthenticateUser.php'],
                'backend' => ['tests/Feature/Service/Command/AuthenticateUserTest.php'],
            ],
            [
                'patterns' => [
                    'app/Service/Command/CreateUser.php',
                    'domain/User/UserRepository.php',
                    'domain/User/Exceptions/EmailAlreadyInUseException.php',
                    'infra/Persistence/Eloquent/EloquentUserRepository.php',
                    'infra/Persistence/Repositories/UserRepository.php',
                ],
                'backend' => [
                    'tests/DbIntegration/Infra/Persistence/Repositories/UserRepositoryTest.php',
                    'tests/Feature/Console/CreateUserCommandTest.php',
                ],
            ],
            [
                'patterns' => [
                    'domain/Shared/UuidV7Id.php',
                    'domain/Shared/Exceptions/InvalidUuidV7IdException.php',
                    'domain/User/UserId.php',
                ],
                'backend' => [
                    'tests/Unit/Domain/User/UserIdTest.php',
                    'tests/Unit/Domain/Equipment/EquipmentTest.php',
                    'tests/Unit/Domain/Exercise/ExerciseTest.php',
                ],
            ],
            [
                'patterns' => ['domain/Equipment/**'],
                'backend' => [
                    'tests/Unit/Domain/Equipment/EquipmentTest.php',
                    'tests/DbIntegration/Domain/Equipment/EquipmentSchemaTest.php',
                    'tests/DbIntegration/Infra/Persistence/Repositories/EquipmentRepositoryTest.php',
                    'tests/DbIntegration/Infra/Persistence/Queries/EquipmentListQueryTest.php',
                    'tests/Feature/Equipment/IndexTest.php',
                ],
            ],
            [
                'patterns' => [
                    'database/factories/EquipmentFactory.php',
                    'database/migrations/2026_09_22_055057_create_equipment_table.php',
                ],
                'backend' => [
                    'tests/Unit/Domain/Equipment/EquipmentTest.php',
                    'tests/DbIntegration/Domain/Equipment/EquipmentSchemaTest.php',
                    'tests/DbIntegration/Domain/Exercise/ExerciseSchemaTest.php',
                    'tests/DbIntegration/Infra/Persistence/Repositories/EquipmentRepositoryTest.php',
                    'tests/DbIntegration/Infra/Persistence/Queries/EquipmentListQueryTest.php',
                    'tests/Feature/Equipment/IndexTest.php',
                ],
            ],
            [
                'patterns' => [
                    'app/Service/Command/CreateEquipment.php',
                    'app/Service/Command/UpdateEquipment.php',
                    'infra/Persistence/Mappers/EquipmentMapper.php',
                    'infra/Persistence/Repositories/EquipmentRepository.php',
                ],
                'backend' => [
                    'tests/DbIntegration/Infra/Persistence/Repositories/EquipmentRepositoryTest.php',
                ],
            ],
            [
                'patterns' => ['infra/Persistence/Eloquent/Models/Equipment.php'],
                'backend' => [
                    'tests/DbIntegration/Domain/Exercise/ExerciseSchemaTest.php',
                    'tests/DbIntegration/Infra/Persistence/Repositories/EquipmentRepositoryTest.php',
                ],
            ],
            [
                'patterns' => [
                    'domain/Exercise/**',
                    'database/factories/ExerciseFactory.php',
                    'database/migrations/2026_09_24_080206_create_exercises_table.php',
                    'infra/Persistence/Eloquent/Models/Exercise.php',
                ],
                'backend' => [
                    'tests/Unit/Domain/Exercise/ExerciseTest.php',
                    'tests/DbIntegration/Domain/Exercise/ExerciseSchemaTest.php',
                    'tests/DbIntegration/Infra/Persistence/Repositories/ExerciseRepositoryTest.php',
                ],
            ],
            [
                'patterns' => [
                    'app/Service/Command/CreateExercise.php',
                    'app/Service/Command/UpdateExercise.php',
                    'infra/Persistence/Mappers/ExerciseMapper.php',
                    'infra/Persistence/Repositories/ExerciseRepository.php',
                ],
                'backend' => [
                    'tests/DbIntegration/Infra/Persistence/Repositories/ExerciseRepositoryTest.php',
                ],
            ],
            [
                'patterns' => [
                    'app/Livewire/Forms/EquipmentForm.php',
                    'app/Livewire/Forms/Dto/ValidatedEquipmentInputDto.php',
                ],
                'backend' => ['tests/Feature/Equipment/IndexTest.php'],
                'browser' => ['tests/Browser/TodayPageTest.php'],
            ],
            [
                'patterns' => [
                    'app/Livewire/Forms/ExerciseForm.php',
                    'app/Livewire/Forms/Dto/ValidatedExerciseInputDto.php',
                    'resources/views/components/exercise/body-part-options.blade.php',
                ],
                'backend' => ['tests/Feature/Exercise/IndexTest.php'],
                'browser' => ['tests/Browser/TodayPageTest.php'],
            ],
            [
                'patterns' => [
                    'app/Livewire/Equipment/Index.php',
                    'app/Service/Query/EquipmentListItem.php',
                    'app/Service/Query/EquipmentListQuery.php',
                    'app/Service/Query/Dto/TypedList.php',
                    'app/Service/Query/Equipment/Dto/EquipmentListItemDto.php',
                    'app/Service/Query/Equipment/Dto/EquipmentListItemCollection.php',
                    'app/Service/Query/Equipment/EquipmentListQuery.php',
                    'infra/Persistence/Queries/EquipmentListQuery.php',
                    'resources/views/livewire/equipment/index.blade.php',
                ],
                'backend' => [
                    'tests/DbIntegration/Infra/Persistence/Queries/EquipmentListQueryTest.php',
                    'tests/Feature/Equipment/IndexTest.php',
                    'tests/Unit/App/Service/Query/Equipment/Dto/EquipmentListItemCollectionTest.php',
                ],
                'browser' => ['tests/Browser/TodayPageTest.php'],
            ],
            [
                'patterns' => [
                    'app/Livewire/Exercise/Index.php',
                    'app/Service/Query/Exercise/Dto/ExerciseListItemDto.php',
                    'app/Service/Query/Exercise/Dto/ExerciseListItemCollection.php',
                    'app/Service/Query/Exercise/ExerciseListQuery.php',
                    'infra/Persistence/Queries/ExerciseListQuery.php',
                    'resources/views/components/exercise/body-part-label.blade.php',
                    'resources/views/livewire/exercise/index.blade.php',
                ],
                'backend' => [
                    'tests/DbIntegration/Infra/Persistence/Queries/ExerciseListQueryTest.php',
                    'tests/Feature/Exercise/IndexTest.php',
                ],
                'browser' => ['tests/Browser/TodayPageTest.php'],
            ],
            [
                'patterns' => ['app/Http/Controllers/Controller.php'],
                'backend' => ['tests/Feature'],
            ],
            [
                'patterns' => ['app/Providers/AppServiceProvider.php'],
                'backend' => ['tests/Feature', 'tests/DbIntegration'],
                'browser' => ['tests/Browser'],
            ],
            [
                'patterns' => [
                    'database/migrations/0001_01_01_000001_create_cache_table.php',
                    'database/migrations/0001_01_01_000002_create_jobs_table.php',
                    'database/seeders/DatabaseSeeder.php',
                    'routes/console.php',
                ],
            ],
        ];
    }

    /**
     * @param  list<string>  $backend
     * @param  list<string>  $browser
     */
    private function selectChangedTest(string $file, array &$backend, array &$browser): void
    {
        if (! preg_match('/^tests\/(Unit|Feature|DbIntegration|Browser)\/[A-Za-z0-9_.\/-]+Test\.php$/', $file)) {
            return;
        }

        $target = is_file($this->projectRoot.'/'.$file)
            ? $file
            : implode('/', array_slice(explode('/', $file), 0, 2));

        if (str_starts_with($file, 'tests/Browser/')) {
            $browser[] = $target;

            return;
        }

        $backend[] = $target;
    }

    private function requiresExplicitMapping(string $file): bool
    {
        return $this->matchesAny($file, [
            'app/**',
            'database/**',
            'domain/**',
            'infra/**',
            'resources/css/**',
            'resources/js/**',
            'resources/views/**',
            'routes/**',
        ]);
    }

    /** @param list<string> $patterns */
    private function matchesAny(string $file, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $expression = preg_quote($pattern, '#');
            $expression = str_replace(['\*\*', '\*'], ['.*', '[^/]*'], $expression);

            if (preg_match('#^'.$expression.'$#', $file) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $paths
     * @return list<string>
     */
    private function uniquePaths(array $paths): array
    {
        $paths = array_values(array_unique($paths));
        sort($paths);

        return array_values(array_filter(
            $paths,
            fn (string $path): bool => ! $this->hasSelectedParentDirectory($path, $paths),
        ));
    }

    /** @param list<string> $paths */
    private function hasSelectedParentDirectory(string $path, array $paths): bool
    {
        foreach ($paths as $candidate) {
            if ($candidate === $path || ! is_dir($this->projectRoot.'/'.$candidate)) {
                continue;
            }

            if (str_starts_with($path, $candidate.'/')) {
                return true;
            }
        }

        return false;
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) {
    return;
}

$selector = new ChangedTestSelector(dirname(__DIR__), null);
$changedFiles = file('php://stdin', FILE_IGNORE_NEW_LINES) ?: [];
$selection = $selector->select($changedFiles);

if ($selection['unmapped'] !== []) {
    fwrite(STDERR, "次のファイルに対応するテストを明示してください:\n - ".implode("\n - ", $selection['unmapped'])."\n");
    exit(1);
}

fwrite(STDOUT, 'backend='.implode(' ', $selection['backend'])."\n");
fwrite(STDOUT, 'browser='.implode(' ', $selection['browser'])."\n");

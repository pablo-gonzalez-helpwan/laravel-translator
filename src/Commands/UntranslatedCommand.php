<?php

declare(strict_types=1);

namespace Elegantly\Translator\Commands;

use Illuminate\Contracts\Console\PromptsForMissingInput;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

class UntranslatedCommand extends TranslatorCommand implements PromptsForMissingInput
{
    public $signature = 'translator:untranslated {source} {target} {--translate} {--driver=}';

    public $description = 'Display all the translation keys defined in the source locale but not in the target locale.';

    public function handle(): int
    {
        /** @var string $source */
        $source = $this->argument('source');
        /** @var string $target */
        $target = $this->argument('target');
        $translate = (bool) $this->option('translate');

        $translator = $this->getTranslator();

        $missing = $translator->getUntranslatedTranslations($source, $target);
        $missingDot = $missing->dot();
        $count = $missingDot->count();

        intro('Using driver: '.$translator->driver::class);

        note("{$count} untranslated keys detected.");

        table(
            headers: ['Key', "Source {$source}"],
            rows: $missingDot
                ->map(fn ($value, $key) => [
                    $key,
                    (string) str((string) $value)->limit(50),
                ])->toArray()
        );

        if ($translate) {
            $translated = spin(function () use ($translator, $source, $target, $missingDot) {

                return $translator->translateTranslations(
                    source: $source,
                    target: $target,
                    keys: $missingDot->keys()->all()
                );

            }, "Translating the {$count} translations from '{$source}' to '{$target}'");

            table(
                headers: ['Key', "Source {$source}", "Target {$target}"],
                rows: $translated
                    ->dot()
                    ->map(function ($value, $key) use ($missingDot) {
                        return [
                            (string) $key,
                            str((string) $missingDot->get($key))->limit(25)->value(),
                            str((string) $value)->limit(25)->value(),
                        ];
                    })
                    ->values()
                    ->all()
            );
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Composer;

use JeanPierreGassin\AiContext\Installer;

final class AiContextInstaller
{
    public static function install(mixed $event): void
    {
        if (! class_exists(Installer::class)) {
            if (is_object($event) && method_exists($event, 'getIO')) {
                $event->getIO()->write('ai-context: skipped because dev dependencies are not installed.');
            }

            return;
        }

        Installer::installFromComposer($event);
    }
}

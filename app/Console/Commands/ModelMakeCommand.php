<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand as Command;

/**
 * Class ModelMakeCommand
 */
class ModelMakeCommand extends Command
{
    /**
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return "{$rootNamespace}\Models";
    }
}

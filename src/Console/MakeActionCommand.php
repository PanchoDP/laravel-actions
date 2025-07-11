<?php

declare(strict_types=1);

namespace Panchodp\LaravelAction\Console;

use Illuminate\Console\Command;
use Panchodp\LaravelAction\Actions\CreateDirectory;
use Panchodp\LaravelAction\Actions\ObtainNamespace;
use Panchodp\LaravelAction\Actions\PreparePath;
use Panchodp\LaravelAction\Actions\PrepareStub;
use Panchodp\LaravelAction\Actions\PrepareSubfolder;
use Panchodp\LaravelAction\Actions\ValidateFolder;
use Panchodp\LaravelAction\Actions\ValidateName;
use Throwable;

final class MakeActionCommand extends Command
{
    protected $signature = 'make:action {name} {subfolder?} 
    {--t : Make a DB transaction action } 
    {--tu : Make a DB transaction action } 
    {--ut : Make a DB transaction action } 
    {--u : Make a User injection}';

    protected $description = 'Create a new action class';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): int
    {

        $name = ($this->argument('name')) ? mb_trim($this->argument('name')) : '';
        $subfolder = $this->argument('subfolder') ? mb_trim($this->argument('subfolder'), '/\\') : '';
        $base_folder = (string) config('laravel-actions.base_folder', 'Actions');

        try {
            ValidateName::handle($name);
            $folders = PrepareSubfolder::handle($subfolder);
            ValidateFolder::handle($folders);
            $folder_path = implode(DIRECTORY_SEPARATOR, $folders);
            $path = PreparePath::handle($folder_path, $name, $base_folder);
            $namespace = ObtainNamespace::handle($folder_path, $name, $base_folder);
            $relative_path = dirname("{$base_folder}/{$folder_path}/{$name}.php");
            CreateDirectory::handle($path);

        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->info("Directory {$relative_path} created successfully...");
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $tuFlag = $this->option('tu') || $this->option('ut');
        $tFlag = $this->option('t') || $tuFlag;
        $uFlag = $this->option('u') || $tuFlag;
        $stub = PrepareStub::handle($tuFlag, $tFlag, $uFlag, $filename, $namespace);
        $transaction = $tFlag ? ' with DB transaction' : '.';

        file_put_contents($path, $stub);
        $this->info("Action {$filename} created successfully at app/{$relative_path} folder".$transaction);

        return 0;
    }
}

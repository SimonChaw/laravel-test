<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiToken;
use Illuminate\Support\Str;

class GenerateAuthToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a new api auth token';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = Str::random(60);

        // Revoke old tokens
        ApiToken::whereNotNull('api_token')->delete();

        ApiToken::create(['api_token' => hash('sha256', $token)]);

        $this->info($token);

        return Command::SUCCESS;
    }
}

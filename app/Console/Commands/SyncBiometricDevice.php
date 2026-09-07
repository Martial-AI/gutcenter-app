<?php

namespace App\Console\Commands;

use App\Services\ZKTecoService;
use Illuminate\Console\Command;

class SyncBiometricDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometric:sync {--ip=192.168.1.201 : IP address of the ZKTeco device} {--port=4370 : Port of the device}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize attendance logs directly from the local network ZKTeco fingerprint scanner.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ip = $this->option('ip');
        $port = (int) $this->option('port');

        $this->info("Connexion à la pointeuse ZKTeco {$ip}:{$port}...");

        $zk = new ZKTecoService($ip, $port);
        $result = $zk->syncToDatabase('ZKTeco-ZK3969');

        $this->info("Synchronisation terminée !");
        $this->table(
            ['Total Récupéré', 'Pointages Traités', 'Échecs / Inconnus'],
            [[$result['total'], $result['processed'], $result['failed']]]
        );

        return Command::SUCCESS;
    }
}

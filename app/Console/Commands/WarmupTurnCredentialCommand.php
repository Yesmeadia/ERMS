<?php

namespace App\Console\Commands;

use App\Services\WebRTC\IceServerService;
use Illuminate\Console\Command;

class WarmupTurnCredentialCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webrtc:warmup-turn-credentials {--force : Force refreshing credentials regardless of cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-warm and cache WebRTC TURN credentials to satisfy Metered TURN propagation requirements before exams begin';

    /**
     * Execute the console command.
     */
    public function handle(IceServerService $iceService): int
    {
        $this->info('Checking WebRTC ICE & TURN configuration...');

        $force = (bool) $this->option('force');
        $provider = $iceService->resolveTurnProvider();

        if (! $provider) {
            $this->warn('No active TURN provider configured (TURN is disabled or unconfigured). STUN direct P2P will be used.');
            $iceServers = $iceService->getIceServers();
            $this->table(['Type', 'URLs'], array_map(function ($s) {
                return [
                    'Type' => isset($s['username']) ? 'TURN' : 'STUN',
                    'URLs' => is_array($s['urls']) ? implode(', ', $s['urls']) : $s['urls'],
                ];
            }, $iceServers));
            return Command::SUCCESS;
        }

        $this->info('Active TURN Provider: ' . class_basename($provider));
        $this->info($force ? 'Forcing credential refresh...' : 'Checking cached credentials or refreshing if needed...');

        $turnServers = $iceService->warmupTurnCredentials($force);

        if (! empty($turnServers)) {
            $this->info('Successfully validated & cached TURN credentials.');
            $this->table(['URLs', 'Username'], array_map(function ($s) {
                return [
                    'URLs' => is_array($s['urls']) ? implode(', ', $s['urls']) : $s['urls'],
                    'Username' => $s['username'] ?? 'N/A',
                ];
            }, $turnServers));
            return Command::SUCCESS;
        }

        $this->warn('Could not obtain TURN credentials from provider. Please check domain and API/Secret keys in .env.');
        return Command::FAILURE;
    }
}

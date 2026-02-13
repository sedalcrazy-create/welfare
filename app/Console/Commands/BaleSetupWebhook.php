<?php

namespace App\Console\Commands;

use App\Services\BaleBot\BaleService;
use Illuminate\Console\Command;

class BaleSetupWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bale:setup-webhook
                            {--delete : Delete the webhook instead of setting it}
                            {--info : Show webhook information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup or manage Bale Bot webhook';

    /**
     * Execute the console command.
     */
    public function handle(BaleService $baleService): int
    {
        if ($this->option('delete')) {
            return $this->deleteWebhook($baleService);
        }

        if ($this->option('info')) {
            return $this->showWebhookInfo($baleService);
        }

        return $this->setupWebhook($baleService);
    }

    /**
     * تنظیم webhook
     */
    private function setupWebhook(BaleService $baleService): int
    {
        $this->info('🔧 Setting up Bale Bot webhook...');
        $this->newLine();

        $token = config('services.bale.token');
        $webhookUrl = config('services.bale.webhook_url');

        if (empty($token)) {
            $this->error('❌ BALE_BOT_TOKEN is not set in .env file!');
            return Command::FAILURE;
        }

        if (empty($webhookUrl)) {
            $this->error('❌ BALE_WEBHOOK_URL is not set in .env file!');
            return Command::FAILURE;
        }

        // ساخت URL کامل webhook
        $fullWebhookUrl = $webhookUrl . '/' . $token;

        $this->line("📍 Webhook URL: {$fullWebhookUrl}");
        $this->newLine();

        // ارسال درخواست setWebhook
        $this->info('⏳ Sending request to Bale API...');
        $result = $baleService->setWebhook($fullWebhookUrl);

        if ($result['ok'] ?? false) {
            $this->newLine();
            $this->info('✅ Webhook setup successful!');
            $this->newLine();
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line("📡 Webhook URL: {$fullWebhookUrl}");
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();
            $this->comment('💡 Tip: Use --info option to check webhook status');

            return Command::SUCCESS;
        } else {
            $this->newLine();
            $this->error('❌ Webhook setup failed!');
            $this->newLine();

            if (isset($result['description'])) {
                $this->line("Error: {$result['description']}");
            }

            if (isset($result['error'])) {
                $this->line("Error: {$result['error']}");
            }

            return Command::FAILURE;
        }
    }

    /**
     * حذف webhook
     */
    private function deleteWebhook(BaleService $baleService): int
    {
        $this->warn('🗑️  Deleting Bale Bot webhook...');
        $this->newLine();

        $result = $baleService->deleteWebhook();

        if ($result['ok'] ?? false) {
            $this->info('✅ Webhook deleted successfully!');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to delete webhook!');

            if (isset($result['description'])) {
                $this->line("Error: {$result['description']}");
            }

            return Command::FAILURE;
        }
    }

    /**
     * نمایش اطلاعات webhook
     */
    private function showWebhookInfo(BaleService $baleService): int
    {
        $this->info('📊 Fetching webhook information...');
        $this->newLine();

        $result = $baleService->getWebhookInfo();

        if ($result['ok'] ?? false) {
            $info = $result['result'] ?? [];

            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('📡 Webhook Information');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            $this->line("URL: " . ($info['url'] ?? 'Not set'));
            $this->line("Has Custom Certificate: " . ($info['has_custom_certificate'] ?? false ? 'Yes' : 'No'));
            $this->line("Pending Update Count: " . ($info['pending_update_count'] ?? 0));
            $this->newLine();

            if (isset($info['last_error_date'])) {
                $this->warn("⚠️  Last Error:");
                $this->line("   Date: " . date('Y-m-d H:i:s', $info['last_error_date']));
                $this->line("   Message: " . ($info['last_error_message'] ?? 'N/A'));
                $this->newLine();
            }

            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to fetch webhook info!');
            return Command::FAILURE;
        }
    }
}

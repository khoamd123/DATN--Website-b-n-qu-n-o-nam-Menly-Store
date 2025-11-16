<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateFundsForClubs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clubs:create-funds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo quỹ tự động cho tất cả CLB chưa có quỹ';

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
        $this->info('Đang kiểm tra và tạo quỹ cho các CLB chưa có quỹ...');
        
        $clubs = \App\Models\Club::all();
        $createdCount = 0;
        $skippedCount = 0;
        
        foreach ($clubs as $club) {
            $fund = \App\Models\Fund::where('club_id', $club->id)->first();
            
            if (!$fund) {
                $creatorId = $club->owner_id ?? $club->leader_id ?? 1;
                
                try {
                    \App\Models\Fund::create([
                        'club_id' => $club->id,
                        'name' => 'Quỹ ' . $club->name,
                        'description' => 'Quỹ tự động được tạo cho CLB',
                        'initial_amount' => 0,
                        'current_amount' => 0,
                        'status' => 'active',
                        'source' => 'Hệ thống',
                        'created_by' => $creatorId,
                    ]);
                    
                    $this->info("✓ Đã tạo quỹ cho CLB: {$club->name} (ID: {$club->id})");
                    $createdCount++;
                } catch (\Exception $e) {
                    $this->error("✗ Lỗi khi tạo quỹ cho CLB {$club->name}: " . $e->getMessage());
                }
            } else {
                $this->line("  CLB {$club->name} đã có quỹ: {$fund->name}");
                $skippedCount++;
            }
        }
        
        $this->newLine();
        $this->info("Hoàn thành!");
        $this->info("Đã tạo: {$createdCount} quỹ mới");
        $this->info("Đã bỏ qua: {$skippedCount} CLB đã có quỹ");
        
        return 0;
    }
}

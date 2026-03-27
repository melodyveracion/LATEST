<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetFundSourcesCommand extends Command
{
    protected $signature = 'university:reset-fund-sources
                            {--force : Required. Acknowledges permanent deletion of procurement rows that reference fund sources}
                            {--yes : Skip the interactive confirmation (use with --force)}
                            {--reset-departments : Also delete all department_units and clear users.department_unit_id (use if you changed office/college names in the data file)}
                            {--no-seed : Delete only; do not run UniversityReferenceDataSeeder}';

    protected $description = 'Remove all fund_sources and related PPMP/PR/consolidated data, clear unit fund assignments, then optionally re-seed reference data.';

    public function handle(): int
    {
        if (!$this->option('force')) {
            $this->error('Refusing to run without --force (this deletes PPMPs, purchase requests, consolidation, etc.).');

            return self::FAILURE;
        }

        if (!Schema::hasTable('fund_sources')) {
            $this->error('Table fund_sources does not exist.');

            return self::FAILURE;
        }

        $this->warn('This will delete inventories, deliveries, biddings, consolidated items, purchase requests, PPMPs, and all fund_sources rows.');
        $this->warn('Unit users will have fund_source_id cleared.');
        if ($this->option('reset-departments')) {
            $this->warn('With --reset-departments: all department_units removed and users.department_unit_id cleared.');
        }

        if (!$this->option('yes') && !$this->confirm('Continue?', false)) {
            return self::FAILURE;
        }

        Schema::disableForeignKeyConstraints();

        try {
            $this->purgeProcurementChain();
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'fund_source_id')) {
                DB::table('users')->update(['fund_source_id' => null]);
            }
            DB::table('fund_sources')->delete();
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('All rows removed from fund_sources (and dependent procurement data cleared).');

        if (!$this->option('no-seed')) {
            $this->call('db:seed', ['--class' => 'UniversityReferenceDataSeeder']);
        }

        return self::SUCCESS;
    }

    private function purgeProcurementChain(): void
    {
        foreach ([
            'inventories',
            'deliveries',
            'biddings',
            'consolidated_item_sources',
            'consolidated_items',
            'purchase_request_items',
            'purchase_requests',
            'ppmp_items',
            'ppmps',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
                $this->line("Cleared: {$table}");
            }
        }
    }
}

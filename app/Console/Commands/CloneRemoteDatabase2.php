<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneRemoteDatabase2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clone-remote-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Configure remote DB connection dynamically
        config([
            'database.connections.remote' => [
                'driver'    => 'mysql',
                'host'      => '52.15.83.225',
                'port'      => 3306,
                'database'  => 'admin_crm',
                'username'  => 'remote_user',
                'password'  => 'Print702!',
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
        ]);

        $remote = DB::connection('remote');
        $local  = DB::connection();

        $remoteDbName = config('database.connections.remote.database');
        $tableKey     = "Tables_in_{$remoteDbName}";
        $tables       = $remote->select('SHOW TABLES');

        $this->info('Disabling foreign key checks...');
        $local->statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$tableKey;
            $this->info("Cloning table: {$tableName}");
            if($tableName == 'invoices' || $tableName == 'invoice_items') {
                continue;
            }

            try {
                $data = $remote->table($tableName)->get();
                $local->table($tableName)->truncate();

                foreach ($data->chunk(500) as $chunk) {
                    $local->table($tableName)->insert(json_decode(json_encode($chunk), true));
                }

                $this->info("Cloned: {$tableName} ✔");
            } catch (\Exception $e) {
                $this->error("Failed on table: {$tableName} → " . $e->getMessage());
            }
        }

        $this->info('Enabling foreign key checks...');
        $local->statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('✔ Remote data cloned into local database successfully.');

    }
}

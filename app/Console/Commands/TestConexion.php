<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestConexion extends Command
{
    protected $signature = 'test:conexion';
    protected $description = 'Test';

    public function handle()
    {
        $nuevas = [
            ['id'=>89, 'cod_area'=>0, 'codigo'=>0, 'nombre'=>'DIRECC. ÁREA REC. Y REG. DOMINIAL', 'created_at'=>now(), 'updated_at'=>now()],
        ];

        foreach($nuevas as $o) {
            $existe = DB::connection('mysql_admin')->table('oficinas')->where('id', $o['id'])->exists();
            if (!$existe) {
                DB::connection('mysql_admin')->table('oficinas')->insert($o);
                $this->info("✓ Insertada MY:{$o['id']} | {$o['nombre']}");
            } else {
                $this->info("- Ya existe MY:{$o['id']}");
            }
        }
    }
}

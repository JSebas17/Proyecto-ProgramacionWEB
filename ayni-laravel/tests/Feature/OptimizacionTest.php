<?php

namespace Tests\Feature;

use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\PerfilTrabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Fase 7: eager loading, caché e índices.
 * Ejecutar: php artisan test --filter=OptimizacionTest
 */
class OptimizacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
    }

    /** Cuenta las consultas SQL que se hacen al abrir una URL. */
    private function consultasAl(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $total = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $total;
    }

    public function test_el_buscador_no_hace_una_consulta_por_trabajador(): void
    {
        $this->get('/'); // calienta el caché de catálogos
        $antes = $this->consultasAl('/');

        // 5 trabajadores más con su perfil: las consultas deben ser las mismas (sin N+1)
        foreach (range(1, 5) as $i) {
            $u = User::create([
                'name' => "Extra$i", 'apellidos' => 'Prueba', 'email' => "extra$i@test.pe", 'telefono' => '98765432' . $i,
                'password' => 'password123', 'rol' => 'trabajador', 'verificado' => true,
                'distrito_id' => Distrito::first()->id,
            ]);
            PerfilTrabajador::create(['user_id' => $u->id, 'oficio_id' => Oficio::first()->id]);
        }

        $this->assertSame($antes, $this->consultasAl('/'));
    }

    public function test_los_catalogos_se_leen_una_sola_vez(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Distrito::catalogo();
        Distrito::catalogo();
        Oficio::catalogo();
        Oficio::catalogo('id');
        Oficio::catalogo('id');
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(3, $consultas); // distritos + oficios por nombre + oficios por id
    }

    public function test_el_cache_del_catalogo_se_limpia_al_cambiar_el_catalogo(): void
    {
        $this->assertFalse(Distrito::catalogo()->contains('nombre', 'Distrito Nuevo'));
        $d = Distrito::create(['nombre' => 'Distrito Nuevo', 'provincia' => 'Cusco']);
        $this->assertTrue(Distrito::catalogo()->contains('nombre', 'Distrito Nuevo'));
        $d->delete();
        $this->assertFalse(Distrito::catalogo()->contains('nombre', 'Distrito Nuevo'));

        $this->assertFalse(Oficio::catalogo('id')->contains('nombre', 'Soldadura'));
        Oficio::create(['nombre' => 'Soldadura']);
        $this->assertTrue(Oficio::catalogo('id')->contains('nombre', 'Soldadura'));
        $this->assertTrue(Oficio::catalogo()->contains('nombre', 'Soldadura'));
    }

    public function test_el_panel_admin_usa_cache_y_se_actualiza_al_cambiar_datos(): void
    {
        $admin = User::where('rol', 'admin')->first();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->assertTrue(Cache::has('admin.metricas'));

        // Un servicio nuevo (o cualquier cambio de usuarios/calificaciones) descarta las cifras viejas
        $t = User::where('rol', 'trabajador')->first();
        $t->servicios()->create([
            'distrito_id' => Distrito::first()->id, 'titulo' => 'Otro', 'descripcion' => 'x', 'precio_desde' => 10,
        ]);
        $this->assertFalse(Cache::has('admin.metricas'));
    }

    public function test_los_indices_de_optimizacion_existen(): void
    {
        $this->assertTrue(Schema::hasIndex('users', 'users_rol_activo_verificado_idx'));
        $this->assertTrue(Schema::hasIndex('servicios', 'servicios_trabajador_estado_idx'));
        $this->assertTrue(Schema::hasIndex('calificaciones', 'calificaciones_autor_servicio_idx'));
    }

    /** Regresión: con un caché que serializa (database/file) los catálogos deben seguir siendo modelos. */
    public function test_los_catalogos_siguen_siendo_modelos_con_un_cache_que_serializa(): void
    {
        config(['cache.stores.array.serialize' => true]);
        Cache::purge('array');
        Cache::flush();

        Distrito::catalogo();                 // 1.ª lectura: llena el caché
        Oficio::catalogo();
        $distritos = Distrito::catalogo();    // 2.ª lectura: viene del caché serializado
        $oficios   = Oficio::catalogo();

        $this->assertInstanceOf(Distrito::class, $distritos->first());
        $this->assertInstanceOf(Oficio::class, $oficios->first());
        $this->assertNotEmpty($distritos->first()->nombre);
        $this->assertNotEmpty($oficios->first()->id);
    }
}

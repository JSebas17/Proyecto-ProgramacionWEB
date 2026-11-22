<?php

namespace Tests\Feature;

use App\Models\Calificacion;
use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\PerfilTrabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Entrega final: gráficas en blanco/negro/plomo, migas de pan y accesos del perfil.
 * Ejecutar: php artisan test --filter=GraficasNavegacionTest
 */
class GraficasNavegacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function trabajador(): User { return User::where('rol', 'trabajador')->where('verificado', true)->first(); }
    private function admin(): User      { return User::where('rol', 'admin')->first(); }

    public function test_la_distribucion_cuenta_todas_las_calificaciones(): void
    {
        $global = Calificacion::distribucion();
        $this->assertSame([5, 4, 3, 2, 1], array_keys($global));
        $this->assertSame(Calificacion::count(), array_sum($global));

        $t = $this->trabajador();
        $propia = Calificacion::distribucion($t->id);
        $this->assertSame($t->calificacionesRecibidas()->count(), array_sum($propia));
    }

    public function test_el_perfil_muestra_la_grafica_de_estrellas_y_los_accesos(): void
    {
        $t = $this->trabajador();
        $this->get(route('trabajadores.show', $t))
            ->assertOk()
            ->assertSee('Distribución de calificaciones')
            ->assertSee('5 ★: ', false)
            ->assertSee('Ruta de navegación')
            ->assertSee('href="#resenas"', false)
            ->assertSee('id="resenas"', false)
            ->assertSee('id="trabajos"', false);
    }

    public function test_un_trabajador_sin_resenas_no_muestra_la_grafica(): void
    {
        $u = User::create([
            'name' => 'Sin', 'apellidos' => 'Reseñas', 'email' => 'sin@test.pe', 'telefono' => '987654321',
            'password' => 'password123', 'rol' => 'trabajador', 'verificado' => true, 'distrito_id' => Distrito::first()->id,
        ]);
        PerfilTrabajador::create(['user_id' => $u->id, 'oficio_id' => Oficio::first()->id]);

        $this->get(route('trabajadores.show', $u))->assertOk()->assertDontSee('Distribución de calificaciones');
    }

    public function test_el_panel_admin_muestra_tres_graficas(): void
    {
        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Verificación de trabajadores')
            ->assertSee('Calificaciones por estrella')
            ->assertSee('Trabajadores por oficio')
            ->assertSee('Ruta de navegación');
    }

    public function test_el_panel_no_se_rompe_con_un_cache_viejo_sin_datos_de_graficas(): void
    {
        Cache::put('admin.metricas', [
            'trabajadores' => 1, 'verificados' => 1, 'pendientes' => 0, 'clientes' => 1,
            'servicios' => 1, 'calificaciones' => 0, 'promedio' => 0.0,
        ], 600);

        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertSee('Aún no hay calificaciones');
    }

    public function test_calificar_y_las_demas_pantallas_tienen_migas(): void
    {
        $cliente = User::create([
            'name' => 'Nuevo', 'apellidos' => 'Cliente', 'email' => 'nuevo@test.pe', 'telefono' => '900111222',
            'password' => 'password123', 'rol' => 'cliente', 'distrito_id' => Distrito::first()->id,
        ]);
        $this->actingAs($cliente)->get(route('calificaciones.create', $this->trabajador()))->assertOk()->assertSee('Ruta de navegación');
        $this->get('/mi-perfil')->assertOk()->assertSee('Ruta de navegación');
        $this->actingAs($this->trabajador())->get('/mis-servicios')->assertOk()->assertSee('Ruta de navegación');
        $this->actingAs($this->admin())->get('/admin/usuarios')->assertOk()->assertSee('Ruta de navegación');
    }

    public function test_la_paleta_de_la_app_es_solo_blanco_negro_y_plomo(): void
    {
        $css = file_get_contents(public_path('css/styles.css'));
        preg_match_all('/#([0-9A-Fa-f]{6})\b/', $css, $m);
        $this->assertNotEmpty($m[1]);

        foreach (array_unique($m[1]) as $hex) {
            $canales = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            $this->assertLessThanOrEqual(8, max($canales) - min($canales), "El color #$hex no es gris");
        }
    }
}

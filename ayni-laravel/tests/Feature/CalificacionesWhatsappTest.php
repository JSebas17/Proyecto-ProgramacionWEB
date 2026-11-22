<?php

namespace Tests\Feature;

use App\Models\Calificacion;
use App\Models\Distrito;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Fase 6: calificaciones y botón de WhatsApp.
 * Ejecutar: php artisan test --filter=CalificacionesWhatsappTest
 */
class CalificacionesWhatsappTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function trabajador(): User { return User::where('rol', 'trabajador')->where('verificado', true)->first(); }
    private function admin(): User      { return User::where('rol', 'admin')->first(); }

    /** Cliente sin calificaciones previas (el de ejemplo ya calificó todos los servicios sembrados). */
    private function clienteNuevo(): User
    {
        return User::create([
            'name' => 'Nuevo', 'apellidos' => 'Cliente', 'email' => 'nuevo@test.pe', 'telefono' => '900111222',
            'password' => 'password123', 'rol' => 'cliente', 'distrito_id' => Distrito::first()->id,
        ]);
    }

    private function datos(User $t, array $extra = []): array
    {
        return array_merge(['servicio_id' => $t->servicios()->first()->id, 'puntaje' => 5], $extra);
    }

    // ---------------------------------------------------------------- WhatsApp

    public function test_whatsapp_agrega_codigo_de_peru_y_codifica_el_saludo(): void
    {
        $u = new User(['name' => 'Lucía', 'telefono' => '987654321']);
        $this->assertSame('51987654321', $u->whatsapp);
        $this->assertSame('https://wa.me/51987654321?text=Hola%20Luc%C3%ADa%2C%20te%20vi%20en%20Ayni', $u->whatsapp_url);

        // Con espacios, "+" y código de país ya puesto
        $this->assertSame('51987654321', (new User(['telefono' => '+51 987 654 321']))->whatsapp);
    }

    public function test_whatsapp_es_null_si_no_hay_telefono_valido(): void
    {
        $this->assertNull((new User(['name' => 'Sin', 'telefono' => null]))->whatsapp_url);
        $this->assertNull((new User(['name' => 'Corto', 'telefono' => '12345']))->whatsapp_url);
    }

    public function test_perfil_y_tarjeta_muestran_el_enlace_de_whatsapp(): void
    {
        $t = $this->trabajador();

        $this->get(route('trabajadores.show', $t))
            ->assertOk()->assertSee('Contactar por WhatsApp')->assertSee($t->whatsapp_url, false);
        $this->get('/')->assertOk()->assertSee($t->whatsapp_url, false);
    }

    public function test_sin_telefono_no_se_muestra_un_enlace_roto(): void
    {
        $t = $this->trabajador();
        $t->update(['telefono' => null]);

        $this->get(route('trabajadores.show', $t))
            ->assertOk()->assertSee('Sin WhatsApp disponible')->assertDontSee('wa.me/?text', false);
        $this->get('/')->assertOk()->assertDontSee('wa.me/?text', false);
    }

    // ---------------------------------------------------------------- Calificaciones

    public function test_la_calificacion_valida_puntaje_chips_y_comentario(): void
    {
        $t = $this->trabajador();
        $this->actingAs($this->clienteNuevo());

        $this->post(route('calificaciones.store', $t), $this->datos($t, ['puntaje' => 0]))->assertSessionHasErrors('puntaje');
        $this->post(route('calificaciones.store', $t), $this->datos($t, ['puntaje' => 6]))->assertSessionHasErrors('puntaje');
        $this->post(route('calificaciones.store', $t), ['servicio_id' => $t->servicios()->first()->id])->assertSessionHasErrors('puntaje');
        $this->post(route('calificaciones.store', $t), $this->datos($t, ['chips' => ['Inventado']]))->assertSessionHasErrors('chips.0');
        $this->post(route('calificaciones.store', $t), $this->datos($t, ['comentario' => str_repeat('a', 501)]))->assertSessionHasErrors('comentario');

        $this->assertSame(0, Calificacion::where('autor_id', auth()->id())->count());
    }

    public function test_no_se_puede_calificar_un_servicio_de_otro_trabajador(): void
    {
        $t    = $this->trabajador();
        $otro = User::where('rol', 'trabajador')->where('id', '!=', $t->id)->first();

        $this->actingAs($this->clienteNuevo())
            ->post(route('calificaciones.store', $t), ['servicio_id' => $otro->servicios()->first()->id, 'puntaje' => 5])
            ->assertSessionHasErrors('servicio_id');
    }

    public function test_admin_y_el_propio_trabajador_no_pueden_calificar(): void
    {
        $t = $this->trabajador();

        foreach ([$this->admin(), $t] as $usuario) {
            $this->actingAs($usuario)->get(route('calificaciones.create', $t))
                ->assertRedirect(route('trabajadores.show', $t))->assertSessionHas('error');
            $this->post(route('calificaciones.store', $t), $this->datos($t))
                ->assertRedirect(route('trabajadores.show', $t))->assertSessionHas('error');
        }
        $this->assertSame(0, Calificacion::where('autor_id', $t->id)->count());
    }

    public function test_un_servicio_calificado_ya_no_se_ofrece_de_nuevo(): void
    {
        $t = $this->trabajador();
        $this->actingAs($this->clienteNuevo());

        $this->get(route('calificaciones.create', $t))->assertOk();
        $this->post(route('calificaciones.store', $t), $this->datos($t, ['chips' => ['Puntual', 'Buen trato']]))
            ->assertRedirect(route('trabajadores.show', $t));

        $guardada = Calificacion::where('autor_id', auth()->id())->first();
        $this->assertSame(5, $guardada->puntuacion);
        $this->assertSame(['Puntual', 'Buen trato'], $guardada->etiquetas);

        // El trabajador tiene un solo servicio: ya no queda nada por calificar
        $this->get(route('calificaciones.create', $t))
            ->assertRedirect(route('trabajadores.show', $t))->assertSessionHas('error');
        $this->post(route('calificaciones.store', $t), $this->datos($t))->assertSessionHas('error');
        $this->assertSame(1, Calificacion::where('autor_id', auth()->id())->count());
    }

    public function test_calificar_actualiza_promedio_y_cantidad_del_perfil(): void
    {
        $t = $this->trabajador();
        $antes = $t->calificacionesRecibidas()->count();

        $this->actingAs($this->clienteNuevo())
            ->post(route('calificaciones.store', $t), $this->datos($t, ['comentario' => 'Muy puntual']))
            ->assertRedirect();

        $this->assertSame($antes + 1, $t->calificacionesRecibidas()->count());
        $this->get(route('trabajadores.show', $t))->assertSee('Muy puntual');
    }

    public function test_calificar_tiene_limite_de_envios_por_minuto(): void
    {
        $t = $this->trabajador();
        $this->actingAs($this->clienteNuevo());

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('calificaciones.store', $t), $this->datos($t));
        }
        $this->post(route('calificaciones.store', $t), $this->datos($t))->assertStatus(429);
    }

    public function test_calificar_borra_el_cache_del_panel_admin(): void
    {
        $t = $this->trabajador();
        Cache::put('admin.metricas', ['viejo' => true], 600);

        $this->actingAs($this->clienteNuevo())->post(route('calificaciones.store', $t), $this->datos($t));

        $this->assertFalse(Cache::has('admin.metricas'));
    }
}

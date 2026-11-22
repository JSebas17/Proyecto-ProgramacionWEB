<?php

namespace Tests\Feature;

use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Pruebas de los flujos principales de Ayni: php artisan test */
class AyniTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    private function trabajador(): User { return User::where('rol', 'trabajador')->where('verificado', true)->first(); }
    private function cliente(): User    { return User::where('rol', 'cliente')->first(); }
    private function admin(): User      { return User::where('rol', 'admin')->first(); }

    private function datosRegistro(array $extra = []): array
    {
        return array_merge([
            'rol' => 'trabajador', 'nombre' => 'Lucía', 'apellidos' => 'Ccori Paz', 'dni' => '12345678',
            'telefono' => '987654321', 'email' => 'lucia@test.pe', 'distrito_id' => Distrito::first()->id,
            'clave' => 'secreto123', 'clave2' => 'secreto123', 'acepto' => '1',
        ], $extra);
    }

    public function test_paginas_publicas_cargan(): void
    {
        foreach (['/', '/login', '/registro', '/registro?rol=trabajador', '/trabajadores/' . $this->trabajador()->id] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/registro?rol=trabajador')->assertSee('value="trabajador"', false);
        $this->get('/no-existe')->assertNotFound()->assertSee('Volver al inicio');
    }

    public function test_filtros_y_orden_del_buscador(): void
    {
        $this->get('/?cuatro=1')->assertOk();                 // antes daba error 500
        $this->get('/?orden=precio&verif=0')->assertOk();
        $this->get('/?orden[]=x&q[]=y')->assertOk();          // parámetros raros no rompen
        $this->get('/?oficio=Pintura')->assertOk()->assertDontSee('Electricista ·');
        $this->get('/?distrito=Nada&verif=0')->assertSee('No encontramos trabajadores');
    }

    public function test_ofrecer_redirige_segun_el_rol(): void
    {
        $this->get('/ofrecer')->assertRedirect(route('registro', ['rol' => 'trabajador']));
        $this->actingAs($this->trabajador())->get('/ofrecer')->assertRedirect(route('publicar'));
        $this->actingAs($this->cliente())->get('/ofrecer')->assertRedirect(route('home'));
    }

    public function test_registro_con_foto_la_guarda_y_la_muestra(): void
    {
        $r = $this->post('/registro', $this->datosRegistro(['foto' => UploadedFile::fake()->image('yo.jpg', 900, 600)]));
        $r->assertRedirect(route('publicar'));

        $u = User::where('email', 'lucia@test.pe')->first();
        $this->assertNotNull($u->foto);
        Storage::disk('public')->assertExists($u->foto);
        $this->assertStringContainsString('storage/uploads/perfiles/', $u->foto_url);
        $this->assertAuthenticatedAs($u);
    }

    public function test_registro_valida_foto_y_consentimiento(): void
    {
        $this->post('/registro', $this->datosRegistro(['foto' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')]))
            ->assertSessionHasErrors('foto');
        $this->post('/registro', $this->datosRegistro(['acepto' => null]))->assertSessionHasErrors('acepto');
        $this->post('/registro', $this->datosRegistro(['rol' => 'admin']))->assertSessionHasErrors('rol');
    }

    public function test_login_vuelve_a_la_pagina_que_se_queria_abrir(): void
    {
        $t = $this->trabajador();
        $url = route('calificaciones.create', $t);
        $this->get($url)->assertRedirect('/login');
        $this->post('/login', ['email' => $this->cliente()->email, 'clave' => 'password123'])->assertRedirect($url);
    }

    public function test_mi_perfil_cambia_datos_y_foto(): void
    {
        $t = $this->trabajador();
        $this->actingAs($t)->get('/mi-perfil')->assertOk()->assertSee('Mi perfil');

        $this->actingAs($t)->put('/mi-perfil', [
            'nombre' => 'Nuevo', 'apellidos' => 'Nombre', 'telefono' => '911222333', 'distrito_id' => Distrito::first()->id,
            'oficio_id' => Oficio::first()->id, 'tarifa' => 70, 'anios_experiencia' => 8, 'descripcion' => 'Hago instalaciones',
            'disponible' => '1', 'foto' => UploadedFile::fake()->image('a.png', 500, 800),
        ])->assertRedirect(route('perfil.edit'));

        $t->refresh();
        $this->assertSame('Nuevo', $t->name);
        $this->assertSame(8, (int) $t->perfilTrabajador->anios_experiencia);
        Storage::disk('public')->assertExists($t->foto);

        // reemplazar borra la foto anterior; quitar la deja en null
        $vieja = $t->foto;
        $this->actingAs($t)->put('/mi-perfil', [
            'nombre' => 'Nuevo', 'apellidos' => 'Nombre', 'telefono' => '911222333', 'distrito_id' => $t->distrito_id,
            'oficio_id' => Oficio::first()->id, 'quitar_foto' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertNull($t->fresh()->foto);
        Storage::disk('public')->assertMissing($vieja);
        $this->assertFalse((bool) $t->fresh()->perfilTrabajador->disponible); // casilla desmarcada
    }

    public function test_cliente_edita_su_perfil_y_desactiva_cuenta(): void
    {
        $c = $this->cliente();
        $this->actingAs($c)->put('/mi-perfil', [
            'nombre' => 'Ana', 'apellidos' => 'Rojas', 'telefono' => '955111222', 'distrito_id' => $c->distrito_id,
            'preferencias_contacto' => 'WhatsApp', 'direccion_frecuente' => 'Wanchaq',
        ])->assertSessionHasNoErrors();
        $this->assertSame('WhatsApp', $c->fresh()->perfilCliente->preferencias_contacto);

        $this->delete('/mi-perfil', ['clave_actual' => 'mala'])->assertSessionHasErrors('clave_actual');
        $this->delete('/mi-perfil', ['clave_actual' => 'password123'])->assertRedirect(route('home'));
        $this->assertFalse($c->fresh()->activo);
        $this->assertGuest();
    }

    public function test_publicar_servicio_guarda_fotos_y_permite_quitar(): void
    {
        $t = $this->trabajador();
        $base = ['oficio_id' => Oficio::first()->id, 'distrito_id' => Distrito::first()->id, 'tarifa' => 80, 'descripcion' => 'Todo tipo'];

        $this->actingAs($t)->post('/servicios', $base + ['fotos' => [
            UploadedFile::fake()->image('1.jpg', 2000, 1500), UploadedFile::fake()->image('2.jpg'),
        ]])->assertRedirect(route('servicios.index'));

        $s = $t->servicios()->latest('id')->first();
        $this->assertCount(2, $s->fotos);                      // antes se perdían (faltaba en $fillable)
        Storage::disk('public')->assertExists($s->fotos[0]);

        $quitada = $s->fotos[0];
        $this->put("/servicios/{$s->id}", $base + ['quitar' => [$quitada]])->assertSessionHasNoErrors();
        $this->assertCount(1, $s->fresh()->fotos);
        Storage::disk('public')->assertMissing($quitada);

        // máximo 4 fotos entre actuales y nuevas
        $cinco = array_map(fn ($i) => UploadedFile::fake()->image("$i.jpg"), range(1, 4));
        $this->put("/servicios/{$s->id}", $base + ['fotos' => $cinco])->assertSessionHasErrors('fotos');

        $this->get('/mis-servicios')->assertOk();
        $this->get("/servicios/{$s->id}/editar")->assertOk();
    }

    public function test_otro_trabajador_no_edita_servicio_ajeno(): void
    {
        $dueno = $this->trabajador();
        $otro  = User::where('rol', 'trabajador')->where('id', '!=', $dueno->id)->first();
        $s = $dueno->servicios()->first();
        $this->actingAs($otro)->get("/servicios/{$s->id}/editar")->assertForbidden();
    }

    public function test_calificar_y_aparece_en_el_perfil(): void
    {
        $t = $this->trabajador();
        // Cliente nuevo: el de ejemplo ya calificó ese servicio y la app no permite repetir
        $c = User::create(['name' => 'Nuevo', 'apellidos' => 'Cliente', 'email' => 'nuevo@test.pe', 'telefono' => '900111222',
            'password' => 'password123', 'rol' => 'cliente', 'distrito_id' => Distrito::first()->id]);
        $s = $t->servicios()->first();
        $this->actingAs($c)->get(route('calificaciones.create', $t))->assertOk()->assertSee('Califica el trabajo');
        $this->post(route('calificaciones.store', $t), [
            'servicio_id' => $s->id, 'puntaje' => 5, 'chips' => ['Puntual'], 'comentario' => 'Excelente <b>trabajo</b>',
        ])->assertRedirect(route('trabajadores.show', $t));

        $this->get(route('trabajadores.show', $t))->assertSee('Excelente')->assertDontSee('<b>trabajo</b>', false); // XSS escapado

        // Segundo intento sobre el mismo servicio: bloqueado
        $this->post(route('calificaciones.store', $t), ['servicio_id' => $s->id, 'puntaje' => 4])->assertSessionHas('error');
    }

    public function test_permisos_por_rol(): void
    {
        $this->actingAs($this->cliente())->get('/publicar')->assertRedirect(route('home'));
        $this->actingAs($this->cliente())->get('/admin')->assertRedirect(route('home'));
        $this->actingAs($this->admin())->get('/admin/usuarios')->assertOk();
        $this->actingAs($this->admin())->get('/admin')->assertOk();
        $this->actingAs($this->admin())->get('/mi-perfil')->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Distrito;
use App\Models\User;
use App\Support\Auditoria;
use App\Support\Imagenes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** Pruebas de los parches OWASP Top 10:2025 (A01, A02, A04, A05, A06, A07, A09, A10). */
class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    private string $logSeguridad;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');

        // El canal "seguridad" escribe a un archivo temporal para poder leerlo en las pruebas
        $this->logSeguridad = tempnam(sys_get_temp_dir(), 'seg');
        config(['logging.channels.seguridad' => ['driver' => 'single', 'path' => $this->logSeguridad, 'level' => 'info']]);
    }

    protected function tearDown(): void
    {
        @unlink($this->logSeguridad);
        parent::tearDown();
    }

    private function cliente(): User { return User::where('rol', 'cliente')->first(); }

    private function datosRegistro(array $extra = []): array
    {
        return array_merge([
            'rol' => 'cliente', 'nombre' => 'Lucía', 'apellidos' => 'Ccori Paz', 'dni' => '12345678',
            'telefono' => '987654321', 'email' => 'lucia@test.pe', 'distrito_id' => Distrito::first()->id,
            'clave' => 'secreto123', 'clave2' => 'secreto123', 'acepto' => '1',
        ], $extra);
    }

    // ---- A02: cabeceras ----
    public function test_las_respuestas_llevan_cabeceras_de_seguridad(): void
    {
        $r = $this->get('/');
        $r->assertHeader('X-Frame-Options', 'DENY');
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('Referrer-Policy');
        $this->assertTrue($r->headers->has('Content-Security-Policy') || $r->headers->has('Content-Security-Policy-Report-Only'));
    }

    public function test_sin_debug_la_csp_bloquea_de_verdad(): void
    {
        config(['app.debug' => false]);
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    // ---- A01: sesión de cuenta desactivada ----
    public function test_una_cuenta_desactivada_pierde_la_sesion_al_instante(): void
    {
        $u = $this->cliente();
        $this->actingAs($u)->get('/mi-perfil')->assertOk();

        $u->update(['activo' => false]);

        $this->get('/mi-perfil')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ---- A07: login y contraseñas ----
    public function test_el_login_se_bloquea_tras_5_intentos_del_mismo_correo(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'admin@ayni.test', 'clave' => 'mala'])->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => 'admin@ayni.test', 'clave' => 'password123'])->assertStatus(429);
        $this->assertStringContainsString('login.bloqueado', file_get_contents($this->logSeguridad));
    }

    public function test_el_registro_rechaza_contrasenas_debiles(): void
    {
        foreach (['solo-letras', '1234567890', 'Password123', 'a1'] as $clave) {
            $this->post('/registro', $this->datosRegistro(['clave' => $clave, 'clave2' => $clave]))
                ->assertSessionHasErrors('clave');
        }

        $larga = str_repeat('a1', 40); // 80 caracteres: bcrypt ignoraría lo que pase de 72
        $this->post('/registro', $this->datosRegistro(['clave' => $larga, 'clave2' => $larga]))->assertSessionHasErrors('clave');

        $this->post('/registro', $this->datosRegistro())->assertSessionHasNoErrors();
    }

    // ---- A06: anti cuentas falsas ----
    public function test_el_registro_tiene_limite_por_hora(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->post('/registro', ['rol' => 'x']); // inválido, pero cuenta para el límite
        }

        $this->post('/registro', $this->datosRegistro())->assertStatus(429);
    }

    // ---- A05: comodines LIKE ----
    public function test_la_busqueda_ignora_comodines_like(): void
    {
        $todos   = $this->get('/?verif=0')->viewData('trabajadores')->total();
        $comodin = $this->get('/?verif=0&q=' . urlencode('%'))->viewData('trabajadores')->total();

        $this->assertSame($todos, $comodin); // "%" ya no filtra ni rompe, se ignora
        $this->get('/?q=' . urlencode(str_repeat('a', 500)))->assertOk();
    }

    // ---- A09: auditoría ----
    public function test_se_registran_login_fallido_y_exitoso_sin_exponer_datos(): void
    {
        $this->post('/login', ['email' => 'admin@ayni.test', 'clave' => 'secreto-incorrecto']);
        $this->post('/login', ['email' => 'admin@ayni.test', 'clave' => 'password123']);

        $log = file_get_contents($this->logSeguridad);
        $this->assertStringContainsString('login.fallido', $log);
        $this->assertStringContainsString('login.exitoso', $log);
        $this->assertStringContainsString('ad***@ayni.test', $log);
        $this->assertStringNotContainsString('secreto-incorrecto', $log);
        $this->assertStringNotContainsString('password123', $log);
    }

    public function test_las_acciones_del_admin_y_los_accesos_denegados_quedan_registrados(): void
    {
        $admin   = User::where('rol', 'admin')->first();
        $cliente = $this->cliente();

        $this->actingAs($admin)->patch(route('admin.usuarios.verificar', $cliente));
        $this->actingAs($cliente)->get(route('admin.panel'));

        $log = file_get_contents($this->logSeguridad);
        $this->assertStringContainsString('admin.verificacion', $log);
        $this->assertStringContainsString('acceso.denegado', $log);
    }

    public function test_enmascarar_correo_y_fallo_del_log_no_rompe(): void
    {
        $this->assertSame('lu***@test.pe', Auditoria::enmascarar('Lucia@test.pe'));
        $this->assertSame('(inválido)', Auditoria::enmascarar(['x']));

        // Ruta imposible de escribir: registrar() debe tragarse el error, no romper la petición
        config(['logging.channels.seguridad' => ['driver' => 'single', 'path' => '/no/existe/seg.log', 'level' => 'info']]);
        app('log')->forgetChannel('seguridad');
        Auditoria::registrar('prueba');
        $this->assertTrue(true);
    }

    // ---- A10: imágenes dañadas ----
    public function test_una_imagen_danada_se_rechaza_en_vez_de_guardarse(): void
    {
        if (! function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('GD no está activo.');
        }

        $ruta = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($ruta, 'esto no es una imagen');
        $falsa = new UploadedFile($ruta, 'foto.jpg', 'image/jpeg', null, true);

        try {
            Imagenes::avatar($falsa);
            $this->fail('Debió rechazar la imagen dañada.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('foto', $e->errors());
        }

        $this->assertEmpty(Storage::disk('public')->allFiles('uploads/perfiles'));
        @unlink($ruta);
    }

    public function test_la_pagina_500_es_autonoma_y_no_expone_detalles(): void
    {
        $html = view('errors.500')->render();

        $this->assertStringContainsString('Algo salió mal', $html);
        $this->assertStringNotContainsString('Exception', $html);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** CV en PDF del trabajador: guardado privado, validación y descarga con permisos. */
class CvTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function pdf(string $nombre = 'cv.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nombre, "%PDF-1.4\n% contenido de prueba\n");
    }

    private function trabajador(): User { return User::where('rol', 'trabajador')->first(); }

    private function datosRegistro(array $extra = []): array
    {
        return array_merge([
            'rol' => 'trabajador', 'nombre' => 'Lucía', 'apellidos' => 'Ccori Paz', 'dni' => '12345678',
            'telefono' => '987654321', 'email' => 'lucia@test.pe', 'distrito_id' => Distrito::first()->id,
            'clave' => 'secreto123', 'clave2' => 'secreto123', 'acepto' => '1',
        ], $extra);
    }

    private function conCv(User $u): string
    {
        $ruta = 'cvs/' . $u->id . '.pdf';
        Storage::disk('local')->put($ruta, "%PDF-1.4\nprueba");
        $u->update(['cv' => $ruta]);

        return $ruta;
    }

    public function test_el_registro_de_trabajador_guarda_el_cv_en_el_disco_privado(): void
    {
        $this->post('/registro', $this->datosRegistro(['cv' => $this->pdf()]))->assertSessionHasNoErrors();

        $ruta = User::where('email', 'lucia@test.pe')->first()->cv;
        $this->assertNotNull($ruta);
        Storage::disk('local')->assertExists($ruta);
        Storage::disk('public')->assertMissing($ruta); // nunca accesible por URL pública
    }

    public function test_un_cliente_no_guarda_cv_aunque_lo_envie(): void
    {
        $this->post('/registro', $this->datosRegistro(['rol' => 'cliente', 'cv' => $this->pdf()]))->assertSessionHasNoErrors();

        $this->assertNull(User::where('email', 'lucia@test.pe')->first()->cv);
        $this->assertEmpty(Storage::disk('local')->allFiles('cvs'));
    }

    public function test_el_cv_es_opcional(): void
    {
        $this->post('/registro', $this->datosRegistro())->assertSessionHasNoErrors();
        $this->assertNull(User::where('email', 'lucia@test.pe')->first()->cv);
    }

    public function test_rechaza_cv_que_no_es_pdf_o_es_muy_pesado(): void
    {
        // Imagen con nombre de otro tipo
        $this->post('/registro', $this->datosRegistro(['cv' => UploadedFile::fake()->image('cv.png')]))->assertSessionHasErrors('cv');
        // Pasa por la extensión .pdf pero NO empieza con %PDF-
        $this->post('/registro', $this->datosRegistro(['cv' => UploadedFile::fake()->createWithContent('falso.pdf', 'texto plano')]))
            ->assertSessionHasErrors('cv');
        // Más de 5 MB
        $this->post('/registro', $this->datosRegistro(['cv' => UploadedFile::fake()->create('grande.pdf', 6000, 'application/pdf')]))
            ->assertSessionHasErrors('cv');

        $this->assertEmpty(Storage::disk('local')->allFiles('cvs'));
        $this->assertSame(0, User::where('email', 'lucia@test.pe')->count());
    }

    public function test_el_cv_es_publico_solo_para_trabajadores_activos(): void
{
    $t = $this->trabajador();
    $this->conCv($t);
    $ruta = route('cv.descargar', $t);

    // Invitado (sin cuenta): puede verlo
    $r = $this->get($ruta);
    $r->assertOk()->assertHeader('Content-Type', 'application/pdf');

    // Cliente con sesión: también
    $this->actingAs(User::where('rol', 'cliente')->first())->get($ruta)->assertOk();

    // Cuenta desactivada: el público ya no lo ve (404), pero el dueño sí
    $t->update(['activo' => false]);
    $this->actingAs(User::where('rol', 'cliente')->first())->get($ruta)->assertNotFound();
    $this->actingAs(User::where('rol', 'admin')->first())->get($ruta)->assertOk();
}

    public function test_sin_cv_devuelve_404_al_dueno(): void
    {
        $this->actingAs($this->trabajador())->get(route('cv.descargar', $this->trabajador()))->assertNotFound();
    }

    public function test_mi_perfil_reemplaza_y_quita_el_cv(): void
    {
        $t     = $this->trabajador();
        $vieja = $this->conCv($t);
        $base  = [
            'nombre' => 'Juan', 'apellidos' => 'Quispe', 'telefono' => '987654321',
            'distrito_id' => Distrito::first()->id, 'oficio_id' => Oficio::first()->id,
        ];

        // Reemplazar
        $this->actingAs($t)->put('/mi-perfil', $base + ['cv' => $this->pdf('nuevo.pdf')])->assertSessionHasNoErrors();
        $nueva = $t->fresh()->cv;
        $this->assertNotSame($vieja, $nueva);
        Storage::disk('local')->assertMissing($vieja);
        Storage::disk('local')->assertExists($nueva);

        // Sin subir nada, el CV se conserva
        $this->actingAs($t)->put('/mi-perfil', $base)->assertSessionHasNoErrors();
        $this->assertSame($nueva, $t->fresh()->cv);

        // Quitar
        $this->actingAs($t)->put('/mi-perfil', $base + ['quitar_cv' => '1'])->assertSessionHasNoErrors();
        $this->assertNull($t->fresh()->cv);
        Storage::disk('local')->assertMissing($nueva);
    }

    public function test_los_formularios_muestran_el_campo_cv(): void
    {
        $this->get('/registro?rol=trabajador')->assertSee('name="cv"', false);
        $this->actingAs($this->trabajador())->get('/mi-perfil')->assertSee('name="cv"', false);
        $this->actingAs(User::where('rol', 'cliente')->first())->get('/mi-perfil')->assertDontSee('name="cv"', false);
    }
}

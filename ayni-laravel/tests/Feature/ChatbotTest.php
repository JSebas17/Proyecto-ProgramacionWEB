<?php

namespace Tests\Feature;

use App\Models\Oficio;
use App\Models\User;
use App\Support\Chatbot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Chatbot de respuestas fijas: contenido consistente y presencia según el tipo de usuario. */
class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_el_chatbot_aparece_en_el_inicio_con_datos_validos(): void
    {
        $html = $this->get('/')->assertOk()->assertSee('id="chatbot"', false)->getContent();

        preg_match('#<script type="application/json" id="chatbot-datos">(.*?)</script>#s', $html, $m);
        $datos = json_decode($m[1] ?? '', true);

        $this->assertIsArray($datos);
        $this->assertNotEmpty($datos['menu']);
        $this->assertArrayHasKey('otro', $datos['temas']);
    }

    public function test_el_admin_no_ve_el_chatbot(): void
    {
        $admin = User::where('rol', 'admin')->first();

        $this->actingAs($admin)->get(route('admin.panel'))->assertOk()->assertDontSee('id="chatbot"', false);
    }

    public function test_todos_los_ids_y_enlaces_existen_y_son_seguros(): void
    {
        foreach ([false, true] as $logueado) {
            $d = Chatbot::datos($logueado);

            foreach ($d['menu'] as $id) {
                $this->assertArrayHasKey($id, $d['temas'], "El menú apunta a un tema que no existe: $id");
            }
            foreach ($d['temas'] as $id => $tema) {
                $this->assertNotSame('', trim($tema['respuesta']), "Tema sin respuesta: $id");
                foreach ($tema['siguientes'] as $sig) {
                    $this->assertArrayHasKey($sig, $d['temas'], "$id sugiere un tema inexistente: $sig");
                }
                foreach ($tema['enlaces'] as $e) {
                    $this->assertTrue(
                        str_starts_with($e['url'], url('/')) || str_starts_with($e['url'], 'https://wa.me/'),
                        "Enlace no permitido en $id: {$e['url']}"
                    );
                }
            }
        }
    }

    public function test_los_temas_cambian_segun_haya_sesion(): void
    {
        $invitado = Chatbot::datos(false)['menu'];
        $conSesion = Chatbot::datos(true)['menu'];

        $this->assertContains('ser_trabajador', $invitado);
        $this->assertContains('crear_cuenta', $invitado);
        $this->assertNotContains('desactivar', $invitado);

        $this->assertContains('desactivar', $conSesion);
        $this->assertNotContains('ser_trabajador', $conSesion);
        $this->assertNotContains('acceso', $conSesion);
    }

    public function test_la_respuesta_de_oficios_usa_el_catalogo_real(): void
    {
        $nombre = Oficio::first()->nombre;

        $this->assertStringContainsString($nombre, Chatbot::datos(false)['temas']['oficios']['respuesta']);
    }

    public function test_el_whatsapp_de_soporte_es_opcional_y_se_limpia(): void
    {
        config(['chatbot.soporte_whatsapp' => null]);
        $this->assertSame([], Chatbot::datos(false)['temas']['otro']['enlaces']);

        config(['chatbot.soporte_whatsapp' => '+51 987-654-321 <script>']);
        $enlaces = Chatbot::datos(false)['temas']['otro']['enlaces'];
        $this->assertSame('https://wa.me/51987654321', $enlaces[0]['url']); // solo dígitos
    }
}

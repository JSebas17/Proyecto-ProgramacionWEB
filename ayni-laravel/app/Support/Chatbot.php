<?php

namespace App\Support;

use App\Models\Oficio;
use Throwable;

/**
 * Chatbot de respuestas fijas. Todo el contenido vive aquí: para cambiar o agregar una respuesta
 * basta editar o añadir un tema en catalogoTemas() (y su id en MENU si debe salir en el menú).
 *
 * Cada tema: titulo (texto del botón), respuesta, enlaces [texto, url], siguientes [ids sugeridos],
 * palabras (para entender lo que el usuario escribe) y solo ('invitado' | 'sesion' | null).
 */
class Chatbot
{
    /** Orden de los botones del menú principal (los temas "solo" se filtran según haya sesión o no). */
    private const MENU = [
        'buscar', 'oficios', 'contactar', 'tarifas', 'verificado', 'calificar',
        'ser_trabajador', 'crear_cuenta', 'cv', 'acceso', 'datos', 'desactivar',
    ];

    /** Datos que recibe el navegador (public/js/chatbot.js). */
    public static function datos(bool $logueado): array
    {
        $visibles = array_filter(self::catalogoTemas($logueado), function (array $t) use ($logueado) {
            return match ($t['solo'] ?? null) {
                'invitado' => ! $logueado,
                'sesion'   => $logueado,
                default    => true,
            };
        });

        $ids = array_keys($visibles);

        foreach ($visibles as &$tema) {
            $tema['siguientes'] = array_values(array_intersect($tema['siguientes'] ?? [], $ids));
            unset($tema['solo']);
        }
        unset($tema);

        return [
            'saludo' => '¡Hola! Soy el asistente de Ayni. Elige una pregunta o escribe la tuya.',
            'menu'   => array_values(array_intersect(self::MENU, $ids)),
            'temas'  => $visibles,
        ];
    }

    private static function catalogoTemas(bool $logueado): array
    {
        $enlace = fn (string $texto, string $url): array => ['texto' => $texto, 'url' => $url];

        // Si la base de datos falla, el chatbot no debe romper la página
        try {
            $oficios = Oficio::catalogo()->pluck('nombre')->implode(', ');
        } catch (Throwable) {
            $oficios = '';
        }

        $soporte = preg_replace('/\D/', '', (string) config('chatbot.soporte_whatsapp'));

        return [
            'buscar' => [
                'titulo' => '¿Cómo busco un trabajador?',
                'respuesta' => "En el inicio escribe un nombre u oficio y usa los filtros: distrito, oficio, \"solo verificados\" y \"4 estrellas o más\". También puedes ordenar por mejor calificados, más reseñas, menor tarifa o más recientes.",
                'enlaces' => [$enlace('Ir al buscador', route('home'))],
                'siguientes' => ['contactar', 'tarifas', 'verificado'],
                'palabras' => ['busc', 'encontr', 'filtro', 'trabajador', 'necesito', 'contrat'],
            ],
            'oficios' => [
                'titulo' => '¿Qué oficios hay?',
                'respuesta' => $oficios !== ''
                    ? "Los oficios disponibles ahora son: {$oficios}. Puedes filtrar por oficio en el buscador."
                    : 'Puedes ver los oficios disponibles en el filtro "Oficio" del buscador.',
                'enlaces' => [$enlace('Ir al buscador', route('home'))],
                'siguientes' => ['buscar', 'contactar'],
                'palabras' => ['oficio', 'tipos de trabajo', 'que servicios', 'que trabajos'],
            ],
            'contactar' => [
                'titulo' => '¿Cómo contacto a un trabajador?',
                'respuesta' => "Entra a su perfil y toca \"Contactar por WhatsApp\". Hablan directamente y acuerdan el trabajo y el precio entre ustedes.",
                'enlaces' => [$enlace('Ver trabajadores', route('home'))],
                'siguientes' => ['tarifas', 'verificado', 'calificar'],
                'palabras' => ['contact', 'whatsapp', 'escribir', 'llamar', 'hablar', 'telefono', 'numero'],
            ],
            'tarifas' => [
                'titulo' => '¿Cuánto cobran?',
                'respuesta' => "Cada trabajador publica una tarifa referencial (desde cuánto cobra). Si no la puso, aparece \"a convenir\". El precio final lo acuerdas directamente con él o ella.",
                'enlaces' => [],
                'siguientes' => ['contactar', 'verificado'],
                'palabras' => ['precio', 'cobr', 'tarifa', 'costo', 'cuesta', 'cuanto', 'pagar', 'pago'],
            ],
            'verificado' => [
                'titulo' => '¿Qué significa "verificado"?',
                'respuesta' => "Significa que el administrador revisó la identidad del trabajador. Es una ayuda para confiar, pero antes de contratar revisa también sus reseñas y fotos de trabajos.",
                'enlaces' => [],
                'siguientes' => ['calificar', 'contactar'],
                'palabras' => ['verific', 'confiable', 'confianza', 'identidad'],
            ],
            'calificar' => [
                'titulo' => '¿Cómo califico un trabajo?',
                'respuesta' => "Necesitas una cuenta. En el perfil del trabajador toca \"Calificar un trabajo\": eliges el servicio, de 1 a 5 estrellas y, si quieres, escribes un comentario. Cada servicio se califica una sola vez y no puedes calificarte a ti mismo.",
                'enlaces' => [$enlace('Ver trabajadores', route('home'))],
                'siguientes' => ['verificado', 'buscar'],
                'palabras' => ['calific', 'estrella', 'resena', 'opinar', 'opinion', 'comentario', 'puntuar'],
            ],
            'ser_trabajador' => [
                'titulo' => 'Quiero ofrecer mi servicio',
                'respuesta' => "Crea tu cuenta eligiendo \"Ofrezco mi servicio\". Después publicas tu servicio con oficio, distrito, tarifa y hasta 4 fotos de tus trabajos. También puedes subir tu CV en PDF, es opcional.",
                'enlaces' => [$enlace('Crear cuenta de trabajador', route('registro', ['rol' => 'trabajador']))],
                'siguientes' => ['cv', 'datos', 'verificado'],
                'palabras' => ['ofrec', 'publicar', 'anunciar', 'ser trabajador', 'trabajar'],
                'solo' => 'invitado',
            ],
            'crear_cuenta' => [
                'titulo' => 'Quiero crear mi cuenta',
                'respuesta' => "Regístrate eligiendo \"Busco trabajadores\". Necesitas nombre, apellidos, DNI, teléfono, correo y una contraseña de mínimo 8 caracteres con letras y números.",
                'enlaces' => [$enlace('Crear cuenta', route('registro'))],
                'siguientes' => ['datos', 'acceso'],
                'palabras' => ['cuenta', 'registr', 'crear', 'inscrib', 'unirme'],
                'solo' => 'invitado',
            ],
            'cv' => [
                'titulo' => '¿Puedo subir mi CV?',
                'respuesta' => "Si eres trabajador, sí: puedes subir tu CV en PDF (máximo 5 MB) al registrarte o después, desde \"Mi perfil\". Es opcional.",
                'enlaces' => $logueado ? [$enlace('Ir a Mi perfil', route('perfil.edit'))] : [$enlace('Crear cuenta de trabajador', route('registro', ['rol' => 'trabajador']))],
                'siguientes' => ['ser_trabajador', 'datos'],
                'palabras' => ['cv', 'curricul', 'pdf', 'hoja de vida'],
            ],
            'acceso' => [
                'titulo' => 'No puedo ingresar',
                'respuesta' => "Revisa que tu correo esté bien escrito. Si fallas varias veces seguidas, espera un minuto antes de intentar otra vez. Si tu cuenta fue desactivada, no podrás ingresar.",
                'enlaces' => [$enlace('Ir a ingresar', route('login'))],
                'siguientes' => ['crear_cuenta', 'datos'],
                'palabras' => ['ingresar', 'entrar', 'login', 'contrasena', 'clave', 'password', 'acceso', 'sesion', 'bloquead', 'olvid'],
                'solo' => 'invitado',
            ],
            'datos' => [
                'titulo' => '¿Qué datos míos se ven?',
                'respuesta' => "Tu DNI no se muestra públicamente: solo lo ve el administrador para verificar identidad. En el perfil de un trabajador se ven su nombre, distrito, foto, servicios y el botón de WhatsApp (que usa su número de teléfono).",
                'enlaces' => [],
                'siguientes' => ['verificado', 'desactivar'],
                'palabras' => ['datos', 'privacidad', 'dni', 'privado', 'informacion', 'personal', 'seguridad'],
            ],
            'desactivar' => [
                'titulo' => 'Quiero desactivar mi cuenta',
                'respuesta' => "En \"Mi perfil\", al final, está \"Desactivar mi cuenta\". Te pide tu contraseña para confirmar. Al desactivarla, tus servicios dejan de mostrarse.",
                'enlaces' => [$enlace('Ir a Mi perfil', route('perfil.edit'))],
                'siguientes' => ['datos'],
                'palabras' => ['desactiv', 'eliminar mi cuenta', 'borrar mi cuenta', 'cerrar cuenta', 'darme de baja', 'baja'],
                'solo' => 'sesion',
            ],
            // Respuesta cuando no se entiende lo escrito (no sale en el menú)
            'otro' => [
                'titulo' => 'Otra pregunta',
                'respuesta' => $soporte !== ''
                    ? 'No tengo una respuesta para eso. Elige una de las opciones o escríbenos por WhatsApp.'
                    : 'No tengo una respuesta para eso todavía. Elige una de las opciones:',
                'enlaces' => $soporte !== '' ? [$enlace('Escribir por WhatsApp', 'https://wa.me/' . $soporte)] : [],
                'siguientes' => [],
                'palabras' => [],
            ],
        ];
    }
}

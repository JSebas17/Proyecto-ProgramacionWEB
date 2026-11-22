<?php

return [
    /*
    | WhatsApp de soporte (opcional). Si lo defines en .env, cuando el chatbot no entienda una
    | pregunta ofrecerá un botón para escribir a ese número. Ejemplo: CHATBOT_SOPORTE_WHATSAPP=51987654321
    | Si lo dejas vacío, el chatbot simplemente vuelve a mostrar las opciones.
    */
    'soporte_whatsapp' => env('CHATBOT_SOPORTE_WHATSAPP'),
];

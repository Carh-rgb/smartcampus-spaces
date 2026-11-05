<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Layout de las paginas Livewire / Volt
    |--------------------------------------------------------------------------
    |
    | Las vistas de configuracion del perfil son componentes de pagina de Volt
    | y necesitan saber dentro de que layout se dibujan. Sin este valor,
    | Livewire busca un layout 'layouts.app' que este proyecto no tiene y la
    | pagina falla con "No hint path defined for [layouts]". Aqui se apunta al
    | layout maestro que usa el resto del sistema.
    |
    */

    'component_layout' => 'components.layouts.app',

];

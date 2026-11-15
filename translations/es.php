<?php
/**
 * 2009-2026 Tecnoacquisti.com — Spanish translations
 *
 * @author    Arte e Informatica <helpdesk@tecnoacquisti.com>
 * @copyright 2009-2026 Tecnoacquisti.com
 * @license   One Paid Licence By WebSite Using This Module.
 */
global $_MODULE;
$_MODULE = [];

// Module meta
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6c9fed73c672c6abedaa994eddcfb139'] = 'Faceted Search Rate Limiter';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_fc2f59de64f104909e39e1381919a3fe'] = 'Protege ps_facetedsearch contra el abuso de bots y las inundaciones de scrapers de IA mediante el parámetro ?q=.';

// Settings form
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_f4f70727dc34561dfde1a3c529b6205c'] = 'Configuración';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6fce8fb6b523579915341355dc4a54e8'] = 'Activar protección';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_93cba07454f06a4a960172bbd6e2a435'] = 'Sí';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_bafd7322c6e97d25b6299b5d6fe8920b'] = 'No';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_75e2df54adcb58fc1ae9729c6df2b35a'] = 'Solicitudes máximas por ventana (rate limit)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_4c4b956f49452edfba82a0c8a5f7d98a'] = 'Número máximo de solicitudes ?q= permitidas por IP en la ventana temporal. Predeterminado: 30.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_f4eb14dd8faf04b5672282f339e60316'] = 'Ventana temporal (segundos)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_2def075a897f420afd4d5447ccb89a4f'] = 'Duración de la ventana de rate-limit en segundos. Predeterminado: 60.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6181ff3e92854c039ce4c10d085e51d7'] = 'Umbral de puntuación de bot';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_e9388dbf238b125415284c9215e2d7de'] = 'Bloquea cuando la puntuación combinada de señales alcanza este valor. Mayor = más permisivo. Puntuaciones: HTTP/1.0 +4, sin User-Agent +3, sin cookie PS +2, sin Referer +1.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_ac58cbc48768097eb71ee0b9ccd3324d'] = 'Muy agresivo (cualquier señal bloquea)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_42d2538d10b88d08d87bb7f5d97d675a'] = 'Agresivo (solo sin cookie bloquea)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_d32f282b7d765ad213f50f3b47da1c32'] = 'Recomendado (HTTP/1.0, sin UA, sin cookie+sin referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_fb117c7b861e642e2dcd233d24724660'] = 'Recomendado · predeterminado (HTTP/1.0 o sin UA+sin referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_8732ca6151769849628559901389aec3'] = 'Estricto (HTTP/1.0+sin referer o sin cookie+sin UA)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_1312a626c238b6003536fc44b4fad1e2'] = 'Muy estricto (HTTP/1.0+sin cookie o las tres señales débiles)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_3b55f8a0cc7da5d64d989b446f17c589'] = 'Máximo (HTTP/1.0+sin UA o HTTP/1.0+sin cookie+sin referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_c9cc8cce247e49bae79f15173ce97354'] = 'Guardar';

// Validation messages
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_212060d866589691fb44b12dc518fd71'] = 'Las solicitudes máximas deben estar entre 1 y 10000.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_0190eb13ed4ace65e3db8b9dda0c9bff'] = 'La ventana temporal debe estar entre 1 y 3600 segundos.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_7da715460b0cbb1641324c35bade6059'] = 'El umbral de puntuación de bot debe estar entre 1 y 7.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_4d9ac946f92f7211e40671513c72fd09'] = 'Configuración guardada correctamente.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_69242344e42d0b36296e7e1e18023684'] = 'Direcciones IP no válidas:';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_257d1f8c23bb6196950697134bfdfca7'] = 'Lista blanca guardada.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_7123634f50fb9fbbccf9f88f55e78091'] = 'Archivo de registro vaciado.';

// blocked.tpl — front-end blocked page
$_MODULE['<{tec_facetlimit}prestashop>blocked_eeb13468b73d93fa8bcbe3ebae6df720'] = 'Demasiadas solicitudes';
$_MODULE['<{tec_facetlimit}prestashop>blocked_4867b3ffdfd5200f727d3adff107e908'] = 'Has enviado demasiadas solicitudes de filtro en poco tiempo. Espera un momento antes de continuar tu navegación.';
$_MODULE['<{tec_facetlimit}prestashop>blocked_0557fa923dcee4d0f86b1409f5c2167f'] = 'Volver';

// configure.tpl — scoring reference panel
$_MODULE['<{tec_facetlimit}prestashop>configure_c50a9084860fdf47af6deee648281022'] = 'Backend del rate limiter';
$_MODULE['<{tec_facetlimit}prestashop>configure_bd225428167fbb7076c17e6f5b6d287c'] = 'APCu — contador atómico en memoria compartida activo.';
$_MODULE['<{tec_facetlimit}prestashop>configure_13465598b6b6a6370a168e7f7a74c44e'] = 'APCu no disponible — usando el fallback PS Cache. Instala la extensión php-apcu para un rate limiting más fiable.';
$_MODULE['<{tec_facetlimit}prestashop>configure_c816a03ed27a1cea6346a954f4f4cda3'] = 'Referencia de puntuación de bot';
$_MODULE['<{tec_facetlimit}prestashop>configure_085fea7abdc5d904fe69a3081efd7398'] = 'Señal';
$_MODULE['<{tec_facetlimit}prestashop>configure_5dd135d1bcfa7f63c3b7f25425c2a4a1'] = 'Puntuación';
$_MODULE['<{tec_facetlimit}prestashop>configure_f4c6f851b00d5518bf888815de279aba'] = 'Notas';
$_MODULE['<{tec_facetlimit}prestashop>configure_20340bff071e855b1291a3fb36ab2012'] = 'Bloquea solo — ningún navegador real usa HTTP/1.0 desde 1996. Desactivado automáticamente cuando se detecta una cabecera de reverse proxy (X-Forwarded-For, CF-Connecting-IP).';
$_MODULE['<{tec_facetlimit}prestashop>configure_7b4fe692051a816f3968424db75d12c4'] = 'Sin User-Agent';
$_MODULE['<{tec_facetlimit}prestashop>configure_09749c549dfac2fee1c5388afbb49ce5'] = 'Bloquea solo — cliente no navegador.';
$_MODULE['<{tec_facetlimit}prestashop>configure_63807e5c2e4bdcb3446579bffd05ae76'] = 'Sin cookie PrestaShop-*';
$_MODULE['<{tec_facetlimit}prestashop>configure_14b53ff94f56f6b1afcd26bb1a98c03e'] = 'Los usuarios reales siempre adquieren la cookie de sesión en la primera visita.';
$_MODULE['<{tec_facetlimit}prestashop>configure_743d14c7de2c27fa94db27793a7a7415'] = 'Sin Referer';
$_MODULE['<{tec_facetlimit}prestashop>configure_58c1051ca1e6921667fac4bf4537ae08'] = 'Los navegadores siempre envían el Referer en la navegación y las llamadas AJAX del mismo origen.';
$_MODULE['<{tec_facetlimit}prestashop>configure_e59df17c169a1fc4282ebaf244619506'] = 'Umbral predeterminado = 3: HTTP/1.0 solo (4) o UA ausente solo (3) bloquean inmediatamente. Sin cookie + sin referer (2+1=3) también bloquea. Los visitantes nuevos con enlace directo (solo sin cookie, puntuación 2) pasan.';

// configure.tpl — whitelist panel
$_MODULE['<{tec_facetlimit}prestashop>configure_0524317c1d882d879ecce066c0765424'] = 'Lista blanca de IP';
$_MODULE['<{tec_facetlimit}prestashop>configure_3e5891188683540a724db87cf0b346bc'] = 'IPs en lista blanca';
$_MODULE['<{tec_facetlimit}prestashop>configure_14dc1e3d9f292c07e349da1a1d21da10'] = 'ej. 203.0.113.1 o 2001:db8::1';
$_MODULE['<{tec_facetlimit}prestashop>configure_ec211f7c20af43e742bf2570c3cb84f9'] = 'Añadir';
$_MODULE['<{tec_facetlimit}prestashop>configure_c770d8e0d1d1943ce239c64dbd6acc20'] = 'Añadir mi IP';
$_MODULE['<{tec_facetlimit}prestashop>configure_73737140d636c9d43e20024dab8f2cc3'] = 'Direcciones IPv4 e IPv6. Las IPs en esta lista omiten todos los controles de rate-limit y bot.';
$_MODULE['<{tec_facetlimit}prestashop>configure_d83651a81af0cbb569c38b819dea1b4c'] = 'Guardar lista blanca';

// configure.tpl — log panel
$_MODULE['<{tec_facetlimit}prestashop>configure_8ed72d8592f2bb507e82b9c23632605f'] = 'Registro de solicitudes bloqueadas';
$_MODULE['<{tec_facetlimit}prestashop>configure_2b6c2e165695b25f192dcd8b3c0ceada'] = '¿Vaciar el archivo de registro?';
$_MODULE['<{tec_facetlimit}prestashop>configure_6bee5661e926f636f142c7f60a227116'] = 'Vaciar registro';
$_MODULE['<{tec_facetlimit}prestashop>configure_88a9663240619395e55ab73a81ceaca0'] = 'Últimas 100 entradas bloqueadas, las más recientes primero.';
$_MODULE['<{tec_facetlimit}prestashop>configure_a76d4ef5f3f6a672bbfab2865563e530'] = 'Hora';
$_MODULE['<{tec_facetlimit}prestashop>configure_a1fa27779242b4902f7ae3bdd5c6d508'] = 'Tipo';
$_MODULE['<{tec_facetlimit}prestashop>configure_cadd2b2ad06d8a0caee658e3c05e615a'] = 'Señales';
$_MODULE['<{tec_facetlimit}prestashop>configure_00b8d7e7eb38979a4e1dd444bdc9571e'] = 'No hay solicitudes bloqueadas registradas aún.';

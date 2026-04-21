<?php
/**
 * 2009-2026 Tecnoacquisti.com — German translations
 *
 * @author    Arte e Informatica <helpdesk@tecnoacquisti.com>
 * @copyright 2009-2026 Tecnoacquisti.com
 * @license   One Paid Licence By WebSite Using This Module.
 */

global $_MODULE;
$_MODULE = [];

// Module meta
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6c9fed73c672c6abedaa994eddcfb139'] = 'Faceted Search Rate Limiter';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_fc2f59de64f104909e39e1381919a3fe'] = 'Schützt ps_facetedsearch vor Bot-Missbrauch und KI-Scraper-Floods über den ?q=-Parameter.';

// Settings form
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_f4f70727dc34561dfde1a3c529b6205c'] = 'Einstellungen';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6fce8fb6b523579915341355dc4a54e8'] = 'Schutz aktivieren';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_93cba07454f06a4a960172bbd6e2a435'] = 'Ja';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_bafd7322c6e97d25b6299b5d6fe8920b'] = 'Nein';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_75e2df54adcb58fc1ae9729c6df2b35a'] = 'Maximale Anfragen pro Zeitfenster (Rate-Limit)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_4c4b956f49452edfba82a0c8a5f7d98a'] = 'Maximale Anzahl erlaubter ?q=-Anfragen pro IP im Zeitfenster. Standard: 30.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_f4eb14dd8faf04b5672282f339e60316'] = 'Zeitfenster (Sekunden)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_2def075a897f420afd4d5447ccb89a4f'] = 'Dauer des Rate-Limit-Zeitfensters in Sekunden. Standard: 60.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6181ff3e92854c039ce4c10d085e51d7'] = 'Bot-Score-Schwellenwert';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_e9388dbf238b125415284c9215e2d7de'] = 'Blockiert, wenn der kombinierte Signal-Score diesen Wert erreicht. Höher = permissiver. Scores: HTTP/1.0 +4, kein User-Agent +3, kein PS-Cookie +2, kein Referer +1.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_ac58cbc48768097eb71ee0b9ccd3324d'] = 'Sehr aggressiv (jedes Signal blockiert)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_42d2538d10b88d08d87bb7f5d97d675a'] = 'Aggressiv (nur kein Cookie blockiert)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_27b2dd67f7d06f44071fce60728515d7'] = 'Empfohlen · Standard (HTTP/1.0, kein UA, kein Cookie+kein Referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_2c8572181c79b89f62ead3f3f66c8490'] = 'Konservativ (HTTP/1.0 oder kein UA+kein Referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_8732ca6151769849628559901389aec3'] = 'Streng (HTTP/1.0+kein Referer oder kein Cookie+kein UA)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_1312a626c238b6003536fc44b4fad1e2'] = 'Sehr streng (HTTP/1.0+kein Cookie oder alle drei schwachen Signale)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_3b55f8a0cc7da5d64d989b446f17c589'] = 'Maximum (HTTP/1.0+kein UA oder HTTP/1.0+kein Cookie+kein Referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_c9cc8cce247e49bae79f15173ce97354'] = 'Speichern';

// Validation messages
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_212060d866589691fb44b12dc518fd71'] = 'Die maximalen Anfragen müssen zwischen 1 und 10000 liegen.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_0190eb13ed4ace65e3db8b9dda0c9bff'] = 'Das Zeitfenster muss zwischen 1 und 3600 Sekunden liegen.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_7da715460b0cbb1641324c35bade6059'] = 'Der Bot-Score-Schwellenwert muss zwischen 1 und 7 liegen.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_4d9ac946f92f7211e40671513c72fd09'] = 'Einstellungen erfolgreich gespeichert.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_69242344e42d0b36296e7e1e18023684'] = 'Ungültige IP-Adressen:';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_257d1f8c23bb6196950697134bfdfca7'] = 'Whitelist gespeichert.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_7123634f50fb9fbbccf9f88f55e78091'] = 'Log-Datei geleert.';

// configure.tpl — scoring reference panel
$_MODULE['<{tec_facetlimit}prestashop>configure_94754249b11cc03e82dba29f5541f994'] = 'Rate-Limiter-Backend';
$_MODULE['<{tec_facetlimit}prestashop>configure_d9045d1cea7549afb30f8446667b3833'] = 'APCu — atomarer Shared-Memory-Zähler aktiv.';
$_MODULE['<{tec_facetlimit}prestashop>configure_06449291b9c5e1e44fb9328bfdaf4f55'] = 'APCu nicht verfügbar — PS-Cache-Fallback wird verwendet. Installieren Sie die php-apcu-Erweiterung für zuverlässigeres Rate-Limiting.';
$_MODULE['<{tec_facetlimit}prestashop>configure_c816a03ed27a1cea6346a954f4f4cda3'] = 'Bot-Score-Referenz';
$_MODULE['<{tec_facetlimit}prestashop>configure_085fea7abdc5d904fe69a3081efd7398'] = 'Signal';
$_MODULE['<{tec_facetlimit}prestashop>configure_5dd135d1bcfa7f63c3b7f25425c2a4a1'] = 'Score';
$_MODULE['<{tec_facetlimit}prestashop>configure_f4c6f851b00d5518bf888815de279aba'] = 'Hinweise';
$_MODULE['<{tec_facetlimit}prestashop>configure_5c0a7dc0964276bbee0efa24d2838588'] = 'Blockiert allein — kein echter Browser nutzt HTTP/1.0 seit 1996.';
$_MODULE['<{tec_facetlimit}prestashop>configure_7b4fe692051a816f3968424db75d12c4'] = 'Kein User-Agent';
$_MODULE['<{tec_facetlimit}prestashop>configure_09749c549dfac2fee1c5388afbb49ce5'] = 'Blockiert allein — kein Browser-Client.';
$_MODULE['<{tec_facetlimit}prestashop>configure_63807e5c2e4bdcb3446579bffd05ae76'] = 'Kein PrestaShop-*-Cookie';
$_MODULE['<{tec_facetlimit}prestashop>configure_14b53ff94f56f6b1afcd26bb1a98c03e'] = 'Echte Nutzer erwerben den Sitzungs-Cookie beim ersten Seitenaufruf.';
$_MODULE['<{tec_facetlimit}prestashop>configure_743d14c7de2c27fa94db27793a7a7415'] = 'Kein Referer';
$_MODULE['<{tec_facetlimit}prestashop>configure_58c1051ca1e6921667fac4bf4537ae08'] = 'Browser senden den Referer immer bei Navigation und Same-Origin-AJAX.';
$_MODULE['<{tec_facetlimit}prestashop>configure_e59df17c169a1fc4282ebaf244619506'] = 'Standardschwelle = 3: HTTP/1.0 allein (4) oder fehlender UA allein (3) blockieren sofort. Kein Cookie + kein Referer (2+1=3) blockiert ebenfalls. Erstbesucher mit Direktlink (nur kein Cookie, Score 2) werden durchgelassen.';

// configure.tpl — whitelist panel
$_MODULE['<{tec_facetlimit}prestashop>configure_0524317c1d882d879ecce066c0765424'] = 'IP-Whitelist';
$_MODULE['<{tec_facetlimit}prestashop>configure_3e5891188683540a724db87cf0b346bc'] = 'Whitelisted IPs';
$_MODULE['<{tec_facetlimit}prestashop>configure_14dc1e3d9f292c07e349da1a1d21da10'] = 'z.B. 203.0.113.1 oder 2001:db8::1';
$_MODULE['<{tec_facetlimit}prestashop>configure_ec211f7c20af43e742bf2570c3cb84f9'] = 'Hinzufügen';
$_MODULE['<{tec_facetlimit}prestashop>configure_c770d8e0d1d1943ce239c64dbd6acc20'] = 'Meine IP hinzufügen';
$_MODULE['<{tec_facetlimit}prestashop>configure_73737140d636c9d43e20024dab8f2cc3'] = 'IPv4- und IPv6-Adressen. IPs in dieser Liste umgehen alle Rate-Limit- und Bot-Prüfungen.';
$_MODULE['<{tec_facetlimit}prestashop>configure_d83651a81af0cbb569c38b819dea1b4c'] = 'Whitelist speichern';

// configure.tpl — log panel
$_MODULE['<{tec_facetlimit}prestashop>configure_8ed72d8592f2bb507e82b9c23632605f'] = 'Log blockierter Anfragen';
$_MODULE['<{tec_facetlimit}prestashop>configure_2b6c2e165695b25f192dcd8b3c0ceada'] = 'Log-Datei leeren?';
$_MODULE['<{tec_facetlimit}prestashop>configure_6bee5661e926f636f142c7f60a227116'] = 'Log leeren';
$_MODULE['<{tec_facetlimit}prestashop>configure_88a9663240619395e55ab73a81ceaca0'] = 'Letzte 100 blockierte Einträge, neueste zuerst.';
$_MODULE['<{tec_facetlimit}prestashop>configure_a76d4ef5f3f6a672bbfab2865563e530'] = 'Zeit';
$_MODULE['<{tec_facetlimit}prestashop>configure_a1fa27779242b4902f7ae3bdd5c6d508'] = 'Typ';
$_MODULE['<{tec_facetlimit}prestashop>configure_cadd2b2ad06d8a0caee658e3c05e615a'] = 'Signale';
$_MODULE['<{tec_facetlimit}prestashop>configure_00b8d7e7eb38979a4e1dd444bdc9571e'] = 'Noch keine blockierten Anfragen protokolliert.';

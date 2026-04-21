<?php
/**
 * 2009-2026 Tecnoacquisti.com — French translations
 *
 * @author    Arte e Informatica <helpdesk@tecnoacquisti.com>
 * @copyright 2009-2026 Tecnoacquisti.com
 * @license   One Paid Licence By WebSite Using This Module.
 */

global $_MODULE;
$_MODULE = [];

// Module meta
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6c9fed73c672c6abedaa994eddcfb139'] = 'Faceted Search Rate Limiter';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_fc2f59de64f104909e39e1381919a3fe'] = 'Protège ps_facetedsearch contre les abus de bots et les attaques de scrapers IA via le paramètre ?q=.';

// Settings form
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_f4f70727dc34561dfde1a3c529b6205c'] = 'Paramètres';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6fce8fb6b523579915341355dc4a54e8'] = 'Activer la protection';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_93cba07454f06a4a960172bbd6e2a435'] = 'Oui';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_bafd7322c6e97d25b6299b5d6fe8920b'] = 'Non';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_75e2df54adcb58fc1ae9729c6df2b35a'] = 'Requêtes maximales par fenêtre (rate limit)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_4c4b956f49452edfba82a0c8a5f7d98a'] = 'Nombre maximum de requêtes ?q= autorisées par IP dans la fenêtre temporelle. Défaut : 30.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_f4eb14dd8faf04b5672282f339e60316'] = 'Fenêtre temporelle (secondes)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_2def075a897f420afd4d5447ccb89a4f'] = 'Durée de la fenêtre de rate-limit en secondes. Défaut : 60.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_6181ff3e92854c039ce4c10d085e51d7'] = 'Seuil du score bot';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_e9388dbf238b125415284c9215e2d7de'] = 'Bloque lorsque le score combiné des signaux atteint cette valeur. Plus élevé = plus permissif. Scores : HTTP/1.0 +4, pas de User-Agent +3, pas de cookie PS +2, pas de Referer +1.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_ac58cbc48768097eb71ee0b9ccd3324d'] = 'Très agressif (n\'importe quel signal bloque)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_42d2538d10b88d08d87bb7f5d97d675a'] = 'Agressif (absence de cookie seule bloque)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_27b2dd67f7d06f44071fce60728515d7'] = 'Recommandé · défaut (HTTP/1.0, pas d\'UA, pas de cookie+pas de referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_2c8572181c79b89f62ead3f3f66c8490'] = 'Conservateur (HTTP/1.0 ou pas d\'UA+pas de referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_8732ca6151769849628559901389aec3'] = 'Strict (HTTP/1.0+pas de referer ou pas de cookie+pas d\'UA)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_1312a626c238b6003536fc44b4fad1e2'] = 'Très strict (HTTP/1.0+pas de cookie ou les trois signaux faibles)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_3b55f8a0cc7da5d64d989b446f17c589'] = 'Maximum (HTTP/1.0+pas d\'UA ou HTTP/1.0+pas de cookie+pas de referer)';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_c9cc8cce247e49bae79f15173ce97354'] = 'Enregistrer';

// Validation messages
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_212060d866589691fb44b12dc518fd71'] = 'Les requêtes maximales doivent être comprises entre 1 et 10000.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_0190eb13ed4ace65e3db8b9dda0c9bff'] = 'La fenêtre temporelle doit être comprise entre 1 et 3600 secondes.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_7da715460b0cbb1641324c35bade6059'] = 'Le seuil du score bot doit être compris entre 1 et 7.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_4d9ac946f92f7211e40671513c72fd09'] = 'Paramètres enregistrés avec succès.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_69242344e42d0b36296e7e1e18023684'] = 'Adresses IP invalides :';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_257d1f8c23bb6196950697134bfdfca7'] = 'Liste blanche enregistrée.';
$_MODULE['<{tec_facetlimit}prestashop>tec_facetlimit_7123634f50fb9fbbccf9f88f55e78091'] = 'Fichier journal vidé.';

// configure.tpl — scoring reference panel
$_MODULE['<{tec_facetlimit}prestashop>configure_94754249b11cc03e82dba29f5541f994'] = 'Backend du rate limiter';
$_MODULE['<{tec_facetlimit}prestashop>configure_d9045d1cea7549afb30f8446667b3833'] = 'APCu — compteur atomique en mémoire partagée actif.';
$_MODULE['<{tec_facetlimit}prestashop>configure_06449291b9c5e1e44fb9328bfdaf4f55'] = 'APCu non disponible — utilisation du fallback PS Cache. Installez l\'extension php-apcu pour un rate limiting plus fiable.';
$_MODULE['<{tec_facetlimit}prestashop>configure_c816a03ed27a1cea6346a954f4f4cda3'] = 'Référence du score bot';
$_MODULE['<{tec_facetlimit}prestashop>configure_085fea7abdc5d904fe69a3081efd7398'] = 'Signal';
$_MODULE['<{tec_facetlimit}prestashop>configure_5dd135d1bcfa7f63c3b7f25425c2a4a1'] = 'Score';
$_MODULE['<{tec_facetlimit}prestashop>configure_f4c6f851b00d5518bf888815de279aba'] = 'Notes';
$_MODULE['<{tec_facetlimit}prestashop>configure_5c0a7dc0964276bbee0efa24d2838588'] = 'Bloque seul — aucun vrai navigateur n\'utilise HTTP/1.0 depuis 1996.';
$_MODULE['<{tec_facetlimit}prestashop>configure_7b4fe692051a816f3968424db75d12c4'] = 'Pas de User-Agent';
$_MODULE['<{tec_facetlimit}prestashop>configure_09749c549dfac2fee1c5388afbb49ce5'] = 'Bloque seul — client non-navigateur.';
$_MODULE['<{tec_facetlimit}prestashop>configure_63807e5c2e4bdcb3446579bffd05ae76'] = 'Pas de cookie PrestaShop-*';
$_MODULE['<{tec_facetlimit}prestashop>configure_14b53ff94f56f6b1afcd26bb1a98c03e'] = 'Les vrais utilisateurs acquièrent toujours le cookie de session à la première visite.';
$_MODULE['<{tec_facetlimit}prestashop>configure_743d14c7de2c27fa94db27793a7a7415'] = 'Pas de Referer';
$_MODULE['<{tec_facetlimit}prestashop>configure_58c1051ca1e6921667fac4bf4537ae08'] = 'Les navigateurs envoient toujours le Referer lors de la navigation et des AJAX de même origine.';
$_MODULE['<{tec_facetlimit}prestashop>configure_e59df17c169a1fc4282ebaf244619506'] = 'Seuil par défaut = 3 : HTTP/1.0 seul (4) ou UA manquant seul (3) bloquent immédiatement. Pas de cookie + pas de referer (2+1=3) bloque aussi. Les nouveaux visiteurs avec un lien direct (seulement pas de cookie, score 2) passent.';

// configure.tpl — whitelist panel
$_MODULE['<{tec_facetlimit}prestashop>configure_0524317c1d882d879ecce066c0765424'] = 'Liste blanche IP';
$_MODULE['<{tec_facetlimit}prestashop>configure_3e5891188683540a724db87cf0b346bc'] = 'IPs en liste blanche';
$_MODULE['<{tec_facetlimit}prestashop>configure_14dc1e3d9f292c07e349da1a1d21da10'] = 'ex. 203.0.113.1 ou 2001:db8::1';
$_MODULE['<{tec_facetlimit}prestashop>configure_ec211f7c20af43e742bf2570c3cb84f9'] = 'Ajouter';
$_MODULE['<{tec_facetlimit}prestashop>configure_c770d8e0d1d1943ce239c64dbd6acc20'] = 'Ajouter mon IP';
$_MODULE['<{tec_facetlimit}prestashop>configure_73737140d636c9d43e20024dab8f2cc3'] = 'Adresses IPv4 et IPv6. Les IPs dans cette liste contournent tous les contrôles de rate-limit et de bot.';
$_MODULE['<{tec_facetlimit}prestashop>configure_d83651a81af0cbb569c38b819dea1b4c'] = 'Enregistrer la liste blanche';

// configure.tpl — log panel
$_MODULE['<{tec_facetlimit}prestashop>configure_8ed72d8592f2bb507e82b9c23632605f'] = 'Journal des requêtes bloquées';
$_MODULE['<{tec_facetlimit}prestashop>configure_2b6c2e165695b25f192dcd8b3c0ceada'] = 'Vider le fichier journal ?';
$_MODULE['<{tec_facetlimit}prestashop>configure_6bee5661e926f636f142c7f60a227116'] = 'Vider le journal';
$_MODULE['<{tec_facetlimit}prestashop>configure_88a9663240619395e55ab73a81ceaca0'] = 'Les 100 dernières entrées bloquées, les plus récentes en premier.';
$_MODULE['<{tec_facetlimit}prestashop>configure_a76d4ef5f3f6a672bbfab2865563e530'] = 'Heure';
$_MODULE['<{tec_facetlimit}prestashop>configure_a1fa27779242b4902f7ae3bdd5c6d508'] = 'Type';
$_MODULE['<{tec_facetlimit}prestashop>configure_cadd2b2ad06d8a0caee658e3c05e615a'] = 'Signaux';
$_MODULE['<{tec_facetlimit}prestashop>configure_00b8d7e7eb38979a4e1dd444bdc9571e'] = 'Aucune requête bloquée enregistrée pour le moment.';

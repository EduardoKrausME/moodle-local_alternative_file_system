<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang es file.
 *
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Sistema de Archivos Alternativo';

$string['settings_destino'] = 'Destino de almacenamiento';
$string['settings_destinodesc'] = 'Elige el destino de almacenamiento y guarda para cargar los datos relacionados con el almacenamiento.';

$string['settings_local'] = 'Archivos locales en Moodle';

$string['settings_s3_region'] = 'Región de {$a->local}';
$string['settings_s3_regiondesc'] = 'La región donde se encuentra el bucket de {$a->local}, por ejemplo, "{$a->ex_region}".';
$string['settings_s3_credentials_key'] = 'Clave de Acceso de {$a->local}';
$string['settings_s3_credentials_keydesc'] = 'La clave de acceso utilizada para autenticar con el servicio {$a->local}.';
$string['settings_s3_credentials_secret'] = 'Clave Secreta de {$a->local}';
$string['settings_s3_credentials_secretdesc'] = 'La clave secreta utilizada para autenticar con el servicio {$a->local}.';

$string['settings_gcs_keyfile'] = 'Contenido de google-storage.json';
$string['settings_gcs_keyfiledesc'] = 'Pega aquí el contenido del archivo "google-storage.json"';

$string['settings_bucketname'] = 'Nombre del Bucket en {$a->local}';
$string['settings_bucketnamedesc'] = 'El nombre único asignado al bucket en {$a->local}.';
$string['settings_path'] = 'Ruta del Objeto en {$a->local}';
$string['settings_pathdesc'] = 'La ruta dentro del bucket donde se almacenarán los objetos. Solo se aceptan letras y números';

$string['settings_success'] = '<h2>Los datos son correctos.</h2>Por favor, ten cuidado al modificar la configuración, ya que cualquier cambio incorrecto puede resultar en la inaccesibilidad de los archivos almacenados.';
$string['settings_migrate'] = 'Utiliza el servicio <a target="_blank" href="{$a->url}">move-to-external.php</a> para migrar los datos locales a {$a->local}.';
$string['migrate_title'] = 'Migrar local a Almacenamiento remoto';
$string['migrate_total'] = '<p>Tienes <strong>{$a->missing}</strong> archivos locales esperando migración, mientras que <strong>{$a->sending}</strong> archivos ya han sido migrados al entorno remoto.</p>';
$string['migrate_link'] = '<p><a class="btn btn-success" href="?execute=1">Ejecutar ahora (puede tomar mucho tiempo)</a></p>';

$string['instruction_title'] = 'Instrucciones de instalación';
$string['instruction_install'] = 'Necesitas agregar el siguiente código en config.php:<pre>$CFG->alternative_file_system_class = "\local_alternative_file_system\external_file_system";</pre>';

$string['privacy:no_data_reason'] = 'El complemento del Sistema de Archivos Alternativo no almacena ningún dato personal.';

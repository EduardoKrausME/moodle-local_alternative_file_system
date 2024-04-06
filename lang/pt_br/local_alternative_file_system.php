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
 * @package    local_alternative_file_system
 * @copyright  2024 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Alternative File System';

$string['settings_destino'] = 'Destino de armazenamento';
$string['settings_destinodesc'] = 'Escolha o destino de armazenamento e salve para carregar os dados relacionados ao armazenamento.';

$string['settings_s3_region'] = 'Região do {$a->local}';
$string['settings_s3_regiondesc'] = 'A região onde está localizado o bucket do {$a->local}, por exemplo, "{$a->ex_region}".';
$string['settings_s3_credentials_key'] = 'Chave de Acesso do {$a->local}';
$string['settings_s3_credentials_keydesc'] = 'A chave de acesso utilizada para autenticar com o serviço {$a->local}.';
$string['settings_s3_credentials_secret'] = 'Chave secreta do {$a->local}';
$string['settings_s3_credentials_secretdesc'] = 'A chave secreta utilizada para autenticar com o serviço {$a->local}.';

$string['settings_gcs_keyfile'] = 'Conteúdo do google-storage.json';
$string['settings_gcs_keyfiledesc'] = 'Cole aqui o conteúdo do arquivo "google-storage.json"';

$string['settings_bucketname'] = 'Nome do Bucket no {$a->local}';
$string['settings_bucketnamedesc'] = 'O nome único atribuído ao bucket no {$a->local}.';
$string['settings_path'] = 'Caminho do Objeto no {$a->local}';
$string['settings_pathdesc'] = 'O caminho dentro do bucket onde os objetos serão armazenados. Somente aceito letras e números';

$string['settings_success'] = '<h2>Os dados estão corretos.</h2>Por favor, tenha cautela ao modificar as configurações, pois qualquer alteração incorreta pode resultar na inacessibilidade dos arquivos armazenados.';
$string['settings_migrate'] = 'Utilize o serviço <a target="_blank" href="{$a->url}">move-to-external.php</a> para migrar os dados locais para o {$a->local}.';
$string['migrate_title'] = 'Migrar local para Storage remoto';
$string['migrate_total'] = '<p>Você possui <strong>{$a->missing}</strong> arquivos locais aguardando migração, enquanto <strong>{$a->sending}</strong> arquivos já foram migrados para o ambiente remoto.</p>';
$string['migrate_link'] = '<p><a class="btn btn-success" href="?execute=1">Executar agora (pode demorar bastante tempo)</a></p>';

$string['instruction_title'] = 'Instruções de instalação';
$string['instruction_install'] = 'Você precisa adicionar o código abaixo no config.php:<pre>$CFG->alternative_file_system_class = "\local_alternative_file_system\external_file_system";</pre>';

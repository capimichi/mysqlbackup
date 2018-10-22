<?php
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "readconfig.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "startlog.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "getclient.php";


$driveService = new Google_Service_Drive($client);

try {

    $folderName = date("YmdHi");
    $folderMetadata = new Google_Service_Drive_DriveFile(array(
        'name'     => $folderName,
        'mimeType' => 'application/vnd.google-apps.folder',
        'parents'  => [
            $config['gd_root_folder_id'],
        ],
    ));
    $folder = $driveService->files->create($folderMetadata, [
        'fields' => 'id',
    ]);
    $folderId = $folder->id;

    $systemLogger->info('Created directory ' . $folderName);

    foreach ($config['db_list'] as $dbName) {

        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $dbName . "_" . date("YmdHi") . ".sql";

        $port = !empty($config['db_port']) ? "@" . $config['db_port'] : "";

        if ($config['db_compress']) {
            $outputFile .= ".gz";
            $command = "mysqldump --opt -h {dbhost}{dbport} -u{dbuser} -p{dbpassword} {dbname} | gzip > {output}";
        } else {
            $command = "mysqldump --opt -h {dbhost}{dbport} -u{dbuser} -p{dbpassword} {dbname} > {output}";
        }

        $replaces = [
            'dbhost'     => $config['db_host'],
            'dbport'     => $config['db_port'],
            'dbuser'     => $config['db_user'],
            'dbpassword' => $config['db_password'],
            'dbname'     => $dbName,
            'output'     => $outputFile,
        ];

        foreach ($replaces as $replace => $substitution) {
            $command = str_replace("{" . $replace . "}", $substitution, $command);
        }

        exec($command);

        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name'    => basename($outputFile),
            'parents' => [
                $folderId,
            ],
        ]);

        $content = file_get_contents($outputFile);
        $file = $driveService->files->create($fileMetadata, [
            'data'       => $content,
            'mimeType'   => mime_content_type($outputFile),
            'uploadType' => 'multipart',
            'fields'     => 'id',
        ]);

        unlink($outputFile);

        $systemLogger->info('Uploaded ' . basename($outputFile));
    }


} catch (\Exception $exception) {
    $exceptionLogger->error($exception->getMessage());
    printf("%s\n", $exception->getMessage());
}



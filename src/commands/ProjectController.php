<?php

namespace beco\yii\commands;

use Yii;
use RuntimeException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\FileHelper;
use beco\yii\utils\StringUtils;

class ProjectController extends Controller {

  /**
   * Tablas a excluir del backup (se pasan como --exclude=table1,table2,...)
   * @var string
   */
  public $exclude = '';
  public $force = 0;
  public $templatesDir = '';

  public $commands = [
    'users' => [
      'command' => 'migrate',
      'question' => 'Shall I run Users and Telegram migration?',
      'params' => [
        'migrationNamespaces' => 'beco\yii\migrations',
        'interactive' => false,
      ],
    ],
    'rbac' => [
      'command' => 'migrate',
      'question' => 'Shall I run RBAC migrations?',
      'params' => [
        'migrationPath' => '@yii/rbac/migrations',
        'interactive'   => false, // importante para no pedir "Apply the above migrations? (yes|no)"
      ],
    ],
    'queue' => [
      'command' => 'migrate',
      'question' => 'Shall I run yii2-queue migrations?',
      'params' => [
        'migrationNamespaces' => 'yii\queue\db\migrations',
        'interactive' => false,
      ]
    ],
  ];

  /**
   * Opciones disponibles para cada acción.
   */
  public function options($actionID) {
    $options = parent::options($actionID);

    if ($actionID === 'backup') {
      $options[] = 'exclude';
    }

    if($actionID === 'setup') {
      $options[] = 'force';
    }

    return $options;
  }

  /**
   * Atajos para las opciones.
   */
  public function optionAliases() {
      return array_merge(parent::optionAliases(), [
          'e' => 'exclude',
          'f' => 'force',
      ]);
  }

  /**
   * Prints this system's to connect it's db.
   */
  public function actionDb() {
    printf("> mysql -u %s -p -h %s --database=%s\n\n%s\n",
      getenv('db_user'),
      getenv('db_host'),
      getenv('db_name'),
      getenv('db_pass')
    );
    return ExitCode::OK;
  }

  /**
   * Simple healthcheck.
   *
   * ./yii system/ping
   */
  public function actionPing(): int {
    $this->stdout("pongs\n");
    return ExitCode::OK;
  }

  /**
   * Muestra información básica del entorno de la app.
   *
   * ./yii system/info
   */
  public function actionInfo(): int {
    $this->stdout("App ID: " . Yii::$app->id . "\n");
    $this->stdout("Environment: " . (defined('YII_ENV') ? YII_ENV : 'unknown') . "\n");
    $this->stdout("Debug: " . (defined('YII_DEBUG') && YII_DEBUG ? 'true' : 'false') . "\n");
    $this->stdout("Base Path: " . Yii::getAlias('@app') . "\n");

    return ExitCode::OK;
  }

  /**
   * Hace backup de la BD actual (MySQL) usando mysqldump.
   *
   * Ejemplo:
   *   ./yii system/backup
   *   ./yii system/backup --exclude=migration,queue,log_table
   *   ./yii system/backup -e=migration,queue
   */
  public function actionBackup(): int {
    /** @var \yii\db\Connection $db */
    $db = Yii::$app->db;

    if (strpos($db->driverName, 'mysql') === false) {
        $this->stderr("This backup command currently only supports MySQL.\n");
        return ExitCode::UNSPECIFIED_ERROR;
    }

    $dsn = $db->dsn;
    $username = $db->username;
    $password = $db->password;

    // Parsear host, dbname y port del DSN
    $host = '127.0.0.1';
    $port = '3306';
    $dbName = null;

    if (preg_match('/host=([^;]+)/', $dsn, $m)) {
        $host = $m[1];
    }
    if (preg_match('/port=([^;]+)/', $dsn, $m)) {
        $port = $m[1];
    }
    if (preg_match('/dbname=([^;]+)/', $dsn, $m)) {
        $dbName = $m[1];
    }

    if ($dbName === null) {
        $this->stderr("Could not detect database name from DSN: {$dsn}\n");
        return ExitCode::UNSPECIFIED_ERROR;
    }

    // Directorio de backups
    $backupDir = Yii::getAlias('@app/backups');
    if (!is_dir($backupDir)) {
        FileHelper::createDirectory($backupDir);
    }

    // Nombre de archivo: yyyymmddhhmm.sql
    $timestamp = date('YmdHi');
    $sqlFile = $backupDir . DIRECTORY_SEPARATOR . $timestamp . '.sql';
    $zipFile = $backupDir . DIRECTORY_SEPARATOR . $timestamp . '.zip';

    // Tablas a excluir (--ignore-table=db.table)
    $excludeTables = [];
    if (!empty($this->exclude)) {
        $excludeTables = preg_split('/\s*,\s*/', $this->exclude, -1, PREG_SPLIT_NO_EMPTY);
    }

    $ignoreArgs = '';
    foreach ($excludeTables as $table) {
        $ignoreArgs .= ' --ignore-table=' . escapeshellarg($dbName . '.' . $table);
    }

    // Construir comando mysqldump
    $cmd =
        'mysqldump' .
        ' --host=' . escapeshellarg($host) .
        ' --port=' . escapeshellarg($port) .
        ' --user=' . escapeshellarg($username) .
        ' --password=' . escapeshellarg($password) .
        $ignoreArgs .
        ' ' . escapeshellarg($dbName) .
        ' > ' . escapeshellarg($sqlFile);

    $this->stdout("Running: {$cmd}\n");

    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);

    if ($exitCode !== 0) {
        $this->stderr("mysqldump failed with exit code {$exitCode}\n");
        if (file_exists($sqlFile)) {
            @unlink($sqlFile);
        }
        return ExitCode::UNSPECIFIED_ERROR;
    }

    $this->stdout("SQL backup created: {$sqlFile}\n");

    // Zippear el archivo .sql
    $zip = new \ZipArchive();
    if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        $this->stderr("Could not create zip file: {$zipFile}\n");
        return ExitCode::UNSPECIFIED_ERROR;
    }

    $zip->addFile($sqlFile, basename($sqlFile));
    $zip->close();

    $this->stdout("Zip created: {$zipFile}\n");

    // Si quieres borrar el .sql y quedarte solo con el zip, descomenta:
    // @unlink($sqlFile);

    return ExitCode::OK;
  }

  public function actionSetup(): int {
    if(!YII_ENV_DEV) {
      $this->stdout("This environment is not DEV, aborting.\n");
      return ExitCode::UNSPECIFIED_ERROR;
    }
    echo YII_ENV;
    return 1;
    $this->stdout("Running yii-common setup...\n");

    $firstRun = !file_exists(Yii::getAlias('@app/runtime/yii-common-setup.txt'));

    $this->stdout(sprintf("First run: %s\n", $firstRun?'TRUE':'FALSE'));

    // 1) Asegurar directorio de backups
    $backupDir = Yii::getAlias('@app/backups');
    if (!is_dir($backupDir)) {
      FileHelper::createDirectory($backupDir);
      $this->stdout("Created backups directory: {$backupDir}\n");
    } else {
      $this->stdout("Backups directory already exists: {$backupDir}\n");
    }

    // 2) Crear un .gitignore dentro de backups (opcional)
    $gitignore = $backupDir . DIRECTORY_SEPARATOR . '.gitignore';
    if (!file_exists($gitignore)) {
      $content = <<<TXT
*
!.gitignore
TXT;
      file_put_contents($gitignore, $content);
      $this->stdout("Created .gitignore in backups.\n");
    } else {
      $this->stdout(".gitignore already exists in backups.\n");
    }

    // 3) (Opcional) Escribir un archivo de prueba desde el paquete
    $testFile = Yii::getAlias('@app/runtime/yii-common-setup.txt');
    FileHelper::createDirectory(dirname($testFile));
    file_put_contents($testFile, "yii-common setup executed at " . date('c') . "\n");
    $this->stdout("Wrote test file: {$testFile}\n");

    // 4) Crear ActiveRecord en models
    $templatesDir = Yii::getAlias('@beco/yii/templates');

    $this->stdout("Templates dir: {$templatesDir}\n");

    $file_templates = [
      'active_record_facade' => [
        'origin' => $templatesDir . DIRECTORY_SEPARATOR . 'ActiveRecord.template',
        'destination' => Yii::getAlias('@app/models/ActiveRecord.php'),
        'overwrite' => false,
      ],
      'user_basic_class' => [
        'origin' => $templatesDir . DIRECTORY_SEPARATOR . 'User.template',
        'destination' => Yii::getAlias('@app/models/User.php'),
        'overwrite' => false,
      ],
      'db_basic_config' => [
        'origin' => $templatesDir . DIRECTORY_SEPARATOR . 'db.template',
        'destination' => Yii::getAlias('@app/config/db.php'),
        'overwrite' => true,
      ],
      'local_vars' => [
        'origin' => $templatesDir . DIRECTORY_SEPARATOR . 'variables_local.template',
        'destination' => Yii::getAlias('@app/local/variables_local.sh'),
        'overwrite' => false,
        'type' => 'render',
        'vars' => [
          'salt' => StringUtils::generate(60),
        ],
        'onlyFirstRun' => true,
      ],
      'rbac_controller' => [
        'origin' => $templatesDir . DIRECTORY_SEPARATOR . 'RbacController.template',
        'destination' => Yii::getAlias('@app/commands/RbacController.php'),
        'overwrite' => false,
      ],
      'web_index' => [
        'origin' => $templatesDir . DIRECTORY_SEPARATOR . 'index.template',
        'destination' => Yii::getAlias('@app/web/index.php'),
        'overwrite' => true,
      ],
      'web_config' => [
        'origin' => $templatesDir . DIRECTORY_SEPARATOR . 'web_config.template',
        'destination' => Yii::getAlias('@app/config/web.php'),
        'overwrite' => true,
        'type' => 'render',
        'vars' => [
          'cookie_validation' => StringUtils::generate(40),
        ],
        'onlyFirstRun' => true,
      ],
      'console_config' => [
        'origin' => $templatesDir . DIRECTORY_SEPARATOR . 'console_config.template',
        'destination' => Yii::getAlias('@app/config/console.php'),
        'overwrite' => false,
      ],
    ];

    $variables = [
      'cookie_validation' => StringUtils::generate(30),
      'salt' => StringUtils::generate(60),
    ];

    foreach ($file_templates as $key => $file) {
      $origin      = $file['origin'];
      $destination = $file['destination'];

      // 1) Validar que el template exista
      if (!file_exists($origin)) {
        $this->stderr("Template not found: {$origin}\n");
        continue;
      }

      // 2) Si el archivo destino ya existe, lo saltamos
      $force = $this->force == 1;
      $overwrite = $file['overwrite'] ?? false;
      if (file_exists($destination)) {
        if(!($overwrite == true && $force == true) && !$firstRun) {
          $this->stdout("File {$destination} already exists, skipping.\n");
          $this->stdout("You can override (if file is overwritable) this with --force=1 or -f 1\n");
          continue;
        }
      }

      // 3) Crear carpeta destino si no existe
      $dir = dirname($destination);
      if (!is_dir($dir)) {
        FileHelper::createDirectory($dir);
      }

      // 4) Copiar el archivo
      if(empty($file['type']) || $file['type'] === 'copy') {
        if (!copy($origin, $destination)) {
          $this->stderr("Failed to copy {$origin} to {$destination}\n");
          continue;
        }
      } elseif($file['type'] === 'render') {
        $content = $this->renderTemplate($origin, $file['vars']);
        $this->writeFile($destination, $content);
      }


      $this->stdout("Created {$destination} from template {$origin}\n");
    }

    $this->stdout("Setup finished.\n");
    return ExitCode::OK;
  }

  public function actionMigrate() {

    foreach($this->commands as $key => $command) {

      if(!empty($command['question'])) {
        $ans = readline($command['question'] . '[yes/N]: ');
        $ans = trim(strtolower($ans));
        if($ans != 'yes') {
          continue;
        }
      }
      $this->stdout($key . ': ');
      $exitCode = Yii::$app->runAction($command['command'], $command['params']);
      $result = $exitCode !== ExitCode::OK?"fail":"ok";
      $this->stdout($result. "\n");

    }

    return ExitCode::OK;
  }

  private function renderTemplate(string $origin, array $vars = []): string {
    if (!file_exists($origin)) {
      throw new RuntimeException("Template not found: {$origin}");
    }

    $content = file_get_contents($origin);

    // reemplazar {{key}} → value
    foreach ($vars as $key => $value) {
      $content = str_replace('{{' . $key . '}}', $value, $content);
    }

    return $content;
  }

  private function writeFile(string $destination, string $content): void {
    $dir = dirname($destination);

    if (!is_dir($dir)) {
      FileHelper::createDirectory($dir);
    }

    file_put_contents($destination, $content);
  }


}

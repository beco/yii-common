Common Stuff for Yii2 Projects
==============================

# Instalation
`composer require beco/yii-common:dev-main`

## Provides
- new project creator (beco flavored)
- `beco\yii\db\ActiveRecord`:
  - Human and Relative Dates automatic functions
- `beco\yii\models\User`: a basic user model (and its migration)
- `beco\yii\commands\SystemCommand`: a command for
 - db backup
 - system check

### New proyect starter
If you

1. composer `composer create-project --prefer-dist yiisoft/yii2-app-basic [PROJECT_NAME]`
2. composer require beco\yii-commons

You have a new System command with some great commands:
1. `system/setup`
  1. creates and secures a backup directory
  2. marks its run (knowing if it's a first run by not finding such file)
  3. moves all files from `templates` into their specific location (if a file has a variable, injects it and then saves it in the right location, aka `$salt`)
    1. an ActiveRecord facade into the models directory
    2. a Users placeholder for the dev to extend User capabilities
    3. db, web and console config
    4. a beco flavored `web/index.php` which uses a new `beco\yii\web\Application` for enforcing https
2. `system/migrate` migrates (optionally) some other's libraries migrations into the project, such as
  1. queue
  2. rbac
  3. users (beco flavor)


### Human and Relative functions

If you have a field which is a date or a datetime column in mysql, aka
`yyyy-mm-dd` or `yyyy-mm-dd hh:ii:ss`, let's call it `starts_at`, by extending
`beco\yii\db\ActiveRecord` you automatically have three new functions:
1. `$model->starts_atHuman`returns a nice print of the date as "jueves 2 de mayo del 2015 a las 12:34pm"
2. `$model->starts_atRelative` returns how much time is left for such date in a human way
3. `$model->starts_atDateTime` a `\DateTime` representation of such date

So, if you create a `getStartsAt()` to translate snake_case into CamelCase as
Yii practices, you also have, directly, `getStartsAtHuman`, `getStartsAtRelative`
and `getStartsAtDateTime`.

# Info

Then, in order to get some local handling use intermediate classes:

```
<?php
namespace app\models;

use beco\yii\db\ActiveRecord as BaseActiveRecord;

abstract class ActiveRecord extends BaseActiveRecord {
    // Aquí puedes meter lógica específica de ESTE proyecto
    // por ejemplo:
    // - Comportamientos específicos
    // - Scope por tenant
    // - Nombre de conexión si difiere, etc.
}
```

# To do
- scheduler and beat
- `beco\yii\db\LoggableActiveRecord`

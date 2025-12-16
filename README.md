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

### Images

add the following line to `start.sh`
`./yii migrate --migrationPath=beco/yii/migrations/image --interactive=0`

### New project starter
Moved to beco\yii2-basic-template


### Human, DateTime and Relative functions

If you have a field which is a date or a datetime column in mysql, aka
`yyyy-mm-dd` or `yyyy-mm-dd hh:ii:ss`, let's call it `starts_at`, by extending
`beco\yii\db\ActiveRecord` you automatically have three new functions:
1. `$model->starts_atHuman`returns a nice print of the date as "jueves 2 de mayo del 2015 a las 12:34pm"
2. `$model->starts_atRelative` returns how much time is left for such date in a human way
3. `$model->starts_atDateTime` a `\DateTime` representation of such date

Automatically you also have `starts_atHuman` and `startsAtHuman`.

# Info

Then, in order to get some local handling use intermediate classes:

```
<?php
namespace app\models;

use beco\yii\db\ActiveRecord as BaseActiveRecord;

abstract class ActiveRecord extends BaseActiveRecord {

}
```

# Changelog

## v0.1.10
- images central repository
- images auto upload to s3

## v0.1.8
- spinoff; beco\yii-commons into beco\yii2-basic-template

## v0.1.6
- adding debug component (depends on RBAC)
- fixing Yii inclusion in `beco\yii\web\Application`

## v0.1.5
- beats more info [here](https://beco.notion.site/Beat-for-Yii2-Queue-2bdc428da03b8028b9c4f0eb2ceb0110)

## v0.1.2
- add `extra` (json) to users
- return null if date_time candidate's value is null
- minor fixes in relative time display
- add queue and mutex at composer level
- improved console and web config scritps
- includes Telegram basic client, command, migration and encapsulated response object
- all ActiveRecord are loggable

# To do
[] separate `system/new` from `system/setup` only for config files rather than code files
[x] add LoginForm to templates
[] scheduler and beat
[x] ~`beco\yii\db\LoggableActiveRecord`~ include log capabilites to all ActiveRecord
[] improve documentation
[] should all tables from this packages be `beco_table_name`?

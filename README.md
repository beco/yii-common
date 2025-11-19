Common Stuff for Yii2 Projects
==============================

# Instalation
`composer require beco/yii-common:dev-main`


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
- automatic start for new projects
- database backup
- queue
- beat

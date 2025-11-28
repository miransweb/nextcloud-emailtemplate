# Custom Email Template voor The Good Cloud

Deze Nextcloud app past de email templates aan voor de registratie mail en de welkomst mail.

## Installatie

### Stap 1: App installeren

1. Upload de `custom_email_template` folder naar `/apps/` op de Nextcloud server
2. Ga naar Apps in de Nextcloud admin interface
3. Activeer de app "Custom Email Template"

### Stap 2: Configuratie aanpassen

Bewerk `/var/www/nextcloud/config/config.php` en voeg toe:

```php
<?php
$CONFIG = array (
  // ... andere configuratie ...

  'mail_template_class' => 'OCA\\CustomEmailTemplate\\CustomEmailTemplate',

  // ... rest van configuratie ...
);
```

## Template aanpassen

Bewerk het bestand: `lib/EmailTemplate.php`

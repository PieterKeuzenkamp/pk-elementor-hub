# PK Elementor Extensions Licensing System

[English](#english) | [Nederlands](#nederlands)

## English

### Overview
The PK Elementor Extensions licensing system provides a secure and flexible way to manage PRO versions of extensions. This document explains how the licensing system works and how to implement it in your extensions.

### License Types
- **Free**: Basic version of extensions, available through WordPress.org
- **PRO**: Premium version with additional features and priority support

### License Features
- Secure license key validation
- Automatic updates for PRO versions
- Site activation/deactivation
- License expiration management
- Multiple site support (depending on license tier)

### Implementation Guide

#### 1. Registering Your Extension
```php
add_action('pk_elementor_hub_init', function() {
    do_action('pk_elementor_hub_register_extension', [
        'name' => 'Your Extension Name',
        'version' => '1.0.0',
        'description' => 'Extension description',
        'author' => 'Your Name',
        'author_uri' => 'https://yourwebsite.com',
        'requires' => '5.0',
        'tested' => '6.4',
        'pro_available' => true,
    ]);
});
```

#### 2. Checking License Status
```php
$license_status = PK_Elementor_Hub_License::instance()->check_license_status('your-extension-slug');
if ($license_status['status'] === 'valid') {
    // Enable PRO features
}
```

#### 3. Handling Updates
The Hub automatically handles updates for both free and PRO versions. No additional code is required in your extension.

### API Endpoints

#### License Activation
```
POST /wp-json/pk-elementor/v1/license/activate
{
    "extension": "extension-slug",
    "license_key": "your-license-key",
    "site_url": "https://yoursite.com"
}
```

#### License Deactivation
```
POST /wp-json/pk-elementor/v1/license/deactivate
{
    "extension": "extension-slug",
    "license_key": "your-license-key",
    "site_url": "https://yoursite.com"
}
```

#### License Check
```
POST /wp-json/pk-elementor/v1/license/check
{
    "extension": "extension-slug",
    "license_key": "your-license-key",
    "site_url": "https://yoursite.com"
}
```

### Security Considerations
- License keys are stored securely using WordPress options API
- All API requests are authenticated and validated
- Site URLs are verified to prevent unauthorized usage
- Sensitive data is encrypted in transit

## Nederlands

### Overzicht
Het PK Elementor Extensions licentiesysteem biedt een veilige en flexibele manier om PRO-versies van extensies te beheren. Dit document legt uit hoe het licentiesysteem werkt en hoe je het kunt implementeren in je extensies.

### Licentie Types
- **Gratis**: Basisversie van extensies, beschikbaar via WordPress.org
- **PRO**: Premium versie met extra functies en prioriteit support

### Licentie Functies
- Veilige licentiesleutel validatie
- Automatische updates voor PRO-versies
- Site activatie/deactivatie
- Licentie verloopdatum beheer
- Ondersteuning voor meerdere sites (afhankelijk van licentieniveau)

### Implementatie Handleiding

#### 1. Je Extensie Registreren
```php
add_action('pk_elementor_hub_init', function() {
    do_action('pk_elementor_hub_register_extension', [
        'name' => 'Jouw Extensie Naam',
        'version' => '1.0.0',
        'description' => 'Extensie beschrijving',
        'author' => 'Jouw Naam',
        'author_uri' => 'https://jouwwebsite.nl',
        'requires' => '5.0',
        'tested' => '6.4',
        'pro_available' => true,
    ]);
});
```

#### 2. Licentiestatus Controleren
```php
$license_status = PK_Elementor_Hub_License::instance()->check_license_status('jouw-extensie-slug');
if ($license_status['status'] === 'valid') {
    // PRO functies inschakelen
}
```

#### 3. Updates Afhandelen
De Hub handelt automatisch updates af voor zowel gratis als PRO versies. Geen extra code nodig in je extensie.

### API Endpoints

#### Licentie Activatie
```
POST /wp-json/pk-elementor/v1/license/activate
{
    "extension": "extensie-slug",
    "license_key": "jouw-licentiesleutel",
    "site_url": "https://jouwsite.nl"
}
```

#### Licentie Deactivatie
```
POST /wp-json/pk-elementor/v1/license/deactivate
{
    "extension": "extensie-slug",
    "license_key": "jouw-licentiesleutel",
    "site_url": "https://jouwsite.nl"
}
```

#### Licentie Controle
```
POST /wp-json/pk-elementor/v1/license/check
{
    "extension": "extensie-slug",
    "license_key": "jouw-licentiesleutel",
    "site_url": "https://jouwsite.nl"
}
```

### Veiligheidsoverwegingen
- Licentiesleutels worden veilig opgeslagen met de WordPress options API
- Alle API-verzoeken worden geauthenticeerd en gevalideerd
- Site URLs worden geverifieerd om ongeautoriseerd gebruik te voorkomen
- Gevoelige gegevens worden versleuteld tijdens verzending

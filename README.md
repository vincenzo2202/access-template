
# Proyecto Symfony con Autenticación JWT

Este proyecto está basado en Symfony y proporciona un ejemplo de autenticación utilizando JWT (JSON Web Tokens) junto con refresh tokens para mantener sesiones seguras.

## Requisitos

- PHP 8.2 o superior
- Composer
- MySQL o cualquier otra base de datos compatible con Doctrine
- OpenSSL

## Instalación

### 1. Crear el Proyecto Symfony

Para crear un nuevo proyecto Symfony, ejecuta:

```bash
symfony new my_project_name
``` 
###  Instalar Dependencias

```bash
composer require doctrine/orm
composer require symfony/maker-bundle --dev
composer require orm
composer require jms/serializer
```
## Autenticación JWT

## Instalar el Bundle JWT

```
composer require lexik/jwt-authentication-bundle
```
## Generar las Claves JWT

```
# Crear directorio de claves
mkdir -p config/jwt

# Generar clave privada
openssl genpkey -out config/jwt/private.pem -algorithm RSA -pkeyopt rsa_keygen_bits:4096

# Generar clave pública
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

#Asegúrate de que las claves tengan los permisos adecuados:
chmod 644 config/jwt/private.pem
chmod 644 config/jwt/public.pem
```

## Configurar JWT en Symfony
 ```
 lexik_jwt_authentication:
    secret_key: '%kernel.project_dir%/config/jwt/private.pem'
    public_key: '%kernel.project_dir%/config/jwt/public.pem'
    pass_phrase: ''  # Si generaste las claves con una passphrase, inclúyela aquí
    token_ttl: 3600  # Tiempo de vida del token en segundos

```

## Configurar el Sistema de Seguridad

```
security:
    password_hashers:
        Symfony\Component\Security\Core\User\UserInterface:
            algorithm: auto

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email  # o username, dependiendo de tu campo de identificación

    firewalls:
        login:
            pattern:  ^/api/login
            stateless: true
            json_login:
                check_path: /api/login
                username_path: email
                password_path: password
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

        api:
            pattern:   ^/api
            stateless: true
            provider:  app_user_provider
            jwt: ~     # Usa JWT para autenticar

    access_control:
        - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }
```

### Refresh Tokens

## 1.Instalar el Bundle de Refresh Tokens
```
composer require gesdinet/jwt-refresh-token-bundle
```

## 2. Registrar el Bundle
```
Gesdinet\JWTRefreshTokenBundle\GesdinetJWTRefreshTokenBundle::class => ['all' => true],
```

## 3. Configurar el Bundle de Refresh Tokens
```
gesdinet_jwt_refresh_token:
    ttl: 2592000          # Tiempo de vida del refresh token en segundos (30 días en este ejemplo)
    ttl_update: true      # Si el refresh token debería actualizarse al momento de su uso
    user_provider: app_user_provider  # El proveedor de usuarios que estás usando en tu configuración
    refresh_token_entity: App\Entity\RefreshToken # Entidad que manejará los tokens de refresco
```

## 4. Crear la Entidad RefreshToken

```
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
class RefreshToken extends BaseRefreshToken
{
}
```
 
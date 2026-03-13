# Guide de Déploiement sur o2switch

## Prérequis

- Accès FTP ou SSH à votre hébergement o2switch
- Base de données MySQL créée via cPanel
- PHP 8.2+ activé sur o2switch

## Étape 1 : Préparation des fichiers

### 1.1 Créer le fichier .env.local (sur o2switch)

Créez un fichier `.env.local` à la racine du projet avec :

```env
###> Configuration o2switch Production ###
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=VOTRE_CLE_SECRETE_32_CARACTERES_MINIMUM

# Base de données o2switch
# Format: mysql://utilisateur:motdepasse@localhost:3306/nom_database
DATABASE_URL="mysql://maal2992:VOTRE_PASS@localhost:3306/maal2992_propositiondouverture?serverVersion=mariadb-10.6.18&charset=utf8mb4"

# URL de votre site
DEFAULT_URI=https://propositiondouverture.developpement-comapik.fr
###< Configuration o2switch Production ###
```

**IMPORTANT** : Remplacez `VOTRE_CLE_SECRETE` et les identifiants de base de données !

### 1.2 Fichiers à uploader

Uploadez **tous** les fichiers SAUF :
- `.env` (utilisez `.env.local` à la place)
- `var/cache/*`
- `var/log/*`
- `.git/`
- `node_modules/` (si présent)

## Étape 2 : Configuration sur o2switch

### 2.1 Via SSH (recommandé)

Connectez-vous en SSH :

```bash
ssh votre_user@votre_domaine.com
cd ~/www/
```

### 2.2 Installation des dépendances

```bash
# Installer les dépendances (sans dev)
composer install --no-dev --optimize-autoloader --no-interaction

# Si composer n'est pas disponible globalement
php composer.phar install --no-dev --optimize-autoloader --no-interaction
```

### 2.3 Permissions des dossiers

```bash
# Créer les dossiers var si nécessaire
mkdir -p var/cache var/log

# Définir les bonnes permissions
chmod -R 775 var/
chmod -R 775 public/uploads/
```

### 2.4 Base de données

Créez la base de données via **cPanel > MySQL Databases**, puis :

```bash
# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Vérifier que les tables sont créées
php bin/console doctrine:schema:validate --env=prod
```

### 2.5 Cache

```bash
# Vider et préchauffer le cache
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod
```

## Étape 3 : Vérification

### 3.1 Diagnostic automatique

Accédez à : `https://votre-domaine.com/check.php`

Ce script vérifiera :
- Version PHP et extensions
- Fichiers et permissions
- Configuration .env
- Connexion base de données
- Tables

**SUPPRIMEZ `check.php` après diagnostic !**

### 3.2 Test manuel

Accédez à votre site : `https://propositiondouverture.developpement-comapik.fr/`

## Problèmes Courants

### Erreur 500

**Cause 1 : Fichier .htaccess avec php_value**
- ✅ Déjà corrigé : Les directives `php_value` sont dans `.user.ini`

**Cause 2 : DATABASE_URL non configuré**
```bash
# Vérifier que .env.local existe et contient DATABASE_URL
cat .env.local | grep DATABASE_URL
```

**Cause 3 : Permissions incorrectes**
```bash
chmod -R 775 var/
chown -R votre_user:votre_user var/
```

**Cause 4 : Cache corrompu**
```bash
# Supprimer tout le cache
rm -rf var/cache/*
php bin/console cache:clear --env=prod
```

### Erreur "Class not found"

```bash
# Régénérer l'autoloader
composer dump-autoload --optimize --no-dev
```

### Base de données vide

```bash
# Vérifier les migrations
php bin/console doctrine:migrations:status

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction
```

### Cannot write to var/cache

```bash
# Vérifier le propriétaire
ls -la var/

# Corriger les permissions
chmod -R 775 var/
```

## Étape 4 : Déploiement des mises à jour

Pour les mises à jour futures :

```bash
# 1. Uploader les nouveaux fichiers via FTP
# 2. Via SSH :

# Installer les nouvelles dépendances
composer install --no-dev --optimize-autoloader

# Exécuter les nouvelles migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Vider le cache
rm -rf var/cache/prod
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Vérifier
php bin/console about --env=prod
```

## Checklist de déploiement

- [ ] `.env.local` créé avec APP_ENV=prod
- [ ] DATABASE_URL configuré avec les vrais identifiants
- [ ] Tous les fichiers uploadés (sauf var/cache, .env, .git)
- [ ] `composer install --no-dev` exécuté
- [ ] Permissions `var/` à 775
- [ ] Migrations exécutées
- [ ] Cache vidé en production
- [ ] Site accessible sans erreur 500
- [ ] `check.php` supprimé

## Support

En cas de problème persistant :

1. Activez temporairement le mode debug pour voir l'erreur exacte :
   ```env
   APP_ENV=prod
   APP_DEBUG=1
   ```

2. Consultez les logs :
   ```bash
   tail -f var/log/prod.log
   ```

3. Vérifiez les logs Apache/PHP de cPanel

4. Désactivez le debug après diagnostic :
   ```env
   APP_DEBUG=0
   ```

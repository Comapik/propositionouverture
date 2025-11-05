# Proposition Ouverture - Symfony Application

Une application Symfony moderne construite en suivant les principes **SOLID**, **DRY** et **KISS**, ainsi que les meilleures pratiques de Symfony.

## 🚀 Principes Appliqués

### SOLID
- **S**ingle Responsibility Principle: Chaque classe a une seule responsabilité
- **O**pen/Closed Principle: Ouvert à l'extension, fermé à la modification
- **L**iskov Substitution Principle: Les sous-classes peuvent remplacer leurs classes de base
- **I**nterface Segregation Principle: Interfaces spécialisées plutôt que générales
- **D**ependency Inversion Principle: Dépendre d'abstractions, pas de concrétions

### DRY (Don't Repeat Yourself)
- Code réutilisable et modulaire
- Services centralisés
- Templates et composants partagés

### KISS (Keep It Simple, Stupid)
- Solutions simples et élégantes
- Code lisible et maintenable
- Architecture claire

## 📁 Structure du Projet

```
src/
├── Command/         # Commandes console
├── Controller/      # Contrôleurs web
├── Entity/          # Entités Doctrine
├── Exception/       # Exceptions personnalisées
├── Form/           # Types de formulaires
├── Repository/     # Repositories Doctrine
├── Security/       # Classes de sécurité
└── Service/        # Services métier
```

## 🛠 Installation et Configuration

### Prérequis
- PHP 8.2 ou supérieur
- Composer
- Serveur web (Apache/Nginx ou serveur de développement Symfony)

### Installation
```bash
# Déjà installé dans votre workspace MAMP
cd /Applications/MAMP/htdocs/propositionouverture

# Installer les dépendances (déjà fait)
composer install

# Configuration de la base de données
# Modifiez DATABASE_URL dans .env selon vos besoins
# Par défaut: SQLite (aucune configuration requise)
# Pour MySQL/MAMP: décommentez la ligne MySQL dans .env

# Créer la base de données et exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction
```

## 🚀 Lancement de l'Application

### Avec le serveur de développement Symfony
```bash
php bin/console server:start
# ou
symfony serve
```

### Avec MAMP
- Placez le projet dans `/Applications/MAMP/htdocs/propositionouverture`
- Accédez à `http://localhost:8888/propositionouverture/public`

## 📋 Commandes Disponibles

```bash
# Afficher le statut de l'application
php bin/console app:status

# Créer un nouveau contrôleur
php bin/console make:controller

# Créer une nouvelle entité
php bin/console make:entity

# Créer un formulaire
php bin/console make:form

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear
```

## 🏗 Composants Installés

- **Symfony Framework Bundle**: Base du framework
- **Doctrine ORM**: Gestionnaire de base de données
- **Twig**: Moteur de templates
- **Symfony Form**: Gestion des formulaires
- **Symfony Validator**: Validation des données
- **Symfony Security**: Authentification et autorisation
- **Maker Bundle**: Génération de code (dev)
- **Web Profiler**: Outils de débogage (dev)
- **Monolog**: Gestion des logs

## 🎯 Exemples d'Implémentation

### Service avec Injection de Dépendance
```php
// Interface (Dependency Inversion Principle)
interface ApplicationServiceInterface
{
    public function getApplicationName(): string;
}

// Implémentation (Single Responsibility Principle)
final readonly class ApplicationService implements ApplicationServiceInterface
{
    public function __construct(
        #[Autowire('%kernel.environment%')] 
        private string $environment
    ) {}
    
    public function getApplicationName(): string
    {
        return 'Proposition Ouverture';
    }
}
```

### Contrôleur Simple (KISS Principle)
```php
#[Route('/', name: 'app_home', methods: ['GET'])]
public function index(): Response
{
    return $this->render('home/index.html.twig', [
        'title' => 'Bienvenue sur notre application Symfony',
    ]);
}
```

### Repository avec Méthodes Spécialisées (DRY Principle)
```php
public function findRecentUsers(int $days = 30): array
{
    $date = new \DateTime();
    $date->modify("-{$days} days");

    return $this->createQueryBuilder('u')
        ->andWhere('u.createdAt >= :date')
        ->setParameter('date', $date)
        ->orderBy('u.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}
```

## 🔒 Sécurité

- Validation des données avec Symfony Validator
- Protection CSRF intégrée
- Hashage sécurisé des mots de passe
- Structure d'entité User prête pour l'authentification

## 📚 Documentation

- [Documentation Symfony](https://symfony.com/doc/current/)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Twig Templates](https://twig.symfony.com/doc/)

## 🤝 Contribution

Ce projet suit les standards PSR-12 pour le style de code et les meilleures pratiques Symfony.

---

**Développé avec ❤️ en suivant les principes SOLID, DRY et KISS**
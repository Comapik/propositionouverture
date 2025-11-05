# Copilot Instructions - Proposition d'Ouverture (Window/Door Configuration System)

## Project Architecture

This is a **Symfony 7.3 business application** for window and door configuration and quoting ("Proposition d'Ouverture"). The project uses MicroKernelTrait with Doctrine ORM and a complex relational database schema for managing construction projects, client quotes, and product configurations.

### Key Structural Patterns

- **MicroKernel Architecture**: `src/Kernel.php` extends `BaseKernel` with `MicroKernelTrait` for simplified configuration
- **Attribute-Based Routing**: Controllers use PHP attributes instead of YAML/XML routing (see `config/routes.yaml`)
- **Autowired Services**: All services in `src/` are auto-registered with autowiring enabled (`config/services.yaml`)
- **Doctrine ORM**: Full entity mapping with attribute-based annotations in `src/Entity/`
- **Complex Business Domain**: Multi-table schema for clients, projects, products, materials, suppliers, and configuration options

## Critical Development Workflows

### Database & Migrations
```bash
# Database operations
php bin/console doctrine:database:create    # Create database
php bin/console doctrine:migrations:migrate # Run pending migrations
php bin/console doctrine:migrations:status  # Check migration status
php bin/console doctrine:schema:validate    # Validate schema against entities
```

### Console Commands
```bash
# Essential development commands
php bin/console cache:clear                 # Clear cache (auto-runs after composer install/update)
php bin/console debug:router                # View all routes
php bin/console debug:container             # View all services
php bin/console debug:config framework      # View framework configuration
php bin/console doctrine:mapping:info       # Show entity mappings
```

### Environment Management
- **Environment loading order**: `.env` → `.env.local` → `.env.$APP_ENV` → `.env.$APP_ENV.local`
- **Current environment**: Controlled by `APP_ENV` in `.env` (currently `dev`)
- **Database**: MySQL configured in `.env` (note: Docker compose has PostgreSQL but project uses MySQL)
- **Secrets handling**: Use `php bin/console secrets:set` for sensitive data, not `.env` files

## Project-Specific Conventions

### Entity Creation Pattern
- **Location**: Place entities in `src/Entity/` with attribute-based Doctrine mappings
- **Repository Pattern**: Custom repositories in `src/Repository/` extend `ServiceEntityRepository`
- **Naming**: PascalCase class names, snake_case table names (Doctrine default)
- **Relationships**: Use Doctrine attributes for OneToMany, ManyToOne, etc.

### Service Configuration
- **Auto-registration**: All classes in `src/` become services automatically
- **No manual service definitions needed** for most use cases
- **Override pattern**: Place explicit service definitions at bottom of `config/services.yaml`

### Controller Pattern
- **Location**: Place controllers in `src/Controller/` (currently empty but configured)
- **Routing**: Use PHP Route attributes on controller methods
- **Namespace**: `App\Controller` is pre-configured for attribute routing
- **Base Class**: Extend `AbstractController` for entity manager access

### Configuration Structure
- **Bundle configs**: Place in `config/packages/` (e.g., `framework.yaml`, `doctrine.yaml`)
- **Route configs**: Additional routes can go in `config/routes/`
- **Environment overrides**: Use `when@prod:`, `when@dev:`, `when@test:` sections

## Design Principles & Best Practices

### SOLID Principles
- **Single Responsibility**: Each class should have one reason to change - controllers handle HTTP, services handle business logic, repositories handle data access
- **Open/Closed**: Classes should be open for extension but closed for modification - use interfaces and dependency injection
- **Liskov Substitution**: Subtypes must be substitutable for their base types - ensure proper inheritance hierarchies
- **Interface Segregation**: Clients should not be forced to depend on interfaces they don't use - create specific interfaces for different clients
- **Dependency Inversion**: Depend on abstractions, not concretions - use interfaces in constructor parameters

### DRY (Don't Repeat Yourself)
- **Extract common logic** into shared services or base classes
- **Use traits** for reusable functionality across entities
- **Create reusable form types** and validation constraints
- **Centralize configuration** in services rather than duplicating setup code

### KISS (Keep It Simple, Stupid)
- **Simple controllers**: Keep controller actions focused on HTTP concerns, delegate business logic to services
- **Clear naming**: Use descriptive names that explain purpose without needing comments
- **Avoid over-engineering**: Start simple, add complexity only when needed
- **Single-level abstraction**: Each method/function should operate at one level of abstraction

### Symfony Best Practices
- **Use dependency injection**: Inject services via constructor, avoid static calls to service container
- **Leverage Doctrine relationships**: Use proper entity associations instead of manual ID management
- **Validate early**: Use Symfony's validation component in forms and API endpoints
- **Handle exceptions properly**: Use custom exception classes and proper HTTP status codes
- **Cache effectively**: Use Symfony's cache component for expensive operations
- **Security first**: Always validate user input, use CSRF protection, escape output
- **Performance considerations**: Use lazy loading, pagination, and efficient queries
- **Logging**: Use Monolog for proper error tracking and debugging

## Key Dependencies & Integration Points

### Core Symfony Components (7.3.*)
- **Doctrine ORM**: Full database abstraction with entity manager (`doctrine/orm`)
- **Doctrine Migrations**: Database schema versioning (`doctrine/doctrine-migrations-bundle`)
- **Runtime Component**: Handles application bootstrap (`vendor/autoload_runtime.php`)
- **Flex**: Recipe system for automatic configuration (`symfony/flex`)
- **Console Component**: CLI commands via `bin/console`

### Business Domain Entities
The application manages a complex window/door configuration system with these core entities:
- **Clients**: Customer information and contact details
- **Projets**: Construction projects linked to clients
- **Produits/Catégories/Sous-catégories**: Product hierarchy (windows, doors, shutters)
- **Ouverture**: Opening types (French windows, doors, etc.)
- **Matériaux**: Construction materials
- **Couleurs**: Color options with plating/finishing si plaxage_laquage_id = 1 alors code hex couleur, sinon lien url vers image
- **Fournisseurs**: Supplier/manufacturer information
- **Configuration Tables**: `conf_pf` (Porte Fenêtre) and `conf_volet` (shutter) configurations

### MAMP Integration
- **Default URI**: `http://localhost` (configured in `.env`)
- **Database**: MySQL via MAMP (not Docker PostgreSQL)

## Development Environment Setup

### PHP Requirements
- **PHP 8.2+** required
- **Extensions**: `ext-ctype`, `ext-iconv` are mandatory
- **Database**: MySQL 8.0+ (configured for MAMP)

### Local Development
```bash
# Install dependencies
composer install

# Database setup
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Start development server (alternative to MAMP)
symfony serve  # or php -S localhost:8000 -t public/

# Check Symfony requirements
symfony check:requirements
```

## File Creation Patterns

### Adding Entities
```php
// src/Entity/Example.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExampleRepository::class)]
class Example
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // Getters and setters...
}
```

### Adding Controllers
```php
// src/Controller/ExampleController.php
namespace App\Controller;

use App\Entity\Example;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExampleController extends AbstractController
{
    #[Route('/example', name: 'app_example')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $examples = $entityManager->getRepository(Example::class)->findAll();
        return $this->render('example/index.html.twig', [
            'examples' => $examples,
        ]);
    }
}
```

### Adding Repositories
```php
// src/Repository/ExampleRepository.php
namespace App\Repository;

use App\Entity\Example;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExampleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Example::class);
    }

    // Custom query methods...
}
```
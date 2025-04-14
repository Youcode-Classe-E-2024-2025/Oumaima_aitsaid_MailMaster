# MailMaster - API de gestion de newsletters

MailMaster est une API robuste permettant de gérer efficacement les campagnes de newsletters d'une entreprise. Cette API permet la création de listes de diffusion, l'ajout d'abonnés, la rédaction et l'envoi de newsletters, ainsi que la consultation des statistiques de lecture.

## Technologies utilisées

- Laravel 12
- postgreSQL
- Laravel Sanctum pour l'authentification API

## Fonctionnalités

- Authentification sécurisée avec Sanctum
- Gestion des listes de diffusion (newsletters)
- Gestion des abonnés
- Création et envoi de campagnes d'emails
- Suivi des statistiques d'ouverture et de clic

## Installation

1. Cloner le dépôt : `git clone https://github.com/Youcode-Classe-E-2024-2025/Oumaima_aitsaid_MailMaster.git`
2. Accéder au répertoire du projet : `cd mail-master`
3. Installer les dépendances : `composer install`
4. Configurer la base de données dans le fichier `.env`
5. Générer une clé d'application : `php artisan key:generate`
6. Migrer la base de données : `php artisan migrate`
7. Lancer le serveur de développement : `php artisan serve`
## Documentation API

La documentation de l'API est disponible à l'URL `/api/documentation` après le démarrage du serveur.

## Tests

Exécuter les tests avec PHPUnit : `php artisan test`
## Contribution
Les contributions sont les bienvenues ! Veuillez ouvrir une issue ou un pull request pour toute contribution.
## Licence
Ce projet est sous licence MIT.

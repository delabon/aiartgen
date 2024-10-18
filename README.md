## AIArtGen

A platform for AI artists where they can leverage Opanai's DALL-E to generate modern art.

[Limited Demo](https://aiartgen.delabon.com/)

### Key Features

- **APIs**: A set of APIs that allow for creating, reading, updating, and deleting art pieces.
- **API Documentation**: Detailed documentation for the APIs, including request/response examples ([Url](https://documenter.getpostman.com/view/24405131/2sAXjJ5YT7)).
- **User Authentication**: Secure user registration and login functionality.
- **Email Verification**: Users must verify their email addresses before accessing certain features.
- **Password Reset**: Users can reset their passwords via email.
- **Art Management**: Authenticated users can create, edit, and delete their art pieces.
- **User Profiles**: View art collections by specific users.
- **Responsive Design**: Optimized for both desktop and mobile devices.
- **Notifications**: Email notifications for account verification and password reset.
- **Role-Based Access Control**: Permissions to ensure only authorized users can perform certain actions.

### Tech Stack

- **Backend**: PHP 8.2, Laravel 11
- **Database**: SQLite
- **Testing**: PHPUnit for unit, integration and feature testing (Developed completely using TDD)
- **Static Analysis**: PHPStan for analyzing code quality
- **Environment Management**: Docker for containerization and consistent development environments
- **CI**: GitHub actions for continuous Integration
- **Openai**: DALL-E API for art generation

## How to test it on your local machine

### Up containers

```bash
docker compose up --build -d
```

### Run the following

```bash
docker compose exec php-service composer install
cp app/.env.example app/.env
docker compose exec php-service php artisan key:generate
docker compose exec php-service php artisan migrate
```

### Build assets

```bash
docker compose run --rm node-service npm install
docker compose run --rm node-service npm run build
```

### Run PHPUnit tests

```bash
docker compose exec php-service vendor/bin/phpunit --testsuite=Unit
docker compose exec php-service vendor/bin/phpunit --testsuite=Integration
docker compose exec php-service vendor/bin/phpunit --testsuite=Feature
```

Open http://localhost:8022/ in your browser.

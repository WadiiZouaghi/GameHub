# GameHub

A Symfony-based gaming platform for managing and reserving games, events, and user interactions. This project is built for educational purposes and self-training.

## Features

- **User Management**: Registration, authentication, and user profiles
- **Game Catalog**: Browse and manage games with cover images and details
- **Game Reservations**: Book games for specific dates
- **Event Management**: Create and manage gaming events
- **Admin Dashboard**: Comprehensive admin panel for managing games, events, reservations, and users
- **Purchase System**: Track game purchases
- **Review System**: Users can leave reviews on games
- **User Library**: Personal library of purchased/reserved games

## Tech Stack

- **Framework**: Symfony
- **Database**: Doctrine ORM
- **Templating**: Twig
- **Frontend**: HTML/CSS with JavaScript (Stimulus.js)
- **Authentication**: Symfony Security

## Installation

1. Clone the repository:
```bash
git clone https://github.com/WadiiZouaghi/GameHub.git
cd GameHub
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment variables:
```bash
cp .env .env.local
```

4. Create the database and run migrations:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Load sample data (optional):
```bash
php bin/console doctrine:fixtures:load
```

6. Start the development server:
```bash
symfony server:start
```

Visit `http://localhost:8000` to access the application.

## Project Structure

- `src/Controller/` - Application controllers
- `src/Entity/` - Doctrine entities
- `src/Form/` - Symfony form types
- `src/Repository/` - Database repositories
- `templates/` - Twig templates
- `migrations/` - Database migrations
- `config/` - Configuration files
- `public/uploads/` - User-uploaded files (avatars, game covers)

## License

This project is for educational purposes.

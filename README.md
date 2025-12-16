# 🎮 GameHub

> A modern Symfony-based gaming platform for managing, discovering games, and participating in community events.

![Symfony](https://img.shields.io/badge/Symfony-7.3-000000?style=flat-square&logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)
![Doctrine](https://img.shields.io/badge/Doctrine-ORM-FC6D26?style=flat-square)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat-square&logo=docker)
![License](https://img.shields.io/badge/License-Educational-green?style=flat-square)

> **Educational Project** | Self-training initiative to master Symfony and modern web development practices

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| 👤 **User Management** | Registration, authentication, and personalized user profiles |
| 🎮 **Game Catalog** | Browse, filter, and manage games with cover images and detailed metadata |
| 🎭 **Event Management** | Create, manage, and participate in gaming events |
| 📰 **News System** | Integrated gaming news with external API integration |
| 🏆 **Admin Dashboard** | Comprehensive control panel for managing games, users, events, and content |
| 🛒 **Purchase System** | Track and manage game purchases and ownership |
| ⭐ **Review System** | Community reviews and ratings for games |
| 📚 **User Dashboard** | Personal collection with purchase history and event participation |

---

## 🛠️ Tech Stack

<table>
<tr>
<td width="50%">

**Backend**
- 🟢 **Symfony 7.3** - Web framework
- 🗄️ **Doctrine ORM** - Database mapping
- 🔐 **Symfony Security** - Authentication & Authorization
- 📧 **Symfony Mailer** - Email functionality

</td>
<td width="50%">

**Frontend**
- 🎨 **HTML/CSS** - Markup & styling
- ⚡ **Stimulus.js** - Interactive components
- 🚀 **Hotwire Turbo** - Fast page navigation
- 🎯 **Responsive Design** - Mobile-friendly UI

</td>
</tr>
</table>

**Database**: PostgreSQL with Doctrine ORM  
**Templating**: Twig  
**Containerization**: Docker Compose  
**Version Control**: Git

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Docker & Docker Compose (recommended)
- PostgreSQL (or use Docker)

### Installation Steps

**1️⃣ Clone the Repository**
```bash
git clone https://github.com/WadiiZouaghi/GameHub.git
cd GameHub
```

**2️⃣ Install Dependencies**
```bash
composer install
```

**3️⃣ Configure Environment**
```bash
cp .env .env.local
# Update database credentials in .env.local if needed
```

**4️⃣ Start Docker (Optional but Recommended)**
```bash
docker-compose up -d
```

**5️⃣ Setup Database**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

**6️⃣ Load Sample Data** (Optional)
```bash
php bin/console doctrine:fixtures:load
```

**7️⃣ Start Development Server**
```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

🌐 Open your browser and navigate to `http://localhost:8000`

---

## 📁 Project Structure

```
GameHub/
├── src/
│   ├── Controller/       # 🎛️  Application controllers
│   ├── Entity/           # 📊 Doctrine entities
│   ├── Form/             # 📝 Symfony form types
│   ├── Repository/       # 🗂️  Database queries
│   ├── Service/          # 🔧 Business logic services
│   ├── Command/          # ⚙️  Console commands
│   ├── Security/         # 🔒 Security handlers
│   └── DataFixtures/     # 🌱 Sample data
├── templates/            # 🎨 Twig templates
├── migrations/           # 📚 Database migrations
├── config/               # ⚙️  Configuration files
├── public/
│   ├── index.php         # 📍 Application entry point
│   └── uploads/          # 📦 User uploads (avatars, covers, gallery)
├── assets/               # 🎭 CSS & JavaScript
├── tests/                # ✅ Test files
├── compose.yaml          # 🐳 Docker Compose configuration
└── phpunit.dist.xml      # 🧪 PHPUnit configuration
```

---

## 📖 Key Entities

- **User** - Player accounts with profiles and roles
- **Game** - Game catalog entries with metadata and cover images
- **Event** - Community gaming events
- **Purchase** - Game ownership and purchase history
- **Review** - Game ratings and user feedback
- **News** - Gaming news articles integrated with external APIs

---

## 🤝 Contributing

This is an educational project. Feel free to fork, explore, and learn!

---

## 📝 License

Educational project for learning purposes.

---

<div align="center">

**Made with ❤️ for learning**

[⭐ Star this repository](https://github.com/WadiiZouaghi/GameHub) if you find it helpful!

</div>

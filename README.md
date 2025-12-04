# 🎮 GameHub

> A modern Symfony-based gaming platform for managing, reserving, and discovering games with community events and social features.

![Symfony](https://img.shields.io/badge/Symfony-6.x-000000?style=flat-square&logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php)
![Doctrine](https://img.shields.io/badge/Doctrine-ORM-FC6D26?style=flat-square)
![Twig](https://img.shields.io/badge/Twig-Template-90C53F?style=flat-square&logo=twig)
![License](https://img.shields.io/badge/License-Educational-green?style=flat-square)

> **Educational Project** | Self-training initiative to master Symfony and modern web development practices

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| 👤 **User Management** | Registration, authentication, and personalized user profiles |
| 🎮 **Game Catalog** | Browse, filter, and manage games with cover images and metadata |
| 📅 **Game Reservations** | Book games for specific dates with availability tracking |
| 🎭 **Event Management** | Create, manage, and participate in gaming events |
| 🏆 **Admin Dashboard** | Comprehensive control panel for managing all platform resources |
| 🛒 **Purchase System** | Track and manage game purchases and transactions |
| ⭐ **Review System** | Community reviews and ratings for games |
| 📚 **User Library** | Personal collection of owned and reserved games |

---

## 🛠️ Tech Stack

<table>
<tr>
<td width="50%">

**Backend**
- 🟢 **Symfony 6.x** - Web framework
- 🗄️ **Doctrine ORM** - Database mapping
- 🔐 **Symfony Security** - Authentication

</td>
<td width="50%">

**Frontend**
- 🎨 **HTML/CSS** - Markup & styling
- ⚡ **Stimulus.js** - JavaScript framework
- 🎯 **Responsive Design** - Friendly UI

</td>
</tr>
</table>

**Database**: PostgreSQL/MySQL with Doctrine ORM  
**Templating**: Twig  
**Version Control**: Git

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Symfony CLI (optional but recommended)
- PostgreSQL/MySQL

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
# Edit .env.local with your database credentials
```

**4️⃣ Setup Database**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

**5️⃣ Load Sample Data** (Optional)
```bash
php bin/console doctrine:fixtures:load
```

**6️⃣ Start Development Server**
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
│   ├── Security/         # 🔒 Security handlers
│   └── DataFixtures/     # 🌱 Sample data
├── templates/            # 🎨 Twig templates
├── migrations/           # 📚 Database migrations
├── config/               # ⚙️  Configuration files
├── public/
│   └── uploads/          # 📦 User uploads (avatars, covers)
├── assets/               # 🎭 CSS & JavaScript
└── tests/                # ✅ Test files
```

---

## 📖 Key Entities

- **User** - Player accounts and profiles
- **Game** - Game catalog entries
- **Reservation** - Game booking records
- **Event** - Community gaming events
- **Purchase** - Purchase history
- **Review** - Game ratings and feedback

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

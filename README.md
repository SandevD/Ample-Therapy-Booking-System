# Ample Therapy - Appointment Booking System

A modern, full-featured appointment booking system built with Laravel, Livewire, and Flux UI. Designed for therapy practices and service-based businesses to manage appointments, staff, services, and customers with an elegant, responsive interface.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat-square&logo=livewire&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)

---

## ✨ Features

### 🎯 Core Functionality

- **Multi-Step Booking Wizard** - Beautiful, guided appointment booking experience with service selection, staff choice, date/time picker, and customer details
- **Interactive Calendar** - Monthly calendar view with appointment indicators and detailed day modals
- **Smart Scheduling** - Conflict detection, buffer times, and availability management
- **Real-time Updates** - Livewire-powered reactive UI without page reloads

### 👥 Role-Based Access Control

| Role            | Access                                                                                                             |
| --------------- | ------------------------------------------------------------------------------------------------------------------ |
| **Super Admin** | Full system access - Dashboard, Appointments, Calendar, Customers, Staff, Services, Administration                 |
| **Staff**       | Scoped access - Dashboard (own stats), own Appointments, Calendar (own schedule), Customers (who booked with them) |
| **Customer**    | Customer portal - Dashboard, Calendar (own bookings), Book Appointment wizard                                      |

### 📊 Dashboard

- Key statistics (Today's appointments, This week, Total customers, Completion rate)
- Upcoming appointments list
- Quick action buttons
- Role-aware data scoping

### 📅 Appointments Management

- List view with search, filter by status/staff
- Create, edit, view, and cancel appointments
- Status workflow: Booked → Confirmed → Completed/Cancelled
- Customer and service details at a glance

### 🗓️ Calendar View

- Monthly grid with appointment indicators
- Click-to-view day details modal
- Color-coded by service
- Modern status badges
- Role-contextual display (Staff sees Customer names, Customers see Staff names)

### 👤 Customer Management

- Customer directory with search
- Individual customer profiles with appointment history
- Quick customer creation
- Staff-scoped visibility

### 👨‍💼 Staff Management

- Staff profiles with bio, services, and availability
- Service assignment
- Weekly availability configuration
- Active/inactive status

### 🛠️ Services Configuration

- Service catalog with name, description, duration, price
- Color coding for visual identification
- Buffer time settings
- Staff assignment

---

## 🛠️ Tech Stack

| Layer           | Technology                         |
| --------------- | ---------------------------------- |
| **Backend**     | Laravel 12.x, PHP 8.2+             |
| **Frontend**    | Livewire 3.x, Flux UI, Alpine.js   |
| **Styling**     | Tailwind CSS 3.x                   |
| **Database**    | MySQL / SQLite                     |
| **Auth**        | Laravel Fortify (with 2FA support) |
| **Permissions** | Spatie Laravel Permission          |
| **Build**       | Vite                               |

---

## 📦 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+ or SQLite

### Setup

1. **Clone the repository**

    ```bash
    git clone https://github.com/your-org/appointment-system.git
    cd appointment-system
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node dependencies**

    ```bash
    npm install
    ```

4. **Configure environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Configure database** in `.env`

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=appointment_system
    DB_USERNAME=root
    DB_PASSWORD=
    ```

6. **Run migrations and seed data**

    ```bash
    php artisan migrate --seed
    ```

7. **Build frontend assets**

    ```bash
    npm run build
    # or for development
    npm run dev
    ```

8. **Start the development server**

    ```bash
    php artisan serve
    ```

    Or use [Laravel Herd](https://herd.laravel.com/) / [Valet](https://laravel.com/docs/valet) for local development.

---

## 🔐 Default Accounts

After seeding, the following accounts are available:

| Role            | Email                       | Password   |
| --------------- | --------------------------- | ---------- |
| **Super Admin** | `admin@ampletherapy.org.uk` | `password` |
| **Staff**       | `hello@ampletherapy.org.uk` | `password` |
| **Customer**    | `john@example.com`          | `password` |
| **Customer**    | `jane@example.com`          | `password` |

---

## 📁 Project Structure

```
appointment-system/
├── app/
│   ├── Livewire/
│   │   ├── Admin/
│   │   │   ├── Appointments/     # Appointment CRUD
│   │   │   ├── Customers/        # Customer management
│   │   │   ├── Services/         # Service configuration
│   │   │   └── Staff/            # Staff management
│   │   ├── Customer/
│   │   │   └── Calendar.php      # Calendar component
│   │   ├── BookingWizard.php     # Multi-step booking
│   │   └── Dashboard.php         # Dashboard statistics
│   ├── Models/
│   │   ├── Appointment.php
│   │   ├── Availability.php
│   │   ├── Service.php
│   │   └── User.php
│   └── Providers/
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── RolesAndPermissionsSeeder.php
│       ├── ServiceSeeder.php
│       └── AppointmentSeeder.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── layouts/
│       └── livewire/
│           ├── admin/
│           ├── customer/
│           ├── booking-wizard.blade.php
│           └── dashboard.blade.php
└── routes/
    └── web.php
```

---

## 🎨 UI Components

This project uses [Flux UI](https://fluxui.dev/) components built on Tailwind CSS:

- `<flux:card>` - Card containers
- `<flux:button>` - Buttons with variants
- `<flux:input>` - Form inputs
- `<flux:select>` - Dropdowns
- `<flux:modal>` - Modal dialogs
- `<flux:badge>` - Status badges
- `<flux:icon>` - Heroicons integration

---

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Tests cover:

- Authentication flows
- Registration with role assignment
- Appointment creation and management
- Access control verification

---

## 📝 Key Livewire Components

### BookingWizard

Multi-step appointment booking with:

- Service selection (Step 1)
- Staff selection with availability (Step 2)
- Date and time slot picker (Step 3)
- Customer details form (Step 4)
- Confirmation (Step 5)

### Dashboard

Role-aware statistics display:

- Appointments today/this week
- Total customers
- Completion rate
- Upcoming appointments list

### Calendar

Interactive monthly calendar:

- Appointment indicators per day
- Detailed modal on day click
- Color-coded services
- Status badges

---

## 🔧 Configuration

### Services

Configure in `database/seeders/ServiceSeeder.php` or via the admin panel:

- Name, description, duration, price
- Color for visual identification
- Buffer time between appointments
- Assigned staff members

### Availability

Staff can set their weekly availability:

- Day of week
- Start and end times
- Managed via Staff edit panel

### Permissions

Managed via Spatie Laravel Permission:

- `view_appointments`, `create_appointments`, `edit_appointments`, `delete_appointments`
- `view_users`, `create_users`, `edit_users`, `delete_users`
- `view_services`, `view_staff`
- And more...

---

## 🚀 Deployment

### Production Build

```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables

Ensure these are set in production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is proprietary software for Ample Therapy.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) - The PHP framework
- [Livewire](https://livewire.laravel.com/) - Full-stack framework for Laravel
- [Flux UI](https://fluxui.dev/) - Beautiful UI components
- [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS framework
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) - Role and permission management
- [Heroicons](https://heroicons.com/) - Beautiful hand-crafted SVG icons

---

<p align="center">
  Made with ❤️ for <strong>Ample Therapy</strong>
</p>

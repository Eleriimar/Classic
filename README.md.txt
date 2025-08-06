🍽️ OnlineFood - Enhanced PHP Food Ordering System
Welcome to the enhanced OnlineFood system, a comprehensive web-based food ordering platform with delivery tracking, mobile payments, and real-time location services. Built using PHP and MySQL with advanced features for modern food delivery operations.

🚀 Enhanced Features

👨‍🍳 Customer Side
- View restaurant menus with categories (e.g., Samosas, Pilau, Beef Stew)
- Add food items to cart with real-time updates
- **Dynamic delivery fee calculation** based on user location and distance zones
- **Real-time order tracking** with Google Maps integration
- **M-Pesa mobile payment** integration
- **GPS location tracking** for accurate delivery
- User authentication with enhanced security
- View past orders and payment history
- **Live delivery status updates**

🚚 Driver Interface
- **Automatic order assignment** when orders are placed
- **Real-time order assignment** and management
- **GPS location tracking** and route optimization
- **Customer location mapping** for efficient delivery
- **Payment collection prompts** (Cash/M-Pesa)
- **Delivery status updates** with timestamps
- **Order history** and earnings tracking
- **Driver login system** with secure authentication

🧑‍💼 Admin Panel
- Add, edit, delete restaurants and food items
- **Driver management** and assignment
- **Delivery tracking** and analytics
- **Payment processing** monitoring
- **Location management** for restaurants
- **Dynamic delivery pricing** with zone-based rates
- **System settings** configuration
- Dashboard with advanced analytics

🔐 Security Features
- **Enhanced authentication** with secure password hashing
- **CSRF protection** for all forms
- **Rate limiting** to prevent abuse
- **Input validation** and sanitization
- **Secure session management**
- **HTTPS enforcement** for production
- **Security logging** and monitoring

🛠️ Tech Stack
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap, Google Maps API
- **Backend**: PHP 7.4+, MySQL 5.7+
- **Payment**: M-Pesa API Integration
- **Location**: Google Maps JavaScript API
- **Security**: Enhanced authentication, CSRF protection, rate limiting
- **Hosting**: Production-ready with SSL/TLS support

📁 Enhanced Project Structure

OnlineFood-PHP/
│
├── admin/                # Admin dashboard and logic
│   ├── order_assignment.php  # 🆕 Order assignment management
├── driver/               # 🆕 Driver interface and APIs
│   ├── login.php            # Driver authentication
│   ├── register.php         # Driver registration
│   ├── logout.php           # Driver logout
│   ├── dashboard.php        # Driver dashboard
│   └── *.php               # Driver API endpoints
├── css/                  # Stylesheets
├── images/               # Static images for food items and UI
├── includes/             # Header, footer, DB connection, etc.
│   ├── location_utils.php    # 🆕 Location and distance calculations
│   ├── mpesa_payment.php     # 🆕 M-Pesa payment integration
│   ├── order_assignment.php  # 🆕 Automatic order assignment system
│   └── security.php          # 🆕 Enhanced security features
├── js/                   # JavaScript and jQuery scripts
├── config/               # 🆕 Configuration files
│   └── production.php        # Production settings
├── logs/                 # 🆕 Application logs
├── user-login/           # Customer authentication logic
├── restaurant/           # Restaurant details and menus
├── order-history/        # View past orders
├── cart/                 # Cart management and checkout
├── contact/              # Contact and feedback
├── index.php             # Home page
├── checkout.php          # 🆕 Enhanced with location tracking
├── your_orders.php       # 🆕 Enhanced with real-time tracking
├── calculate_delivery_fee.php  # 🆕 Delivery fee calculation API
├── get_order_tracking.php      # 🆕 Order tracking API
├── get_order_status.php        # 🆕 Order status API
├── mpesa_callback.php         # 🆕 M-Pesa payment callback
├── DEPLOYMENT_GUIDE.md        # 🆕 Production deployment guide
└── README.md             # Project documentation
⚙️ Installation Guide

### Quick Start (Development)
```bash
# Clone the repository
git clone https://github.com/Eleriimar/Classic.git

# Start Local Server (e.g., XAMPP or WAMP)
# Move Project Folder to htdocs/ (for XAMPP)

# Import the Enhanced Database
# Open phpMyAdmin
# Create a new database: onlinefoodphp
# Import the DATABASE FILE/onlinefoodphp_enhanced.sql file

# Configure Database Connection
# Edit connection/connect.php with your database credentials

# Launch the App
# Visit http://localhost/Classic/ in your browser

### Driver Access
- **Driver Login:** http://localhost/Classic/driver/login.php
- **Driver Registration:** http://localhost/Classic/driver/register.php
- **Demo Credentials:** 
  - Phone: +254700123456
  - Password: driver123
```

### Production Deployment
For production deployment with all enhanced features, see the comprehensive [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md).

### Required API Keys
1. **Google Maps API Key**: Get from [Google Cloud Console](https://console.cloud.google.com/)
2. **M-Pesa API Credentials**: Register at [Safaricom Developer Portal](https://developer.safaricom.co.ke/)

### Environment Setup
Create a `.env` file in the root directory:
```env
# Database
DB_HOST=localhost
DB_USER=your_db_user
DB_PASS=your_db_password
DB_NAME=onlinefoodphp

# M-Pesa (Production)
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_PASSKEY=your_passkey
MPESA_ENVIRONMENT=live

# Google Maps
GOOGLE_MAPS_API_KEY=your_google_maps_api_key

# Security
ENCRYPTION_KEY=your-32-character-encryption-key
SESSION_SECRET=your-session-secret-key
```

🔐 Admin Login
- **URL**: `/admin/index.php`
- **Default Credentials**:
  - Username: `admin`
  - Password: `codeastro`

🚚 Driver Access
- **URL**: `/driver/dashboard.php`
- **Login**: Use driver phone number and password

📸 Screenshots
Add screenshots here showing homepage, menu, cart, and admin panel.

📌 Known Issues
White screen after saving items: usually caused by missing session_start() or unhandled errors. Check error_reporting() settings.

Currency conversion: Modify values from $ to Ksh in all relevant PHP and HTML files.

🙌 Contribution
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

🛠️ Copy `config.sample.php` to `config.php` and add your DB credentials.


📄 License
This project is licensed under the MIT License.
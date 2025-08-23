# Maickel Okereke Professional Website

A modern, professional website built with PHP, MySQL, HTML5, CSS3, and JavaScript featuring a stunning glassmorphism design. This website showcases dual expertise in accounting and web development with a comprehensive blog system and contact management.

## ✨ Features

### 🎨 Modern Design
- **Glassmorphism UI** - Beautiful glass morphism design with blur effects
- **Responsive Layout** - Perfect on all devices (mobile, tablet, desktop)
- **Smooth Animations** - CSS animations and JavaScript interactions
- **Modern Typography** - Inter font for excellent readability

### 🏠 Landing Page
- Hero section with professional introduction
- About section with skills and expertise showcase
- Services section highlighting accounting and web development
- Portfolio section with project showcases
- Client testimonials with ratings
- Recent blog posts preview
- Call-to-action sections

### 📝 Blog System (Twitter-like Social Features)
- **Social Media Style** - Clean, modern Twitter-like interface
- **Like System** - Users can like posts (IP-based tracking)
- **Comment System** - Threaded comments with replies
- **Profile Pictures** - Upload avatars for comments or use Gravatar
- **Search & Filter** - Find posts by keywords
- **Pagination** - Efficient post browsing
- **View Counter** - Track post popularity
- **Share Functionality** - Native sharing API support

### 👨‍💼 Admin Panel
- **Secure Login** - Password hashing and session management
- **Dashboard** - Overview of site statistics
- **Post Management** - Create, edit, delete blog posts
- **Comment Moderation** - Approve, delete inappropriate comments
- **Message Management** - View and respond to contact form submissions
- **Like Analytics** - View post engagement metrics

### 📞 Contact System
- **Professional Contact Form** - Name, email, subject, message
- **Automatic Email Notifications** - Instant admin notifications
- **Auto-Reply System** - Thank you emails to visitors
- **Spam Protection** - Rate limiting and content filtering
- **Message Storage** - All messages saved to database

### 🔒 Security Features
- **SQL Injection Prevention** - Prepared statements throughout
- **CSRF Protection** - Tokens for form submissions
- **XSS Prevention** - Input sanitization and output escaping
- **Rate Limiting** - Prevent spam and abuse
- **Session Security** - Secure session configuration
- **Input Validation** - Client-side and server-side validation

## 🛠️ Technical Stack

- **Backend**: PHP 7.4+ (Object-Oriented)
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Design**: Custom CSS with Glassmorphism effects
- **Icons**: Font Awesome 6.0
- **Fonts**: Google Fonts (Inter)

## 📁 Project Structure

```
maickel-website/
├── assets/
│   ├── css/
│   │   └── style.css              # Main stylesheet with glassmorphism
│   ├── js/
│   │   └── main.js                # JavaScript functionality
│   └── images/                    # Website images and assets
├── classes/
│   ├── User.php                   # User authentication and management
│   ├── Post.php                   # Blog post management
│   ├── Comment.php                # Comment system with threading
│   ├── Like.php                   # Like system management
│   └── Message.php                # Contact form messages
├── config/
│   └── database.php               # Database configuration
├── includes/
│   ├── init.php                   # Application initialization
│   └── functions.php              # Utility functions
├── templates/
│   ├── header.php                 # Common header template
│   └── footer.php                 # Common footer template
├── admin/                         # Admin panel (to be created)
├── api/                          # AJAX endpoints (to be created)
├── uploads/                      # File upload directory
├── database/
│   └── schema.sql                # Database schema
├── index.php                     # Landing page
├── blog.php                      # Blog listing page
├── blog-post.php                 # Individual blog post (to be created)
├── contact.php                   # Contact page (to be created)
└── README.md                     # This file
```

## 🚀 Installation Guide

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- mod_rewrite enabled (for clean URLs)

### Step 1: Download and Extract
1. Download the project files
2. Extract to your web server directory
3. Ensure proper file permissions (755 for directories, 644 for files)

### Step 2: Database Setup
1. Create a MySQL database:
```sql
CREATE DATABASE maickel_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u your_username -p maickel_website < database/schema.sql
```

### Step 3: Configuration
1. Edit `config/database.php`:
```php
private $host = 'localhost';        // Your database host
private $db_name = 'maickel_website'; // Your database name
private $username = 'your_username';  // Your database username
private $password = 'your_password';  // Your database password
```

2. Update site URL in `config/database.php`:
```php
define('SITE_URL', 'https://yourdomain.com');
define('ADMIN_EMAIL', 'your-email@example.com');
```

### Step 4: File Permissions
Create the uploads directory and set permissions:
```bash
mkdir uploads
chmod 777 uploads
```

### Step 5: Admin Account
The default admin credentials are:
- **Username**: admin
- **Password**: admin123

**⚠️ IMPORTANT**: Change this password immediately after first login!

## 🌐 Hosting Guide (Hostinger Example)

### Hostinger Setup
1. **Upload Files**:
   - Use File Manager or FTP to upload all files to `public_html`
   - Ensure the directory structure is preserved

2. **Database Creation**:
   - Go to Hosting → MySQL Databases
   - Create new database: `username_maickel`
   - Import `database/schema.sql` using phpMyAdmin

3. **Configuration**:
   - Edit `config/database.php` with your Hostinger database details
   - Update `SITE_URL` to your domain name

4. **SSL Certificate**:
   - Enable SSL in Hostinger control panel
   - Update `SITE_URL` to use `https://`

5. **Email Setup**:
   - Configure email accounts in Hostinger
   - Test contact form functionality

### Other Hosting Providers
The website works with any PHP hosting provider:
- **cPanel Hosting**: Use File Manager and phpMyAdmin
- **VPS/Dedicated**: Install LAMP stack and configure virtual hosts
- **Cloud Hosting**: AWS, DigitalOcean, Google Cloud

## ⚙️ Configuration Options

### Email Settings
Update in `config/database.php`:
```php
define('ADMIN_EMAIL', 'your-email@domain.com');
```

### Upload Limits
Modify in `includes/functions.php`:
```php
if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
```

### Pagination
Adjust in `config/database.php`:
```php
define('POSTS_PER_PAGE', 5);
define('COMMENTS_PER_PAGE', 10);
```

### Security Settings
Configure in `config/database.php`:
```php
define('SESSION_TIMEOUT', 3600);     // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);     // Login attempts
define('LOCKOUT_TIME', 900);         // 15 minutes
```

## 🎨 Customization

### Color Scheme
Edit CSS variables in `assets/css/style.css`:
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --accent-color: #f093fb;
    /* Customize these colors */
}
```

### Content Updates
1. **Personal Information**: Update in `index.php`
2. **Services**: Modify service cards in `index.php`
3. **Portfolio**: Add your projects in portfolio section
4. **Social Links**: Update in `templates/footer.php`

### Logo and Branding
1. Replace placeholder images in `assets/images/`
2. Update logo text in navigation
3. Customize favicon and app icons

## 📱 Features in Detail

### Blog System
- **Rich Text Content**: Support for HTML content in posts
- **Featured Images**: Upload and display post images
- **SEO Friendly**: Clean URLs and meta tags
- **Social Sharing**: Native share API integration
- **Engagement Metrics**: Track views, likes, and comments

### Comment System
- **Threaded Replies**: Multi-level comment conversations
- **Avatar Support**: Gravatar integration + custom uploads
- **Moderation Tools**: Admin approval and spam filtering
- **User Experience**: Real-time updates via AJAX

### Admin Dashboard
- **Statistics Overview**: Posts, comments, likes, messages
- **Content Management**: WYSIWYG editor for posts
- **User Management**: View and manage admin users
- **Security Monitoring**: Failed login attempts tracking

### Contact Management
- **Form Validation**: Comprehensive input validation
- **Spam Protection**: Multiple anti-spam measures
- **Email Integration**: Automatic notifications and replies
- **Message Organization**: Status tracking and categorization

## 🔧 API Endpoints

The website includes AJAX endpoints for dynamic functionality:

- `POST /api/toggle-like.php` - Like/unlike posts
- `POST /api/add-comment.php` - Submit comments
- `POST /api/search.php` - Search posts
- `POST /contact.php` - Contact form submission

## 🛡️ Security Measures

1. **Input Sanitization**: All user inputs are sanitized
2. **Prepared Statements**: SQL injection prevention
3. **CSRF Tokens**: Cross-site request forgery protection
4. **Rate Limiting**: Prevent spam and brute force attacks
5. **Session Security**: Secure session configuration
6. **File Upload Validation**: Secure file handling
7. **XSS Prevention**: Output escaping and content filtering

## 🚀 Performance Optimizations

- **Lazy Loading**: Images loaded as needed
- **Minified Assets**: Optimized CSS and JavaScript
- **Database Indexing**: Efficient database queries
- **Caching Headers**: Browser caching optimization
- **Compressed Images**: Optimized image delivery
- **CDN Ready**: External resources via CDN

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**:
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists and user has permissions

**File Upload Issues**:
- Check `uploads/` directory permissions (777)
- Verify PHP upload settings in `php.ini`
- Ensure sufficient disk space

**Email Not Working**:
- Check server mail configuration
- Verify SMTP settings if using external mail
- Test with simple PHP mail script

**CSS/JS Not Loading**:
- Check file paths in templates
- Verify web server can serve static files
- Clear browser cache

## 📞 Support

For support or questions:
- **Email**: maickel@example.com
- **Website**: [Your Website]
- **GitHub**: [Your GitHub Profile]

## 📄 License

This project is licensed under the MIT License. Feel free to use, modify, and distribute as needed.

## 🤝 Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 🔄 Updates and Maintenance

### Regular Maintenance
- Update PHP and MySQL versions
- Monitor security vulnerabilities
- Backup database regularly
- Update dependencies

### Future Enhancements
- Multi-language support
- Advanced analytics
- Social media integration
- Newsletter subscription
- RSS feed generation
- Progressive Web App features

---

**Built with ❤️ by Maickel Okereke**

*Professional Accountant & Web Developer*
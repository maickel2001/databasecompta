<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?php echo isset($site_title) ? htmlspecialchars($site_title) : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($description) ? htmlspecialchars($description) : 'Maickel Okereke - Professional Accountant and Web Developer'; ?>">
    <meta name="keywords" content="<?php echo isset($keywords) ? htmlspecialchars($keywords) : 'Maickel Okereke, accountant, web developer, financial services'; ?>">
    <meta name="author" content="Maickel Okereke">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($site_title) ? htmlspecialchars($site_title) : SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($description) ? htmlspecialchars($description) : 'Professional Accountant and Web Developer'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($site_title) ? htmlspecialchars($site_title) : SITE_NAME; ?>">
    <meta name="twitter:description" content="<?php echo isset($description) ? htmlspecialchars($description) : 'Professional Accountant and Web Developer'; ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_URL; ?>/assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>/assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>/assets/images/favicon-16x16.png">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Preconnect to external domains for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "Maickel Okereke",
        "jobTitle": ["Accountant", "Web Developer"],
        "description": "Professional Accountant and Web Developer specializing in financial management and modern web development solutions",
        "url": "<?php echo SITE_URL; ?>",
        "sameAs": [
            "https://linkedin.com/in/maickelokereke",
            "https://github.com/maickelokereke",
            "https://twitter.com/maickelokereke"
        ],
        "knowsAbout": [
            "Accounting",
            "Web Development", 
            "Financial Planning",
            "Tax Preparation",
            "Business Consulting",
            "PHP Development",
            "JavaScript",
            "Database Design"
        ],
        "offers": [
            {
                "@type": "Service",
                "name": "Accounting Services",
                "description": "Comprehensive financial management including bookkeeping, tax preparation, and business consulting"
            },
            {
                "@type": "Service", 
                "name": "Web Development",
                "description": "Modern, responsive websites and web applications built with cutting-edge technologies"
            },
            {
                "@type": "Service",
                "name": "Business Consulting", 
                "description": "Strategic business advice combining financial expertise with technology insights"
            }
        ]
    }
    </script>
    
    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">
    
    <!-- Google Analytics (replace with your tracking ID) -->
    <!-- 
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'GA_TRACKING_ID');
    </script>
    -->
    
    <!-- Performance optimizations -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    
    <!-- Theme color for mobile browsers -->
    <meta name="theme-color" content="#667eea">
    <meta name="msapplication-TileColor" content="#667eea">
    
    <!-- Mobile app capabilities -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Maickel Okereke">
    
    <!-- Security headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="<?php echo SITE_URL; ?>/assets/css/style.css" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" as="style">
</head>
<body>
    <!-- Page loader (optional) -->
    <div class="page-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity 0.3s ease;">
        <div style="text-align: center; color: white;">
            <div style="width: 50px; height: 50px; border: 3px solid rgba(255, 255, 255, 0.3); border-top: 3px solid white; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div>
            <p>Loading...</p>
        </div>
    </div>
    
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
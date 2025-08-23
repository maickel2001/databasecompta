<?php
require_once 'includes/init.php';

// Get recent blog posts for the page
$recent_posts = $post->getRecent(3);

// Page metadata
$page_title = 'Home';
$page_description = 'Maickel Okereke - Professional Accountant and Web Developer. Expert in financial management, bookkeeping, tax preparation, and modern web development solutions.';
$page_keywords = 'Maickel Okereke, accountant, web developer, financial services, bookkeeping, tax preparation, web development, professional services';

includeHeader($page_title, $page_description, $page_keywords);
?>

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <a href="#home" class="logo">Maickel Okereke</a>
        <ul class="nav-menu">
            <li><a href="#home" class="nav-link active">Home</a></li>
            <li><a href="#about" class="nav-link">About</a></li>
            <li><a href="#services" class="nav-link">Services</a></li>
            <li><a href="#portfolio" class="nav-link">Portfolio</a></li>
            <li><a href="#testimonials" class="nav-link">Testimonials</a></li>
            <li><a href="blog.php" class="nav-link">Blog</a></li>
            <li><a href="contact.php" class="nav-link">Contact</a></li>
        </ul>
        <button class="mobile-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<!-- Hero Section -->
<section id="home" class="hero">
    <div class="hero-content">
        <h1 class="hero-title">Maickel Okereke</h1>
        <p class="hero-subtitle">
            Professional <span class="text-gradient">Accountant</span> & 
            <span class="text-gradient">Web Developer</span>
        </p>
        <p class="hero-subtitle">
            Transforming businesses through expert financial management and cutting-edge web solutions
        </p>
        <div class="hero-cta">
            <a href="contact.php" class="btn btn-primary">Contact Me</a>
            <a href="#portfolio" class="btn btn-glass">View My Work</a>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">About Me</h2>
            <p class="section-subtitle">
                Bridging the gap between financial expertise and technological innovation
            </p>
        </div>
        
        <div class="grid grid-2" style="align-items: center;">
            <div class="glass-card">
                <div style="padding: 2rem;">
                    <h3 style="margin-bottom: 1.5rem;">Professional Journey</h3>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.8; margin-bottom: 1.5rem;">
                        With years of experience in both accounting and web development, I bring a unique perspective to business solutions. 
                        My dual expertise allows me to understand the financial implications of technology decisions and create 
                        digital solutions that drive real business value.
                    </p>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.8; margin-bottom: 1.5rem;">
                        As a certified accountant, I specialize in financial planning, tax preparation, and business consulting. 
                        As a web developer, I create modern, responsive websites and applications that help businesses thrive in 
                        the digital age.
                    </p>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <span style="background: rgba(255, 255, 255, 0.1); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem;">CPA Certified</span>
                        <span style="background: rgba(255, 255, 255, 0.1); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem;">Full-Stack Developer</span>
                        <span style="background: rgba(255, 255, 255, 0.1); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem;">Business Consultant</span>
                    </div>
                </div>
            </div>
            
            <div class="glass-card">
                <div style="padding: 2rem;">
                    <h3 style="margin-bottom: 1.5rem;">Skills & Expertise</h3>
                    
                    <div style="margin-bottom: 2rem;">
                        <h4 style="color: rgba(255, 255, 255, 0.9); margin-bottom: 1rem;">Accounting & Finance</h4>
                        <div style="display: grid; gap: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; color: rgba(255, 255, 255, 0.8);">
                                <span>Financial Planning</span>
                                <span>95%</span>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.2); height: 6px; border-radius: 3px;">
                                <div style="background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%); height: 100%; width: 95%; border-radius: 3px;"></div>
                            </div>
                        </div>
                        
                        <div style="display: grid; gap: 0.5rem; margin-top: 1rem;">
                            <div style="display: flex; justify-content: space-between; color: rgba(255, 255, 255, 0.8);">
                                <span>Tax Preparation</span>
                                <span>90%</span>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.2); height: 6px; border-radius: 3px;">
                                <div style="background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%); height: 100%; width: 90%; border-radius: 3px;"></div>
                            </div>
                        </div>
                        
                        <div style="display: grid; gap: 0.5rem; margin-top: 1rem;">
                            <div style="display: flex; justify-content: space-between; color: rgba(255, 255, 255, 0.8);">
                                <span>Business Analysis</span>
                                <span>88%</span>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.2); height: 6px; border-radius: 3px;">
                                <div style="background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%); height: 100%; width: 88%; border-radius: 3px;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 style="color: rgba(255, 255, 255, 0.9); margin-bottom: 1rem;">Web Development</h4>
                        <div style="display: grid; gap: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; color: rgba(255, 255, 255, 0.8);">
                                <span>Frontend Development</span>
                                <span>92%</span>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.2); height: 6px; border-radius: 3px;">
                                <div style="background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%); height: 100%; width: 92%; border-radius: 3px;"></div>
                            </div>
                        </div>
                        
                        <div style="display: grid; gap: 0.5rem; margin-top: 1rem;">
                            <div style="display: flex; justify-content: space-between; color: rgba(255, 255, 255, 0.8);">
                                <span>Backend Development</span>
                                <span>85%</span>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.2); height: 6px; border-radius: 3px;">
                                <div style="background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%); height: 100%; width: 85%; border-radius: 3px;"></div>
                            </div>
                        </div>
                        
                        <div style="display: grid; gap: 0.5rem; margin-top: 1rem;">
                            <div style="display: flex; justify-content: space-between; color: rgba(255, 255, 255, 0.8);">
                                <span>Database Design</span>
                                <span>80%</span>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.2); height: 6px; border-radius: 3px;">
                                <div style="background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%); height: 100%; width: 80%; border-radius: 3px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">My Services</h2>
            <p class="section-subtitle">
                Comprehensive solutions for your business and digital needs
            </p>
        </div>
        
        <div class="grid grid-3">
            <div class="glass-card card">
                <div class="card-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <h3 class="card-title">Accounting Services</h3>
                <p class="card-text">
                    Comprehensive financial management including bookkeeping, financial statement preparation, 
                    tax planning, and business consulting to help your business thrive.
                </p>
                <ul style="text-align: left; color: rgba(255, 255, 255, 0.8); margin-top: 1rem;">
                    <li>Bookkeeping & Record Keeping</li>
                    <li>Financial Statement Preparation</li>
                    <li>Tax Preparation & Planning</li>
                    <li>Business Financial Consulting</li>
                    <li>Payroll Management</li>
                </ul>
            </div>
            
            <div class="glass-card card">
                <div class="card-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3 class="card-title">Web Development</h3>
                <p class="card-text">
                    Modern, responsive websites and web applications built with the latest technologies. 
                    From simple business websites to complex web applications.
                </p>
                <ul style="text-align: left; color: rgba(255, 255, 255, 0.8); margin-top: 1rem;">
                    <li>Responsive Website Design</li>
                    <li>E-commerce Solutions</li>
                    <li>Web Application Development</li>
                    <li>Content Management Systems</li>
                    <li>SEO Optimization</li>
                </ul>
            </div>
            
            <div class="glass-card card">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="card-title">Business Consulting</h3>
                <p class="card-text">
                    Strategic business advice combining financial expertise with technology insights 
                    to help businesses optimize operations and achieve growth.
                </p>
                <ul style="text-align: left; color: rgba(255, 255, 255, 0.8); margin-top: 1rem;">
                    <li>Financial Analysis & Planning</li>
                    <li>Digital Transformation</li>
                    <li>Process Optimization</li>
                    <li>Technology Strategy</li>
                    <li>Performance Analytics</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Section -->
<section id="portfolio" class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Portfolio</h2>
            <p class="section-subtitle">
                Some of my recent work and successful projects
            </p>
        </div>
        
        <div class="grid grid-3">
            <div class="glass-card portfolio-item">
                <img src="https://via.placeholder.com/400x250/667eea/ffffff?text=Financial+Dashboard" alt="Financial Dashboard" class="portfolio-image">
                <div class="portfolio-overlay">
                    <h3>Financial Dashboard</h3>
                    <p>A comprehensive financial management dashboard for small businesses with real-time reporting and analytics.</p>
                    <div style="margin-top: 1rem;">
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">PHP</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">MySQL</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">Chart.js</span>
                    </div>
                </div>
            </div>
            
            <div class="glass-card portfolio-item">
                <img src="https://via.placeholder.com/400x250/764ba2/ffffff?text=E-commerce+Platform" alt="E-commerce Platform" class="portfolio-image">
                <div class="portfolio-overlay">
                    <h3>E-commerce Platform</h3>
                    <p>Modern e-commerce solution with integrated accounting features and automated financial reporting.</p>
                    <div style="margin-top: 1rem;">
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">React</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">Node.js</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">MongoDB</span>
                    </div>
                </div>
            </div>
            
            <div class="glass-card portfolio-item">
                <img src="https://via.placeholder.com/400x250/f093fb/ffffff?text=Tax+Calculator" alt="Tax Calculator" class="portfolio-image">
                <div class="portfolio-overlay">
                    <h3>Tax Calculator App</h3>
                    <p>Interactive tax calculation tool for individuals and businesses with real-time updates and tax planning features.</p>
                    <div style="margin-top: 1rem;">
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">JavaScript</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">Vue.js</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">API</span>
                    </div>
                </div>
            </div>
            
            <div class="glass-card portfolio-item">
                <img src="https://via.placeholder.com/400x250/4facfe/ffffff?text=Business+Website" alt="Business Website" class="portfolio-image">
                <div class="portfolio-overlay">
                    <h3>Professional Business Website</h3>
                    <p>Modern, responsive website for a consulting firm with integrated contact management and service booking.</p>
                    <div style="margin-top: 1rem;">
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">HTML5</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">CSS3</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">WordPress</span>
                    </div>
                </div>
            </div>
            
            <div class="glass-card portfolio-item">
                <img src="https://via.placeholder.com/400x250/00f2fe/ffffff?text=Inventory+System" alt="Inventory System" class="portfolio-image">
                <div class="portfolio-overlay">
                    <h3>Inventory Management System</h3>
                    <p>Comprehensive inventory tracking system with automated reordering and financial integration.</p>
                    <div style="margin-top: 1rem;">
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">PHP</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">Laravel</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">MySQL</span>
                    </div>
                </div>
            </div>
            
            <div class="glass-card portfolio-item">
                <img src="https://via.placeholder.com/400x250/f5576c/ffffff?text=Analytics+Platform" alt="Analytics Platform" class="portfolio-image">
                <div class="portfolio-overlay">
                    <h3>Business Analytics Platform</h3>
                    <p>Advanced analytics dashboard providing insights into business performance with customizable reports.</p>
                    <div style="margin-top: 1rem;">
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">Python</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.5rem;">Django</span>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">PostgreSQL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Blog Posts Section -->
<?php if (!empty($recent_posts)): ?>
<section id="blog-preview" class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Latest Blog Posts</h2>
            <p class="section-subtitle">
                Insights on accounting, web development, and business strategy
            </p>
        </div>
        
        <div class="grid grid-3">
            <?php foreach ($recent_posts as $blog_post): ?>
            <article class="glass-card blog-post">
                <?php if ($blog_post['featured_image']): ?>
                <img src="<?php echo UPLOAD_URL . $blog_post['featured_image']; ?>" alt="<?php echo htmlspecialchars($blog_post['title']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 12px 12px 0 0;">
                <?php endif; ?>
                
                <div style="padding: 1.5rem;">
                    <div class="blog-meta">
                        <span><?php echo date('M j, Y', strtotime($blog_post['created_at'])); ?></span>
                        <span>•</span>
                        <span><?php echo $blog_post['view_count']; ?> views</span>
                    </div>
                    
                    <h3 class="blog-title">
                        <a href="blog-post.php?slug=<?php echo $blog_post['slug']; ?>">
                            <?php echo htmlspecialchars($blog_post['title']); ?>
                        </a>
                    </h3>
                    
                    <p class="blog-excerpt">
                        <?php echo htmlspecialchars($blog_post['excerpt']); ?>
                    </p>
                    
                    <div style="margin-top: 1rem;">
                        <a href="blog-post.php?slug=<?php echo $blog_post['slug']; ?>" class="btn btn-glass" style="padding: 8px 16px; font-size: 0.875rem;">Read More</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="blog.php" class="btn btn-primary">View All Posts</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Testimonials Section -->
<section id="testimonials" class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Client Testimonials</h2>
            <p class="section-subtitle">
                What my clients say about working with me
            </p>
        </div>
        
        <div class="grid grid-2">
            <div class="glass-card testimonial">
                <p class="testimonial-text">
                    "Maickel's dual expertise in accounting and web development has been invaluable to our business. 
                    He not only built us a fantastic website but also helped us streamline our financial processes. 
                    His understanding of both domains allowed him to create solutions that perfectly fit our needs."
                </p>
                <div class="testimonial-author">
                    <img src="https://via.placeholder.com/60x60/667eea/ffffff?text=JS" alt="John Smith" class="testimonial-avatar">
                    <div class="testimonial-info">
                        <h4>John Smith</h4>
                        <p>CEO, Tech Solutions Inc.</p>
                    </div>
                </div>
            </div>
            
            <div class="glass-card testimonial">
                <p class="testimonial-text">
                    "Working with Maickel has been a game-changer for our small business. His accounting services are thorough and professional, 
                    and the website he built for us has significantly increased our online presence. He's responsive, reliable, and truly cares about his clients' success."
                </p>
                <div class="testimonial-author">
                    <img src="https://via.placeholder.com/60x60/764ba2/ffffff?text=MD" alt="Maria Davis" class="testimonial-avatar">
                    <div class="testimonial-info">
                        <h4>Maria Davis</h4>
                        <p>Owner, Davis Marketing</p>
                    </div>
                </div>
            </div>
            
            <div class="glass-card testimonial">
                <p class="testimonial-text">
                    "I hired Maickel for both accounting services and to develop a custom web application for my business. 
                    His technical skills are impressive, but what sets him apart is his ability to understand business requirements from a financial perspective. 
                    Highly recommended!"
                </p>
                <div class="testimonial-author">
                    <img src="https://via.placeholder.com/60x60/f093fb/ffffff?text=RJ" alt="Robert Johnson" class="testimonial-avatar">
                    <div class="testimonial-info">
                        <h4>Robert Johnson</h4>
                        <p>Founder, Johnson Consulting</p>
                    </div>
                </div>
            </div>
            
            <div class="glass-card testimonial">
                <p class="testimonial-text">
                    "Maickel's expertise in both accounting and technology helped us modernize our entire business operation. 
                    He implemented systems that not only look great but also provide valuable insights into our financial performance. 
                    Professional, knowledgeable, and always available when we need support."
                </p>
                <div class="testimonial-author">
                    <img src="https://via.placeholder.com/60x60/4facfe/ffffff?text=LW" alt="Lisa Wong" class="testimonial-avatar">
                    <div class="testimonial-info">
                        <h4>Lisa Wong</h4>
                        <p>Director, Wong Enterprises</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="section" style="padding: 4rem 0;">
    <div class="container">
        <div class="glass-card" style="padding: 3rem; text-align: center;">
            <h2 style="margin-bottom: 1rem;">Ready to Transform Your Business?</h2>
            <p style="font-size: 1.125rem; color: rgba(255, 255, 255, 0.8); margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Let's work together to streamline your finances and create a powerful online presence for your business. 
                Contact me today for a free consultation.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="contact.php" class="btn btn-primary">Get Started Today</a>
                <a href="tel:+1234567890" class="btn btn-glass">Call Now</a>
            </div>
        </div>
    </div>
</section>

<!-- Back to Top Button -->
<a href="#home" class="back-to-top" style="display: none; position: fixed; bottom: 2rem; right: 2rem; width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); transition: all 0.3s ease;">
    <i class="fas fa-arrow-up"></i>
</a>

<?php includeFooter(); ?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
// Add CSRF token for AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    const metaTag = document.createElement('meta');
    metaTag.name = 'csrf-token';
    metaTag.content = '<?php echo generate_csrf_token(); ?>';
    document.head.appendChild(metaTag);
});
</script>
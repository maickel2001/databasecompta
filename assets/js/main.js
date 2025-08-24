/**
 * Maickel Okereke Professional Website
 * Main JavaScript File
 */

// DOM loaded event
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all features
    initNavbar();
    initScrollAnimations();
    initLikeSystem();
    initCommentSystem();
    initContactForm();
    initPortfolioFilter();
    initMobileMenu();
    initSmoothScroll();
});

// Navbar functionality
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Active nav link highlighting
    function updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const scrollPos = window.scrollY + 200;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPos >= sectionTop && scrollPos <= sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }
    
    window.addEventListener('scroll', updateActiveNavLink);
    updateActiveNavLink(); // Initial call
}

// Scroll animations
function initScrollAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animateElements = document.querySelectorAll('.card, .portfolio-item, .testimonial, .blog-post');
    animateElements.forEach(el => observer.observe(el));
}

// Like system
function initLikeSystem() {
    const likeButtons = document.querySelectorAll('.like-btn');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const postId = this.getAttribute('data-post-id');
            const likeCount = this.querySelector('.like-count');
            
            // Prevent multiple clicks
            if (this.classList.contains('loading')) return;
            
            this.classList.add('loading');
            
            // AJAX request to toggle like
            fetch('/api/toggle-like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    post_id: postId,
                    csrf_token: getCSRFToken()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    this.classList.toggle('liked', data.liked);
                    likeCount.textContent = data.like_count;
                    
                    // Add animation
                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                } else {
                    showMessage(data.message || 'Error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Network error occurred', 'error');
            })
            .finally(() => {
                this.classList.remove('loading');
            });
        });
    });
}

// Comment system
function initCommentSystem() {
    const commentForms = document.querySelectorAll('.comment-form');
    const replyButtons = document.querySelectorAll('.reply-btn');
    
    // Handle comment form submissions
    commentForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Prevent multiple submissions
            if (submitBtn.classList.contains('loading')) return;
            
            submitBtn.classList.add('loading');
            submitBtn.textContent = 'Posting...';
            
            fetch('/api/add-comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset form
                    this.reset();
                    
                    // Add new comment to DOM
                    addCommentToDOM(data.comment);
                    
                    showMessage('Comment posted successfully!', 'success');
                    
                    // Update comment count
                    updateCommentCount(data.post_id, data.total_comments);
                } else {
                    showMessage(data.message || 'Error posting comment', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Network error occurred', 'error');
            })
            .finally(() => {
                submitBtn.classList.remove('loading');
                submitBtn.textContent = 'Post Comment';
            });
        });
    });
    
    // Handle reply buttons
    replyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const replyForm = document.querySelector(`#reply-form-${commentId}`);
            
            if (replyForm) {
                replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
            }
        });
    });
    
    // Avatar upload preview
    const avatarInputs = document.querySelectorAll('input[name="avatar"]');
    avatarInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            const preview = this.parentElement.querySelector('.avatar-preview');
            
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

// Contact form
function initContactForm() {
    const contactForm = document.querySelector('.contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Prevent multiple submissions
            if (submitBtn.classList.contains('loading')) return;
            
            submitBtn.classList.add('loading');
            submitBtn.textContent = 'Sending...';
            
            fetch('/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    showMessage('Message sent successfully! I\'ll get back to you soon.', 'success');
                } else {
                    showMessage(data.message || 'Error sending message', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Network error occurred', 'error');
            })
            .finally(() => {
                submitBtn.classList.remove('loading');
                submitBtn.textContent = 'Send Message';
            });
        });
    }
}

// Portfolio filter (if needed)
function initPortfolioFilter() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter items
            portfolioItems.forEach(item => {
                if (filter === 'all' || item.classList.contains(filter)) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeInUp 0.5s ease forwards';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
}

// Mobile menu
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Close menu when clicking on nav links
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
            });
        });
    }
}

// Smooth scroll
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 100; // Account for fixed navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Utility functions
function getCSRFToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

function showMessage(message, type = 'info') {
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `message message-${type}`;
    messageEl.innerHTML = `
        <div class="message-content">
            <span class="message-text">${message}</span>
            <button class="message-close">&times;</button>
        </div>
    `;
    
    // Add styles
    messageEl.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 16px 20px;
        color: white;
        max-width: 400px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    // Color based on type
    if (type === 'success') {
        messageEl.style.borderColor = 'rgba(72, 187, 120, 0.5)';
    } else if (type === 'error') {
        messageEl.style.borderColor = 'rgba(245, 101, 101, 0.5)';
    }
    
    document.body.appendChild(messageEl);
    
    // Animate in
    setTimeout(() => {
        messageEl.style.transform = 'translateX(0)';
    }, 100);
    
    // Close button
    const closeBtn = messageEl.querySelector('.message-close');
    closeBtn.addEventListener('click', () => removeMessage(messageEl));
    
    // Auto remove after 5 seconds
    setTimeout(() => removeMessage(messageEl), 5000);
}

function removeMessage(messageEl) {
    if (messageEl && messageEl.parentNode) {
        messageEl.style.transform = 'translateX(100%)';
        setTimeout(() => {
            messageEl.parentNode.removeChild(messageEl);
        }, 300);
    }
}

function addCommentToDOM(comment) {
    const commentsContainer = document.querySelector('.comments-list');
    if (!commentsContainer) return;
    
    const commentHTML = `
        <div class="comment" id="comment-${comment.id}">
            <div class="comment-header">
                <img src="${comment.avatar_url}" alt="${comment.author_name}" class="comment-avatar">
                <div class="comment-meta">
                    <span class="comment-author">${comment.author_name}</span>
                    <span class="comment-date">${comment.date}</span>
                </div>
            </div>
            <div class="comment-content">${comment.content}</div>
            <div class="comment-actions">
                <button class="reply-btn" data-comment-id="${comment.id}">Reply</button>
            </div>
        </div>
    `;
    
    commentsContainer.insertAdjacentHTML('afterbegin', commentHTML);
    
    // Animate new comment
    const newComment = document.querySelector(`#comment-${comment.id}`);
    newComment.style.opacity = '0';
    newComment.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        newComment.style.opacity = '1';
        newComment.style.transform = 'translateY(0)';
        newComment.style.transition = 'all 0.3s ease';
    }, 100);
}

function updateCommentCount(postId, count) {
    const commentCountElements = document.querySelectorAll(`[data-post-id="${postId}"] .comment-count`);
    commentCountElements.forEach(el => {
        el.textContent = count;
    });
}

// Search functionality
function initSearch() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.querySelector('.search-results');
    
    if (searchForm && searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            } else {
                if (searchResults) {
                    searchResults.innerHTML = '';
                    searchResults.style.display = 'none';
                }
            }
        });
        
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query.length >= 3) {
                performSearch(query);
            }
        });
    }
}

function performSearch(query) {
    const searchResults = document.querySelector('.search-results');
    if (!searchResults) return;
    
    searchResults.innerHTML = '<div class="search-loading">Searching...</div>';
    searchResults.style.display = 'block';
    
    fetch('/api/search.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ query: query })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySearchResults(data.results);
        } else {
            searchResults.innerHTML = '<div class="search-error">Search failed</div>';
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        searchResults.innerHTML = '<div class="search-error">Search error occurred</div>';
    });
}

function displaySearchResults(results) {
    const searchResults = document.querySelector('.search-results');
    if (!searchResults) return;
    
    if (results.length === 0) {
        searchResults.innerHTML = '<div class="search-empty">No results found</div>';
        return;
    }
    
    const resultsHTML = results.map(result => `
        <div class="search-result">
            <h4><a href="${result.url}">${result.title}</a></h4>
            <p>${result.excerpt}</p>
            <small>${result.date}</small>
        </div>
    `).join('');
    
    searchResults.innerHTML = resultsHTML;
}

// Lazy loading images
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if (images.length === 0) return;
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading
document.addEventListener('DOMContentLoaded', initLazyLoading);

// Page loading animation
window.addEventListener('load', function() {
    const loader = document.querySelector('.page-loader');
    if (loader) {
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.style.display = 'none';
        }, 300);
    }
});

// Back to top button
function initBackToTop() {
    const backToTopBtn = document.querySelector('.back-to-top');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });
        
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Initialize back to top
document.addEventListener('DOMContentLoaded', initBackToTop);
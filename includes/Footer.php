<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-about">
                <a href="#" class="footer-logo"><span class="highlight">Tharidu</span>Sadanuwan</a>
                <p>Freelance designer and developer helping businesses create exceptional digital experiences.</p>
                <div class="social-links">
                    <a href="https://x.com/ThariduSadanuw1?t=lZ04HYJUowKeYokW8gHvVQ&s=09" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/tharidu_sranasinha?igsh=dXR4YWdnem8xc2pm" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com/in/tharidu-sadanuwan-78b5a9299/" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
                    <a href="https://www.facebook.com/share/169HYZrknk/" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#portfolio">Portfolio</a></li>
                    <!--<li><a href="#testimonials">Testimonials</a></li> -->
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Services</h3>
                <ul>
                    <li><a href="#">UI/UX Design</a></li>
                    <li><a href="#">Development</a></li>
                    <li><a href="#">Mobile Development</a></li>
                    <li><a href="#">Design</a></li>
                    <li><a href="#">SEO Optimization</a></li>
                    <li><a href="#">3D modeling</a></li>
                </ul>
            </div>
            <div class="footer-newsletter">
                <h3>Stay Updated</h3>
                <p>Subscribe to my newsletter for the latest news and insights.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <span id="currentYear">2025</span> Tharidu. All rights reserved.</p>
            <div class="footer-legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>




<script>
    // Theme Toggle Functionality
    const themeToggle = document.getElementById('themeToggle');
    const mobileThemeToggle = document.getElementById('mobileThemeToggle');
    
    // Check for saved theme preference or use the system preference
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    const savedTheme = localStorage.getItem('theme');
    
    // If the user has explicitly chosen a theme, use it
    if (savedTheme === 'dark') {
        document.body.classList.add('dark');
    } else if (savedTheme === 'light') {
        document.body.classList.remove('dark');
    } else {
        // Otherwise, use the system preference
        if (prefersDarkScheme.matches) {
            document.body.classList.add('dark');
        }
    }
    
    // Toggle theme when button is clicked
    function toggleTheme() {
        document.body.classList.toggle('dark');
        
        // Save the theme preference
        if (document.body.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    }
    
    // Add event listeners to both toggle buttons
    themeToggle.addEventListener('click', toggleTheme);
    mobileThemeToggle.addEventListener('click', toggleTheme);
    
    // Rest of your JavaScript code...
</script>

<script type="text/javascript">
    // Select all the portfolio links
    let previewContainer = document.querySelector('.products-preview');
    let previewBoxes = previewContainer.querySelectorAll('.preview');
    
    document.querySelectorAll('.portfolio-link').forEach(link => {
        link.onclick = (e) => {
            e.preventDefault();
            let target = link.getAttribute('data-target');
    
            // Show the preview modal
            previewContainer.style.display = 'flex';
    
            // Show the correct preview
            previewBoxes.forEach(preview => {
                if (preview.getAttribute('data-target') === target) {
                    preview.classList.add('active');
                }
            });
        };
    });
    
    // Close the preview modal when clicking the close icon
    document.querySelectorAll('.fa-times').forEach(closeBtn => {
        closeBtn.onclick = () => {
            previewContainer.style.display = 'none';
            previewBoxes.forEach(preview => preview.classList.remove('active'));
        };
    });
    
    // Close the preview modal when clicking outside of it
    previewContainer.onclick = (e) => {
        if (e.target === previewContainer) {
            previewContainer.style.display = 'none';
            previewBoxes.forEach(preview => preview.classList.remove('active'));
        }
    };
    
    </script>

</body>
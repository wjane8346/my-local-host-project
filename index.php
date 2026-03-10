<?php
require_once 'includes/db_connect.php';
$currentUser = getCurrentUser($conn);

// Get statistics for the homepage
$stats = [
    'students' => 0,
    'companies' => 0,
    'jobs' => 0,
    'placements' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM students");
if ($result) $stats['students'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM companies");
if ($result) $stats['companies'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM jobs WHERE deadline > CURDATE()");
if ($result) $stats['jobs'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status IN ('shortlisted', 'interview', 'accepted')");
if ($result) $stats['placements'] = $result->fetch_assoc()['count'];

// Get featured jobs
$featured_jobs = $conn->query("
    SELECT j.*, c.name as company_name 
    FROM jobs j 
    JOIN companies c ON j.company_id = c.id 
    WHERE j.deadline > CURDATE() 
    ORDER BY j.created_at DESC 
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartIntern Kenya - Connect Students, Companies & Institutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            line-height: 1.6;
        }
        
        /* Kenyan Flag Header */
        .flag-header {
            height: 4px;
            background: linear-gradient(90deg, 
                #000000 0%, #000000 33.33%,
                #b91c1c 33.33%, #b91c1c 66.66%,
                #059669 66.66%, #059669 100%);
        }
        
        /* Navbar */
        .navbar {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #4ade80;
            text-decoration: none;
        }
        
        .navbar-brand i {
            color: #4ade80;
            margin-right: 8px;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: #4ade80;
        }
        
        .btn-login {
            border: 1px solid #4ade80;
            color: #4ade80;
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-login:hover {
            background: #4ade80;
            color: #0f172a;
        }
        
        .btn-register {
            background: #4ade80;
            color: #0f172a;
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-register:hover {
            background: #22c55e;
        }
        
        /* Hero Section */
        .hero {
            padding: 80px 0;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            text-align: center;
        }
        
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
        }
        
        .hero h1 span {
            color: #4ade80;
        }
        
        .hero p {
            font-size: 1.2rem;
            color: #94a3b8;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
        }
        
        .hero-stat {
            text-align: center;
        }
        
        .hero-stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #4ade80;
        }
        
        .hero-stat-label {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        /* User Sections */
        .user-sections {
            padding: 60px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .section-title h2 {
            font-size: 2.2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: #94a3b8;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .users-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .user-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .user-card:hover {
            border-color: #4ade80;
            transform: translateY(-10px);
            box-shadow: 0 20px 30px rgba(0,0,0,0.3);
        }
        
        .user-icon {
            width: 80px;
            height: 80px;
            background: #0f172a;
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.5rem;
            color: #4ade80;
            border: 1px solid #334155;
        }
        
        .user-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            margin-bottom: 20px;
        }
        
        .user-features {
            list-style: none;
            padding: 0;
            margin: 20px 0;
            text-align: left;
        }
        
        .user-features li {
            color: #94a3b8;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-features i {
            color: #4ade80;
            width: 20px;
        }
        
        .user-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .student-btn {
            background: #4ade80;
            color: #0f172a;
        }
        
        .student-btn:hover {
            background: #22c55e;
            transform: translateY(-2px);
        }
        
        .company-btn {
            border: 1px solid #4ade80;
            color: #4ade80;
        }
        
        .company-btn:hover {
            background: #4ade80;
            color: #0f172a;
        }
        
        .institution-btn {
            background: transparent;
            border: 1px solid #4ade80;
            color: #4ade80;
        }
        
        .institution-btn:hover {
            background: #4ade80;
            color: #0f172a;
        }
        
        /* Featured Jobs */
        .jobs-section {
            padding: 60px 0;
            background: #0f172a;
        }
        
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 40px;
        }
        
        .job-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 25px;
            transition: all 0.3s;
        }
        
        .job-card:hover {
            border-color: #4ade80;
        }
        
        .job-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            margin-bottom: 8px;
        }
        
        .job-company {
            color: #4ade80;
            font-weight: 500;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .job-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        .job-details i {
            color: #4ade80;
            width: 16px;
            margin-right: 5px;
        }
        
        .job-salary {
            font-weight: 600;
            color: #4ade80;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .btn-job {
            color: #4ade80;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
        }
        
        .btn-job:hover {
            text-decoration: underline;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #059669, #4ade80);
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 15px;
        }
        
        .cta-section p {
            font-size: 1.1rem;
            color: #0f172a;
            margin-bottom: 30px;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        
        .btn-cta {
            background: #0f172a;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-cta:hover {
            background: #1e293b;
            transform: translateY(-2px);
        }
        
        .btn-cta-outline {
            border: 2px solid #0f172a;
            color: #0f172a;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-cta-outline:hover {
            background: #0f172a;
            color: white;
        }
        
        /* Footer */
        .footer {
            background: #0f172a;
            border-top: 1px solid #334155;
            padding: 40px 0;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 40px;
        }
        
        .footer-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: #4ade80;
            margin-bottom: 15px;
            display: block;
            text-decoration: none;
        }
        
        .footer-about {
            color: #94a3b8;
            margin-bottom: 20px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-links a {
            width: 36px;
            height: 36px;
            background: #1e293b;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .social-links a:hover {
            background: #4ade80;
            color: #0f172a;
        }
        
        .footer-title {
            font-weight: 600;
            margin-bottom: 20px;
            color: white;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-links a:hover {
            color: #4ade80;
        }
        
        .footer-contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #94a3b8;
        }
        
        .footer-contact-item i {
            color: #4ade80;
            width: 20px;
        }
        
        .footer-bottom {
            border-top: 1px solid #334155;
            padding-top: 20px;
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 40px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .users-grid,
            .jobs-grid,
            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                gap: 15px;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            .users-grid,
            .jobs-grid,
            .footer-content {
                grid-template-columns: 1fr;
            }
            .hero h1 {
                font-size: 2rem;
            }
            .hero-stats {
                flex-direction: column;
                gap: 20px;
            }
            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="flag-header"></div>
    
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-shield-alt"></i>SmartIntern Kenya
            </a>
            
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="internships.php">Internships</a>
                
                <?php if ($currentUser): ?>
                    <span style="color: #94a3b8;">Welcome, <?= htmlspecialchars($currentUser['name']) ?></span>
                    <?php
                    $dashboard = '';
                    if ($_SESSION['user_type'] == 'student') {
                        $dashboard = 'student/dashboard.php';
                    } elseif ($_SESSION['user_type'] == 'company') {
                        $dashboard = 'company/dashboard.php';
                    } else {
                        $dashboard = 'institution/dashboard.php';
                    }
                    ?>
                    <a href="<?= $dashboard ?>" class="btn-login">Dashboard</a>
                    <a href="logout.php" style="color: #ef4444;">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Login</a>
                    <a href="register.php" class="btn-register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Connecting <span>Students</span> • <span>Companies</span> • <span>Institutions</span></h1>
            <p>Kenya's premier platform for internship opportunities. Find the perfect match for your career journey.</p>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-number"><?= $stats['students'] ?: '500+' ?></div>
                    <div class="hero-stat-label">Active Students</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number"><?= $stats['companies'] ?: '50+' ?></div>
                    <div class="hero-stat-label">Partner Companies</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number"><?= $stats['jobs'] ?: '200+' ?></div>
                    <div class="hero-stat-label">Open Internships</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number"><?= $stats['placements'] ?: '300+' ?></div>
                    <div class="hero-stat-label">Successful Placements</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- User Sections -->
    <section class="user-sections">
        <div class="container">
            <div class="section-title">
                <h2>Who We Serve</h2>
                <p>Three platforms in one - tailored for each user type</p>
            </div>
            
            <div class="users-grid">
                <!-- Student Card -->
                <div class="user-card">
                    <div class="user-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>For Students</h3>
                    <ul class="user-features">
                        <li><i class="fas fa-check-circle"></i> Find internships matching your skills</li>
                        <li><i class="fas fa-check-circle"></i> Track application status</li>
                        <li><i class="fas fa-check-circle"></i> AI-powered job recommendations</li>
                        <li><i class="fas fa-check-circle"></i> Build professional profile</li>
                        <li><i class="fas fa-check-circle"></i> Get interview alerts</li>
                    </ul>
                    <a href="login.php?type=student" class="user-btn student-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>Student Login
                    </a>
                </div>
                
                <!-- Company Card -->
                <div class="user-card">
                    <div class="user-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>For Companies</h3>
                    <ul class="user-features">
                        <li><i class="fas fa-check-circle"></i> Post internship opportunities</li>
                        <li><i class="fas fa-check-circle"></i> AI candidate matching</li>
                        <li><i class="fas fa-check-circle"></i> Review applications</li>
                        <li><i class="fas fa-check-circle"></i> Schedule interviews</li>
                        <li><i class="fas fa-check-circle"></i> Find top talent</li>
                    </ul>
                    <a href="login.php?type=company" class="user-btn company-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>Company Login
                    </a>
                </div>
                
                <!-- Institution Card -->
                <div class="user-card">
                    <div class="user-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3>For Institutions</h3>
                    <ul class="user-features">
                        <li><i class="fas fa-check-circle"></i> Track student progress</li>
                        <li><i class="fas fa-check-circle"></i> Generate placement reports</li>
                        <li><i class="fas fa-check-circle"></i> ML analytics insights</li>
                        <li><i class="fas fa-check-circle"></i> Partner with companies</li>
                        <li><i class="fas fa-check-circle"></i> At-risk student detection</li>
                    </ul>
                    <a href="login.php?type=institution" class="user-btn institution-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>Institution Login
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Jobs -->
    <section class="jobs-section">
        <div class="container">
            <div class="section-title">
                <h2>Featured Internships</h2>
                <p>Latest opportunities from our partner companies</p>
            </div>
            
            <div class="jobs-grid">
                <?php if ($featured_jobs && $featured_jobs->num_rows > 0): ?>
                    <?php while($job = $featured_jobs->fetch_assoc()): ?>
                    <div class="job-card">
                        <h3><?= htmlspecialchars($job['title']) ?></h3>
                        <div class="job-company">
                            <i class="fas fa-building"></i> <?= htmlspecialchars($job['company_name']) ?>
                        </div>
                        <div class="job-details">
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location']) ?></span>
                            <span><i class="fas fa-clock"></i> <?= htmlspecialchars($job['duration']) ?></span>
                        </div>
                        <div class="job-salary">
                            KSh <?= number_format($job['salary']) ?>/month
                        </div>
                        <a href="job-details.php?id=<?= $job['id'] ?>" class="btn-job">View Details →</a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Sample jobs -->
                    <div class="job-card">
                        <h3>Software Developer Intern</h3>
                        <div class="job-company">
                            <i class="fas fa-building"></i> Safaricom PLC
                        </div>
                        <div class="job-details">
                            <span><i class="fas fa-map-marker-alt"></i> Nairobi</span>
                            <span><i class="fas fa-clock"></i> 3 months</span>
                        </div>
                        <div class="job-salary">
                            KSh 45,000/month
                        </div>
                        <a href="job-details.php?id=1" class="btn-job">View Details →</a>
                    </div>
                    
                    <div class="job-card">
                        <h3>Banking Intern</h3>
                        <div class="job-company">
                            <i class="fas fa-building"></i> KCB Bank
                        </div>
                        <div class="job-details">
                            <span><i class="fas fa-map-marker-alt"></i> Nairobi</span>
                            <span><i class="fas fa-clock"></i> 6 months</span>
                        </div>
                        <div class="job-salary">
                            KSh 35,000/month
                        </div>
                        <a href="job-details.php?id=2" class="btn-job">View Details →</a>
                    </div>
                    
                    <div class="job-card">
                        <h3>Data Science Intern</h3>
                        <div class="job-company">
                            <i class="fas fa-building"></i> Strathmore University
                        </div>
                        <div class="job-details">
                            <span><i class="fas fa-map-marker-alt"></i> Nairobi</span>
                            <span><i class="fas fa-clock"></i> 4 months</span>
                        </div>
                        <div class="job-salary">
                            KSh 40,000/month
                        </div>
                        <a href="job-details.php?id=3" class="btn-job">View Details →</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="internships.php" class="btn-login">View All Internships</a>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of students, companies, and institutions already using SmartIntern Kenya</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn-cta">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </a>
                <a href="login.php" class="btn-cta-outline">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <a href="index.php" class="footer-brand">
                        <i class="fas fa-shield-alt me-2"></i>SmartIntern Kenya
                    </a>
                    <p class="footer-about">
                        Connecting students, companies, and institutions across Kenya.
                        Bridging the gap between education and industry.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="footer-title">For Students</h4>
                    <ul class="footer-links">
                        <li><a href="internships.php">Browse Internships</a></li>
                        <li><a href="register.php">Create Account</a></li>
                        <li><a href="student/dashboard.php">Student Dashboard</a></li>
                        <li><a href="student/matches.php">Job Matches</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">For Companies</h4>
                    <ul class="footer-links">
                        <li><a href="company/register.php">Post Internships</a></li>
                        <li><a href="company/dashboard.php">Company Dashboard</a></li>
                        <li><a href="company/ai-matches.php">Find Candidates</a></li>
                        <li><a href="company/manage-jobs.php">Manage Jobs</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Contact Us</h4>
                    <div class="footer-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Nairobi, Kenya</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+254 700 000 000</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>info@smartintern.co.ke</span>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 SmartIntern Kenya. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
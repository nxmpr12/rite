<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'siyutkin';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}

function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Фитнес-клуб СТАЛЬ - Cyber Fitness</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Cyber Background Elements -->
    <div class="cyber-grid"></div>
    <div class="hologram-lines"></div>
    <div class="energy-orb"></div>
    <div class="energy-orb"></div>
    <div class="energy-orb"></div>
    <div class="noise-overlay"></div>
    
    <!-- Cyber Loader -->
    <div class="loader">
        <div class="loader-content">
            <div class="cyber-spinner"></div>
            <div class="loader-text">СТАЛЬ CYBER</div>
            <div class="loader-subtext">INITIALIZING FITNESS SYSTEM</div>
        </div>
    </div>

    <!-- Cyber Header -->
    <header>
        <div class="header-content">
            <a href="index.php" class="cyber-logo">
                <i class="fas fa-dumbbell logo-icon"></i>
                <span class="logo-text">СТАЛЬ</span>
            </a>
            <div class="cyber-nav">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-home"></i>Главная
                </a>
                <a href="register.php" class="nav-link">
                    <i class="fas fa-user-plus"></i>Регистрация
                </a>
                <a href="auto.php" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i>Вход
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i>Профиль
                </a>
                <?php if (!empty($_SESSION['login'])): ?>
                    <?php if ($_SESSION['status'] == 1): ?>
                        <a href="admin.php" class="nav-link admin-panel">
                            <i class="fas fa-cog"></i>Админ-панель
                        </a>
                    <?php endif; ?>
                    <form action="index.php" method="post" class="logout-form">
                        <button type="submit" name="out" value="Выход" class="cyber-button logout">
                            <i class="fas fa-sign-out-alt"></i>Выход
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="main-content">
                <!-- Cyber Sidebar -->
                <div class="cyber-sidebar">
                    <div class="search-container">
                        <form method="post" action="index.php">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="search_text" placeholder="ПОИСК ТОВАРОВ..." 
                                       value="<?php echo isset($_POST['search_text']) ? xss_clean($_POST['search_text']) : ''; ?>">
                            </div>
                            <button type="submit" name="search" class="cyber-button search-btn">
                                <i class="fas fa-bolt"></i>НАЙТИ
                            </button>
                        </form>
                    </div>
                    
                    <div class="category-panel">
                        <h3 class="panel-title"><i class="fas fa-tags"></i> КАТЕГОРИИ</h3>
                        <?php
                        $query = "SELECT * FROM `category`";
                        $result = mysqli_query($conn, $query);
                        if ($result) {
                            while ($row = mysqli_fetch_array($result)) {
                        ?>
                        <form action="index.php" method="post">
                            <button type="submit" name="category" value="<?php echo xss_clean($row[1]); ?>" 
                                    class="category-btn cyber-button">
                                <i class="fas fa-chevron-right"></i>
                                <?php echo xss_clean($row[1]); ?>
                            </button>
                        </form>
                        <?php
                            }
                            mysqli_free_result($result);
                        } else {
                            echo '<div class="cyber-alert error">ОШИБКА ЗАГРУЗКИ КАТЕГОРИЙ</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Main Products Grid -->
                <div class="cyber-main">
                    <?php
                    if (isset($_POST['out']) && $_POST['out'] == 'Выход') {
                        $_SESSION['login'] = '';
                        $_SESSION['status'] = '';
                        header('Location: index.php');
                        exit;
                    }

                    $query = "SELECT * FROM product";
                    $result = null;

                    if (isset($_POST['category']) && !empty($_POST['category'])) {
                        $category = mysqli_real_escape_string($conn, $_POST['category']);
                        if ($category == 'Все товары') {
                            $query = "SELECT * FROM product";
                            $result = mysqli_query($conn, $query);
                        } else {
                            $query = "SELECT * FROM product WHERE category = ?";
                            $stmt = mysqli_prepare($conn, $query);
                            if ($stmt) {
                                mysqli_stmt_bind_param($stmt, "s", $category);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                mysqli_stmt_close($stmt);
                            }
                        }
                    } elseif (isset($_POST['search']) && !empty($_POST['search_text'])) {
                        $search_text = mysqli_real_escape_string($conn, $_POST['search_text']);
                        $query = "SELECT * FROM product WHERE name LIKE ? OR category LIKE ? OR description1 LIKE ? OR description2 LIKE ?";
                        $stmt = mysqli_prepare($conn, $query);
                        if ($stmt) {
                            $search_param = "%$search_text%";
                            mysqli_stmt_bind_param($stmt, "ssss", $search_param,$search_param, $search_param, $search_param);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            mysqli_stmt_close($stmt);
                        }
                    } else {
                        $result = mysqli_query($conn, $query);
                    }

                    if (!$result) {
                        echo '<div class="cyber-alert error">ОШИБКА ЗАГРУЗКИ ТОВАРОВ</div>';
                    } elseif (mysqli_num_rows($result) == 0) {
                        echo '<div class="no-products">
                                <i class="fas fa-search cyber-icon"></i>
                                <h3>ТОВАРЫ НЕ НАЙДЕНЫ</h3>
                                <p>Попробуйте изменить параметры поиска</p>
                              </div>';
                    } else {
                        while ($row = mysqli_fetch_array($result)) {
                    ?>
                    <div class="cyber-product animate-on-scroll">
                        <div class="product-hologram"></div>
                        <img src="img/<?php echo xss_clean($row['img'] ?? 'default_product.jpg'); ?>" 
                             alt="<?php echo xss_clean($row['name']); ?>" 
                             class="product-image">
                        <h3 class="product-title"><?php echo xss_clean($row['name']); ?></h3>
                        <span class="product-category">
                            <i class="fas fa-tag"></i>
                            <?php echo xss_clean($row['category']); ?>
                        </span>
                        <p class="product-description"><?php echo xss_clean($row['description1']); ?></p>
                        <div class="product-price">
                            <span class="price"><?php echo number_format($row['price'], 0, ',', ' '); ?> ₽</span>
                        </div>
                        <a href="product.php?id=<?php echo xss_clean($row['id']); ?>" 
                           class="cyber-button detail-btn">
                            <i class="fas fa-eye"></i>ПОДРОБНЕЕ
                        </a>
                    </div>
                    <?php
                        }
                        mysqli_free_result($result);
                    }
                    mysqli_close($conn);
                    ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Cyber Footer -->
    <footer class="cyber-footer">
        <div class="footer-content">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>ФИТНЕС-КЛУБ СТАЛЬ</h3>
                    <p>Cyber Fitness оборудование для профессиональных тренировок нового поколения</p>
                </div>
                <div class="footer-section">
                    <h3>КОНТАКТЫ</h3>
                    <p>Якутск, ул. Киберспортивная, 42</p>
                    <p>+7 (999) 123-45-67</p>
                </div>
                <div class="footer-section">
                    <h3>СОЦИАЛЬНЫЕ СЕТИ</h3>
                    <div class="social-grid">
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-vk"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 ФИТНЕС-КЛУБ СТАЛЬ CYBER. ВСЕ ПРАВА ЗАЩИЩЕНЫ.</p>
            </div>
        </div>
    </footer>

    <script src="js/cyber-effects.js"></script>
    <script>
        // Hide loader when page is loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.querySelector('.loader').classList.add('hidden');
            }, 2000);
        });

        // Initialize cyber effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add particles
            const particlesContainer = document.createElement('div');
            particlesContainer.className = 'particles-container';
            document.body.appendChild(particlesContainer);

            for (let i = 0; i < 30; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.animationDelay = `${Math.random() * 20}s`;
                particle.style.background = i % 3 === 0 ? 'var(--primary)' : 
                                         i % 3 === 1 ? 'var(--secondary)' : 'var(--accent)';
                particlesContainer.appendChild(particle);
            }

            // Scroll animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('cyber-visible');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.cyber-product').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
<script>
// Fast loader with mobile detection
window.addEventListener('load', function() {
    const loader = document.querySelector('.loader');
    if (loader) {
        // Faster loading on mobile
        const isMobile = window.innerWidth < 768;
        setTimeout(() => {
            loader.classList.add('hidden');
        }, isMobile ? 300 : 500);
    }
});

// Mobile menu and optimizations
document.addEventListener('DOMContentLoaded', function() {
    // Add optimized particles (fewer on mobile)
    const particlesContainer = document.createElement('div');
    particlesContainer.className = 'particles-container';
    document.body.appendChild(particlesContainer);

    const particleCount = window.innerWidth < 768 ? 10 : 20;
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = `${Math.random() * 100}vw`;
        particle.style.animationDelay = `${Math.random() * 25}s`;
        particle.style.background = i % 3 === 0 ? 'var(--primary)' : 
                                 i % 3 === 1 ? 'var(--secondary)' : 'var(--accent)';
        particlesContainer.appendChild(particle);
    }

    // Scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('cyber-visible');
            }
        });
    }, { 
        threshold: window.innerWidth < 768 ? 0.05 : 0.1 
    });

    document.querySelectorAll('.cyber-product').forEach(el => {
        observer.observe(el);
    });

    // Mobile navigation touch improvements
    if (window.innerWidth < 768) {
        document.querySelectorAll('.nav-link, .cyber-button').forEach(button => {
            button.style.cursor = 'pointer';
        });
    }
});

// Handle resize events for better mobile experience
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        // Re-initialize particles on orientation change
        const container = document.querySelector('.particles-container');
        if (container) {
            container.innerHTML = '';
            const particleCount = window.innerWidth < 768 ? 10 : 20;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.animationDelay = `${Math.random() * 25}s`;
                particle.style.background = i % 3 === 0 ? 'var(--primary)' : 
                                         i % 3 === 1 ? 'var(--secondary)' : 'var(--accent)';
                container.appendChild(particle);
            }
        }
    }, 250);
});
</script>
</body>
</html>
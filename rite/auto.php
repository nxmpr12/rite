<?php
session_start();

function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

if (isset($_POST['out']) && $_POST['out'] == 'Выход') {
    $_SESSION['login'] = '';
    $_SESSION['status'] = '';
    header('Location: index.php');
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'siyutkin';
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация - Cyber Fitness</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Cyber Background Elements -->
    <div class="cyber-grid"></div>
    <div class="hologram-lines"></div>
    <div class="energy-orb"></div>
    <div class="noise-overlay"></div>

    <!-- Cyber Header -->
    <header>
        <div class="header-content">
            <a href="index.php" class="cyber-logo">
                <i class="fas fa-dumbbell logo-icon"></i>
                <span class="logo-text">СТАЛЬ</span>
            </a>
            <div class="cyber-nav">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>Главная
                </a>
                <a href="register.php" class="nav-link">
                    <i class="fas fa-user-plus"></i>Регистрация
                </a>
                <a href="auto.php" class="nav-link active">
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
                    <form action="auto.php" method="post" class="logout-form">
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
            <div class="form-wrapper">
                <div class="cyber-form">
                    <h2 class="form-title">АВТОРИЗАЦИЯ В СИСТЕМЕ</h2>
                    
                    <?php
                    if (isset($_POST['auto']) && $_POST['auto'] == 'Войти') {
                        $login = trim($_POST['login'] ?? '');
                        $password = $_POST['pass'] ?? '';

                        if (empty($login) || empty($password)) {
                            echo '<div class="cyber-alert error">ЗАПОЛНИТЕ ВСЕ ПОЛЯ</div>';
                        } else {
                            $query = "SELECT * FROM `reg` WHERE `login` = ? AND `pass` = ?";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "ss", $login, $password);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            $row = mysqli_fetch_array($result);

                            if ($row) {
                                $_SESSION['login'] = $login;
                                $_SESSION['status'] = $row['status'];
                                echo '<div class="cyber-alert success">ВЫ УСПЕШНО ВОШЛИ В СИСТЕМУ! 
                                      <a href="profile.php">ПЕРЕЙТИ В ЛИЧНЫЙ КАБИНЕТ</a></div>';
                                if ($row['status'] == 1) {
                                    echo '<div class="cyber-alert success">ДОСТУП К <a href="admin.php">ПАНЕЛИ АДМИНИСТРАТОРА</a> РАЗРЕШЕН</div>';
                                }
                            } else {
                                echo '<div class="cyber-alert error">НЕВЕРНЫЙ ЛОГИН ИЛИ ПАРОЛЬ</div>';
                            }
                            mysqli_stmt_close($stmt);
                        }
                    }
                    ?>
                    
                    <form action="auto.php" method="post" class="auth-form">
                        <div class="form-group">
                            <label for="login" class="form-label">ЛОГИН</label>
                            <input type="text" id="login" name="login" class="cyber-input" 
                                   value="<?php echo isset($_POST['login']) ? xss_clean($_POST['login']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="pass" class="form-label">ПАРОЛЬ</label>
                            <input type="password" id="pass" name="pass" class="cyber-input" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="auto" value="Войти" class="cyber-button submit-btn">
                                <i class="fas fa-sign-in-alt"></i>ВОЙТИ В СИСТЕМУ
                            </button>
                        </div>
                        
                        <div class="form-links">
                            <a href="register.php" class="cyber-button link-btn">
                                <i class="fas fa-user-plus"></i>СОЗДАТЬ АККАУНТ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="cyber-footer">
        <div class="footer-content">
            <div class="footer-bottom">
                <p>&copy; 2024 ФИТНЕС-КЛУБ СТАЛЬ CYBER. ВСЕ ПРАВА ЗАЩИЩЕНЫ.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.createElement('div');
            particlesContainer.className = 'particles-container';
            document.body.appendChild(particlesContainer);

            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.animationDelay = `${Math.random() * 20}s`;
                particle.style.background = i % 3 === 0 ? 'var(--primary)' : 
                                         i % 3 === 1 ? 'var(--secondary)' : 'var(--accent)';
                particlesContainer.appendChild(particle);
            }
        });
    </script>

    <style>
        .form-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
            padding: 40px 0;
        }

        .form-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 900;
            background: var(--cyber-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 40px;
            font-family: 'Orbitron', monospace;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: var(--neon-glow);
        }

        .auth-form {
            max-width: 400px;
            margin: 0 auto;
        }

        .submit-btn {
            width: 100%;
            justify-content: center;
            font-size: 1.1rem;
            padding: 18px 30px;
            margin-bottom: 20px;
        }

        .form-links {
            text-align: center;
        }

        .link-btn {
            background: rgba(0, 255, 255, 0.1);
            border-color: var(--secondary);
        }

        .link-btn:hover {
            background: var(--cyber-gradient);
        }

        @media (max-width: 768px) {
            .form-title {
                font-size: 2rem;
            }
        }
    </style>
</body>
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
</html>
<?php
mysqli_close($conn);
?>
<?php
session_start();

function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

if (isset($_POST['out']) && $_POST['out'] == 'Выход') {
    $_SESSION['login'] = '';
    $_SESSION['status'] = '';
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Cyber Fitness</title>
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
                <a href="register.php" class="nav-link active">
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
                    <form action="register.php" method="post" class="logout-form">
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
                    <h2 class="form-title">РЕГИСТРАЦИЯ В СИСТЕМЕ</h2>
                    
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
                        $login = trim($_POST['login'] ?? '');
                        $mail = trim($_POST['mail'] ?? '');
                        $pass1 = $_POST['pass1'] ?? '';
                        $pass2 = $_POST['pass2'] ?? '';
                        $family = trim($_POST['family'] ?? '');
                        $name = trim($_POST['name'] ?? '');
                        $about_name = trim($_POST['about_name'] ?? '');

                        $errors = [];

                        if (empty($login) || empty($mail) || empty($pass1) || empty($pass2) || empty($family) || empty($name) || empty($about_name)) {
                            $errors[] = 'ЗАПОЛНИТЕ ВСЕ ПОЛЯ';
                        }
                        if ($pass1 !== $pass2) {
                            $errors[] = 'ПАРОЛИ НЕ СОВПАДАЮТ';
                        }
                        if (strlen($pass1) < 6) {
                            $errors[] = 'ПАРОЛЬ ДОЛЖЕН БЫТЬ НЕ МЕНЕЕ 6 СИМВОЛОВ';
                        }
                        if (!validate_email($mail)) {
                            $errors[] = 'НЕКОРРЕКТНЫЙ EMAIL';
                        }
                        if (strlen($login) < 3 || strlen($login) > 50) {
                            $errors[] = 'ЛОГИН ДОЛЖЕН БЫТЬ ОТ 3 ДО 50 СИМВОЛОВ';
                        }

                        if (empty($errors)) {
                            $conn = mysqli_connect('localhost', 'root', '', 'siyutkin') or die('Ошибка подключения: ' . mysqli_connect_error());

                            $query = "SELECT COUNT(*) FROM `reg` WHERE `login` = ? OR `mail` = ?";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "ss", $login, $mail);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_bind_result($stmt, $count);
                            mysqli_stmt_fetch($stmt);
                            mysqli_stmt_close($stmt);

                            if ($count > 0) {
                                $errors[] = 'ЛОГИН ИЛИ EMAIL ЗАНЯТ';
                            } else {
                                $query = "INSERT INTO `reg` (`login`, `mail`, `pass`, `status`, `family`, `name`, `about_name`) VALUES (?, ?, ?, '0', ?, ?, ?)";
                                $stmt = mysqli_prepare($conn, $query);
                                mysqli_stmt_bind_param($stmt, "ssssss", $login, $mail, $pass1, $family, $name, $about_name);

                                if (mysqli_stmt_execute($stmt)) {
                                    echo '<div class="cyber-alert success">РЕГИСТРАЦИЯ УСПЕШНА! <a href="auto.php">ВОЙДИТЕ В СИСТЕМУ</a></div>';
                                } else {
                                    $errors[] = 'ОШИБКА: ' . mysqli_error($conn);
                                }
                                mysqli_stmt_close($stmt);
                            }
                            mysqli_close($conn);
                        }

                        if (!empty($errors)) {
                            foreach ($errors as $error) {
                                echo '<div class="cyber-alert error">' . xss_clean($error) . '</div>';
                            }
                        }
                    } else {
                    ?>
                    <form method="POST" action="register.php" class="form-grid">
                        <div class="form-group">
                            <label for="login" class="form-label">ЛОГИН</label>
                            <input type="text" id="login" name="login" class="cyber-input" 
                                   value="<?php echo isset($_POST['login']) ? xss_clean($_POST['login']) : ''; ?>" 
                                   required minlength="3" maxlength="50">
                        </div>
                        
                        <div class="form-group">
                            <label for="family" class="form-label">ФАМИЛИЯ</label>
                            <input type="text" id="family" name="family" class="cyber-input"
                                   value="<?php echo isset($_POST['family']) ? xss_clean($_POST['family']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="name" class="form-label">ИМЯ</label>
                            <input type="text" id="name" name="name" class="cyber-input"
                                   value="<?php echo isset($_POST['name']) ? xss_clean($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="about_name" class="form-label">ОТЧЕСТВО</label>
                            <input type="text" id="about_name" name="about_name" class="cyber-input"
                                   value="<?php echo isset($_POST['about_name']) ? xss_clean($_POST['about_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="mail" class="form-label">EMAIL</label>
                            <input type="email" id="mail" name="mail" class="cyber-input"
                                   value="<?php echo isset($_POST['mail']) ? xss_clean($_POST['mail']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="pass1" class="form-label">ПАРОЛЬ</label>
                            <input type="password" id="pass1" name="pass1" class="cyber-input" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="pass2" class="form-label">ПОВТОРИТЕ ПАРОЛЬ</label>
                            <input type="password" id="pass2" name="pass2" class="cyber-input" required minlength="6">
                        </div>
                        
                        <div class="form-group full-width">
                            <button type="submit" name="register" value="Зарегистрироваться" class="cyber-button submit-btn">
                                <i class="fas fa-user-plus"></i>ЗАРЕГИСТРИРОВАТЬСЯ
                            </button>
                        </div>
                    </form>
                    <?php
                    }
                    ?>
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
        // Add particles
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .submit-btn {
            width: 100%;
            justify-content: center;
            font-size: 1.1rem;
            padding: 18px 30px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-title {
                font-size: 2rem;
            }
        }
    </style>
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
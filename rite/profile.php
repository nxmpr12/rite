<?php
session_start();

function xss_clean($data) {
    if (is_array($data)) {
        return array_map('xss_clean', $data);
    }
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

if (empty($_SESSION['login'])) {
    header('Location: auto.php');
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

function handle_file_upload($file, $upload_dir, $allowed_types, $max_size) {
    $errors = [];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'ОШИБКА ЗАГРУЗКИ ФАЙЛА';
        return [false, $errors];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = 'НЕДОПУСТИМЫЙ ТИП ФАЙЛА';
    }
    if ($file['size'] > $max_size) {
        $errors[] = 'ФАЙЛ СЛИШКОМ БОЛЬШОЙ';
    }
    if (!empty($errors)) {
        return [false, $errors];
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('avatar_') . '.' . $ext;
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [true, $filename];
    } else {
        $errors[] = 'ОШИБКА ПРИ СОХРАНЕНИИ ФАЙЛА';
        return [false, $errors];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - Cyber Fitness</title>
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
                <a href="auto.php" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i>Вход
                </a>
                <a href="profile.php" class="nav-link active">
                    <i class="fas fa-user"></i>Профиль
                </a>
                <?php if (!empty($_SESSION['login'])): ?>
                    <?php if ($_SESSION['status'] == 1): ?>
                        <a href="admin.php" class="nav-link admin-panel">
                            <i class="fas fa-cog"></i>Админ-панель
                        </a>
                    <?php endif; ?>
                    <form action="profile.php" method="post" class="logout-form">
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
            <div class="profile-container">
                <h2 class="profile-title">ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ</h2>
                
                <?php
                $login = $_SESSION['login'];
                $query = "SELECT * FROM `reg` WHERE `login` = ?";
                $stmt_user = mysqli_prepare($conn, $query);
                if (!$stmt_user) {
                    echo '<div class="cyber-alert error">ОШИБКА ПОДГОТОВКИ ЗАПРОСА</div>';
                    mysqli_close($conn);
                    exit;
                }
                mysqli_stmt_bind_param($stmt_user, "s", $login);
                mysqli_stmt_execute($stmt_user);
                $result = mysqli_stmt_get_result($stmt_user);
                $row = mysqli_fetch_array($result);
                mysqli_stmt_close($stmt_user);

                if (!$row) {
                    echo '<div class="cyber-alert error">ПОЛЬЗОВАТЕЛЬ НЕ НАЙДЕН</div>';
                    mysqli_close($conn);
                    exit;
                }

                // Avatar upload
                if (isset($_POST['img_user']) && $_POST['img_user'] == 'Изменить аватар') {
                    if (isset($_FILES['img']) && $_FILES['img']['error'] == UPLOAD_ERR_OK) {
                        $upload_dir = 'img/';
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                        $max_size = 2 * 1024 * 1024;
                        
                        list($success, $result) = handle_file_upload($_FILES['img'], $upload_dir, $allowed_types, $max_size);
                        
                        if ($success) {
                            $filename = $result;
                            $query = "UPDATE `reg` SET `img` = ? WHERE `id` = ?";
                            $stmt_img = mysqli_prepare($conn, $query);
                            if ($stmt_img) {
                                mysqli_stmt_bind_param($stmt_img, "si", $filename, $row['id']);
                                if (mysqli_stmt_execute($stmt_img)) {
                                    echo '<div class="cyber-alert success">АВАТАР УСПЕШНО ОБНОВЛЕН</div>';
                                    // Refresh user data
                                    $query = "SELECT * FROM `reg` WHERE `login` = ?";
                                    $stmt_refresh = mysqli_prepare($conn, $query);
                                    if ($stmt_refresh) {
                                        mysqli_stmt_bind_param($stmt_refresh, "s", $login);
                                        mysqli_stmt_execute($stmt_refresh);
                                        $result = mysqli_stmt_get_result($stmt_refresh);
                                        $row = mysqli_fetch_array($result);
                                        mysqli_stmt_close($stmt_refresh);
                                    }
                                } else {
                                    echo '<div class="cyber-alert error">ОШИБКА ПРИ ОБНОВЛЕНИИ АВАТАРА</div>';
                                }
                                mysqli_stmt_close($stmt_img);
                            }
                        } else {
                            foreach ($result as $error) {
                                echo '<div class="cyber-alert error">' . xss_clean($error) . '</div>';
                            }
                        }
                    } else {
                        echo '<div class="cyber-alert error">ВЫБЕРИТЕ ФАЙЛ ДЛЯ ЗАГРУЗКИ</div>';
                    }
                }

                // Update user data
                if (isset($_POST['update_user']) && $_POST['update_user'] == 'Изменить контакты') {
                    $id = (int)$_POST['id'];
                    $login2 = trim($_POST['login2'] ?? '');
                    $mail = trim($_POST['mail'] ?? '');
                    $pass2 = $_POST['pass2'] ?? '';
                    $name = trim($_POST['name'] ?? '');
                    $surname = trim($_POST['surname'] ?? '');
                    $patronymic = trim($_POST['patronymic'] ?? '');

                    $errors = [];
                    if (empty($login2) || empty($mail) || empty($pass2) || empty($name) || empty($surname) || empty($patronymic)) {
                        $errors[] = 'ЗАПОЛНИТЕ ВСЕ ПОЛЯ';
                    }
                    if (!validate_email($mail)) {
                        $errors[] = 'НЕКОРРЕКТНЫЙ EMAIL';
                    }
                    if (strlen($login2) < 3 || strlen($login2) > 50) {
                        $errors[] = 'ЛОГИН ДОЛЖЕН БЫТЬ ОТ 3 ДО 50 СИМВОЛОВ';
                    }
                    
                    if (empty($errors)) {
                        $query = "SELECT COUNT(*) FROM `reg` WHERE (`login` = ? OR `mail` = ?) AND `id` != ?";
                        $stmt_check = mysqli_prepare($conn, $query);
                        if ($stmt_check) {
                            mysqli_stmt_bind_param($stmt_check, "ssi", $login2, $mail, $id);
                            mysqli_stmt_execute($stmt_check);
                            mysqli_stmt_bind_result($stmt_check, $count);
                            mysqli_stmt_fetch($stmt_check);
                            mysqli_stmt_close($stmt_check);

                            if ($count > 0) {
                                $errors[] = 'ЛОГИН ИЛИ EMAIL ЗАНЯТ';
                            } else {
                                $query = "UPDATE `reg` SET `login` = ?, `mail` = ?, `pass` = ?, `name` = ?, `family` = ?, `about_name` = ? WHERE `id` = ?";
                                $stmt_update = mysqli_prepare($conn, $query);
                                if ($stmt_update) {
                                    mysqli_stmt_bind_param($stmt_update, "ssssssi", $login2, $mail, $pass2, $name, $surname, $patronymic, $id);
                                    if (mysqli_stmt_execute($stmt_update)) {
                                        $_SESSION['login'] = $login2;
                                        echo '<div class="cyber-alert success">ДАННЫЕ УСПЕШНО ОБНОВЛЕНЫ</div>';
                                        // Refresh user data
                                        $query = "SELECT * FROM `reg` WHERE `login` = ?";
                                        $stmt_refresh = mysqli_prepare($conn, $query);
                                        if ($stmt_refresh) {
                                            mysqli_stmt_bind_param($stmt_refresh, "s", $login2);
                                            mysqli_stmt_execute($stmt_refresh);
                                            $result = mysqli_stmt_get_result($stmt_refresh);
                                            $row = mysqli_fetch_array($result);
                                            mysqli_stmt_close($stmt_refresh);
                                        }
                                    } else {
                                        $errors[] = 'ОШИБКА ПРИ ОБНОВЛЕНИИ ДАННЫХ';
                                    }
                                    mysqli_stmt_close($stmt_update);
                                }
                            }
                        }
                    }
                    
                    if (!empty($errors)) {
                        foreach ($errors as $error) {
                            echo '<div class="cyber-alert error">' . xss_clean($error) . '</div>';
                        }
                    }
                }
                ?>

                <div class="profile-content">
                    <!-- Avatar Section -->
                    <div class="avatar-section">
                        <div class="cyber-card">
                            <h3 class="card-title">АВАТАР</h3>
                            <div class="avatar-container">
                                <img src="img/<?php echo xss_clean($row['img'] ?: 'default_avatar.jpg'); ?>" 
                                     alt="Аватар" class="avatar-image">
                            </div>
                            <form action="profile.php" method="post" enctype="multipart/form-data" class="avatar-form">
                                <div class="form-group">
                                    <label for="img" class="file-label">
                                        <i class="fas fa-upload"></i>
                                        ВЫБРАТЬ ФАЙЛ
                                    </label>
                                    <input type="file" id="img" name="img" accept="image/jpeg,image/png,image/gif" class="file-input">
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="id" value="<?php echo xss_clean($row['id']); ?>">
                                    <button type="submit" name="img_user" value="Изменить аватар" class="cyber-button">
                                        <i class="fas fa-sync"></i>ОБНОВИТЬ АВАТАР
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Info Section -->
                    <div class="info-section">
                        <div class="cyber-card">
                            <h3 class="card-title">ИНФОРМАЦИЯ О ПОЛЬЗОВАТЕЛЕ</h3>
                            <form action="profile.php" method="post" class="info-form">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="login2" class="form-label">ЛОГИН</label>
                                        <input type="text" id="login2" name="login2" class="cyber-input" 
                                               value="<?php echo xss_clean($row['login']); ?>" required minlength="3" maxlength="50">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mail" class="form-label">ПОЧТА</label>
                                        <input type="email" id="mail" name="mail" class="cyber-input" 
                                               value="<?php echo xss_clean($row['mail']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="pass2" class="form-label">ПАРОЛЬ</label>
                                        <input type="text" id="pass2" name="pass2" class="cyber-input" 
                                               value="<?php echo xss_clean($row['pass']); ?>" required minlength="6">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="name" class="form-label">ИМЯ</label>
                                        <input type="text" id="name" name="name" class="cyber-input" 
                                               value="<?php echo xss_clean($row['name']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="surname" class="form-label">ФАМИЛИЯ</label>
                                        <input type="text" id="surname" name="surname" class="cyber-input" 
                                               value="<?php echo xss_clean($row['family']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="patronymic" class="form-label">ОТЧЕСТВО</label>
                                        <input type="text" id="patronymic" name="patronymic" class="cyber-input" 
                                               value="<?php echo xss_clean($row['about_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <input type="hidden" name="id" value="<?php echo xss_clean($row['id']); ?>">
                                    <button type="submit" name="update_user" value="Изменить контакты" class="cyber-button submit-btn">
                                        <i class="fas fa-save"></i>СОХРАНИТЬ ИЗМЕНЕНИЯ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
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

            // File input styling
            const fileInput = document.querySelector('.file-input');
            const fileLabel = document.querySelector('.file-label');
            
            if (fileInput && fileLabel) {
                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        fileLabel.innerHTML = `<i class="fas fa-check"></i> ${this.files[0].name}`;
                    }
                });
            }
        });
    </script>

    <style>
        .profile-container {
            padding: 40px 0;
        }

        .profile-title {
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

        .profile-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .cyber-card {
            background: rgba(26, 26, 26, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 0, 128, 0.3);
            border-radius: 15px;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .cyber-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--cyber-gradient);
        }

        .card-title {
            color: var(--secondary);
            font-family: 'Orbitron', monospace;
            font-size: 1.3rem;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .avatar-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .avatar-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            box-shadow: var(--neon-glow);
            transition: all 0.4s ease;
        }

        .avatar-image:hover {
            transform: scale(1.05);
            border-color: var(--secondary);
        }

        .file-input {
            display: none;
        }

        .file-label {
            display: inline-block;
            padding: 12px 25px;
            background: rgba(255, 0, 128, 0.1);
            border: 2px solid var(--primary);
            border-radius: 8px;
            color: var(--text);
            cursor: pointer;
            transition: all 0.4s ease;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .file-label:hover {
            background: var(--cyber-gradient);
            color: var(--dark);
            transform: translateY(-2px);
            box-shadow: var(--neon-glow);
        }

        .info-form .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .submit-btn {
            width: 100%;
            justify-content: center;
            font-size: 1.1rem;
            padding: 18px 30px;
            margin-top: 20px;
        }

        @media (max-width: 968px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
            
            .info-form .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .profile-title {
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
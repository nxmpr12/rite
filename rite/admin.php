<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

if (isset($_POST['out']) && $_POST['out'] == 'Выход') { 
    $_SESSION['login'] = ''; 
    $_SESSION['status'] = '';
    header('Location: index.php');
    exit;
}

if ($_SESSION['status'] != 1) {
    echo "<script>
        alert('Вы не администратор'); 
        location.href='index.php';
    </script>";
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админ-панель - Фитнес-клуб Сталь</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header>
        <div class="header-buttons">
            <a href="index.php">Главная</a>
            <a href="register.php">Регистрация</a>
            <a href="auto.php">Авторизация</a>
            <a href="profile.php">Профиль</a>
            <?php if (!empty($_SESSION['login'])): ?>
                <?php if ($_SESSION['status'] == 1): ?>
                    <a href="admin.php" class="admin-panel-btn">Админ-панель</a>
                <?php endif; ?>
                <form action="admin.php" method="post" class="logout-form">
                    <input type="submit" name="out" value="Выход" class="logout-button">
                </form>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="admin-container">
            <h1>Админ-панель</h1>
            
            <div class="admin-actions">
                <form action="add.php" method="post" class="action-form">
                    <button type="submit" name="add_pol" value="Добавить пользователя" class="admin-btn">Добавить пользователя</button>
                    <button type="submit" name="add_cat" value="Добавить категорию" class="admin-btn">Добавить категорию</button>
                    <button type="submit" name="add_tov" value="Добавить товар" class="admin-btn">Добавить товар</button>
                </form>
            </div>

            <div class="admin-section">
                <h2>Пользователи</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Аватар</th>
                            <th>Логин</th>
                            <th>Фамилия</th>
                            <th>Имя</th>
                            <th>Отчество</th>
                            <th>Почта</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM `reg`";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td>
                                <img src="img/<?php echo xss_clean($row['img'] ?? 'default_avatar.jpg'); ?>" width="50" height="50" class="admin-avatar">
                            </td>
                            <td><?php echo xss_clean($row['login']); ?></td>
                            <td><?php echo xss_clean($row['family']); ?></td>
                            <td><?php echo xss_clean($row['name']); ?></td>
                            <td><?php echo xss_clean($row['about_name']); ?></td>
                            <td><?php echo xss_clean($row['mail']); ?></td>
                            <td>
                                <?php 
                                if ($row['status'] == 1) {
                                    echo '<span class="status-admin">Админ</span>';
                                } else {
                                    echo '<span class="status-user">Пользователь</span>';
                                }
                                ?>
                            </td>
                            <td class="action-buttons">
                                <form action="add.php" method="post" class="inline-form">
                                    <input type="hidden" name="id" value="<?php echo xss_clean($row['id']); ?>">
                                    <button type="submit" name="red_pol" value="Редактировать" class="btn-edit">Редактировать</button>
                                </form>
                                <form action="add.php" method="post" class="inline-form" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                    <input type="hidden" name="id" value="<?php echo xss_clean($row['id']); ?>">
                                    <button type="submit" name="del_pol" value="Удалить" class="btn-delete">Удалить</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="8" class="no-data">Нет пользователей</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-section">
                <h2>Категории</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Наименование</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM `category`";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo xss_clean($row['id']); ?></td>
                            <td><?php echo xss_clean($row['name']); ?></td>
                            <td class="action-buttons">
                                <form action="add.php" method="post" class="inline-form">
                                    <input type="hidden" name="id" value="<?php echo xss_clean($row['id']); ?>">
                                    <button type="submit" name="red_cat" value="Редактировать" class="btn-edit">Редактировать</button>
                                </form>
                                <form action="add.php" method="post" class="inline-form" onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию?');">
                                    <input type="hidden" name="id" value="<?php echo xss_clean($row['id']); ?>">
                                    <button type="submit" name="del_cat" value="Удалить" class="btn-delete">Удалить</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="3" class="no-data">Нет категорий</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-section">
                <h2>Товары</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Изображение</th>
                            <th>Наименование</th>
                            <th>Цена</th>
                            <th>Категория</th>
                            <th>Описание 1</th>
                            <th>Описание 2</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM `product`";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td>
                                <img src="img/<?php echo xss_clean($row['img'] ?? 'default_product.jpg'); ?>" width="50" height="50" class="admin-product-img">
                            </td>
                            <td><?php echo xss_clean($row['name']); ?></td>
                            <td><?php echo xss_clean($row['price']); ?> руб.</td>
                            <td><?php echo xss_clean($row['category']); ?></td>
                            <td><?php echo xss_clean(substr($row['description1'], 0, 50)) . '...'; ?></td>
                            <td><?php echo xss_clean(substr($row['description2'], 0, 50)) . '...'; ?></td>
                            <td class="action-buttons">
                                <form action="add.php" method="post" class="inline-form">
                                    <input type="hidden" name="id" value="<?php echo xss_clean($row['id']); ?>">
                                    <button type="submit" name="red_tov" value="Редактировать" class="btn-edit">Редактировать</button>
                                </form>
                                <form action="add.php" method="post" class="inline-form" onsubmit="return confirm('Вы уверены, что хотите удалить этот товар?');">
                                    <input type="hidden" name="id" value="<?php echo xss_clean($row['id']); ?>">
                                    <button type="submit" name="del_tov" value="Удалить" class="btn-delete">Удалить</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="no-data">Нет товаров</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Фитнес-клуб Сталь, Якутск. Все права защищены.</p>
    </footer>
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
<?php
mysqli_close($conn);
?>
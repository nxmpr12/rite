<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
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

if (isset($_POST['out']) && $_POST['out'] == 'Выход') {
    $_SESSION['login'] = '';
    $_SESSION['status'] = '';
    header('Location: index.php');
    exit;
}

if (isset($_POST['del_pol']) && $_POST['del_pol'] == 'Удалить') {
    $id = (int)$_POST['id'];
    $query = "DELETE FROM `reg` WHERE `id`=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Пользователь удален!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Ошибка при удалении пользователя!'); window.location.href='admin.php';</script>";
    }
    mysqli_stmt_close($stmt);
    exit;
}

if (isset($_POST['del_cat']) && $_POST['del_cat'] == 'Удалить') {
    $id = (int)$_POST['id'];
    $query = "DELETE FROM `category` WHERE `id`=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Категория удалена!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Ошибка при удалении категории!'); window.location.href='admin.php';</script>";
    }
    mysqli_stmt_close($stmt);
    exit;
}

if (isset($_POST['del_tov']) && $_POST['del_tov'] == 'Удалить') {
    $id = (int)$_POST['id'];
    $query = "DELETE FROM `product` WHERE `id`=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Товар удален!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Ошибка при удалении товара!'); window.location.href='admin.php';</script>";
    }
    mysqli_stmt_close($stmt);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Добавление - Фитнес-клуб Сталь</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header>
        <div class="header-buttons">
            <a href="index.php">Главная</a>
            <a href="admin.php">Админ-панель</a>
            <?php if (!empty($_SESSION['login'])): ?>
                <form action="add.php" method="post" class="logout-form">
                    <input type="submit" name="out" value="Выход" class="logout-button">
                </form>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="admin-container">
            <?php
            if (isset($_POST['add_pol']) || isset($_POST['add_pol2'])) {
                if (isset($_POST['add_pol2']) && $_POST['add_pol2'] == 'Добавить пользователя') {
                    $login = mysqli_real_escape_string($conn, $_POST['login']);
                    $mail = mysqli_real_escape_string($conn, $_POST['mail']);
                    $pass1 = mysqli_real_escape_string($conn, $_POST['pass1']);
                    $pass2 = mysqli_real_escape_string($conn, $_POST['pass2']);
                    $family = mysqli_real_escape_string($conn, $_POST['surname']);
                    $name = mysqli_real_escape_string($conn, $_POST['name']);
                    $about_name = mysqli_real_escape_string($conn, $_POST['patronymic']);
                    $status = (int)$_POST['status'];
                    
                    if ($pass1 == $pass2) {
                        $query = "INSERT INTO `reg` (`login`, `mail`, `pass`, `family`, `name`, `about_name`, `status`) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "ssssssi", $login, $mail, $pass1, $family, $name, $about_name, $status);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            echo "<div class='success-message'>Пользователь добавлен! <a href='admin.php'>Вернуться в админ-панель</a></div>";
                        } else {
                            echo "<div class='error-message'>Ошибка при добавлении пользователя: " . mysqli_error($conn) . "</div>";
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        echo "<div class='error-message'>Пароли не совпадают!</div>";
                    }
                }
                ?>
                <h2>Добавить пользователя</h2>
                <form action='add.php' method='post' class="admin-form">
                    <div class="form-group">
                        <label>Логин *</label>
                        <input type='text' name='login' value="<?php echo isset($_POST['login']) ? xss_clean($_POST['login']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type='email' name='mail' value="<?php echo isset($_POST['mail']) ? xss_clean($_POST['mail']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль *</label>
                        <input type='password' name='pass1' required>
                    </div>
                    <div class="form-group">
                        <label>Повторите пароль *</label>
                        <input type='password' name='pass2' required>
                    </div>
                    <div class="form-group">
                        <label>Фамилия *</label>
                        <input type='text' name='surname' value="<?php echo isset($_POST['surname']) ? xss_clean($_POST['surname']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Имя *</label>
                        <input type='text' name='name' value="<?php echo isset($_POST['name']) ? xss_clean($_POST['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Отчество *</label>
                        <input type='text' name='patronymic' value="<?php echo isset($_POST['patronymic']) ? xss_clean($_POST['patronymic']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Статус</label>
                        <select name="status">
                            <option value="0">Пользователь</option>
                            <option value="1">Администратор</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type='submit' name='add_pol2' value='Добавить пользователя' class="admin-btn">Добавить пользователя</button>
                        <a href="admin.php" class="cancel-btn">Отмена</a>
                    </div>
                </form>
                <?php
            }

            elseif (isset($_POST['add_cat']) || isset($_POST['add_cat2'])) {
                if (isset($_POST['add_cat2']) && $_POST['add_cat2'] == 'Добавить категорию') {
                    $name = mysqli_real_escape_string($conn, $_POST['name']);
                    
                    $query = "INSERT INTO `category`(`name`) VALUES (?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "s", $name);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        echo "<div class='success-message'>Категория добавлена! <a href='admin.php'>Вернуться в админ-панель</a></div>";
                    } else {
                        echo "<div class='error-message'>Ошибка при добавлении категории: " . mysqli_error($conn) . "</div>";
                    }
                    mysqli_stmt_close($stmt);
                }
                ?>
                <h2>Добавить категорию</h2>
                <form action='add.php' method='post' class="admin-form">
                    <div class="form-group">
                        <label>Название категории *</label>
                        <input type='text' name='name' value="<?php echo isset($_POST['name']) ? xss_clean($_POST['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <button type='submit' name='add_cat2' value='Добавить категорию' class="admin-btn">Добавить категорию</button>
                        <a href="admin.php" class="cancel-btn">Отмена</a>
                    </div>
                </form>
                <?php
            }

            elseif (isset($_POST['add_tov']) || isset($_POST['add_tov2'])) {
                if (isset($_POST['add_tov2']) && $_POST['add_tov2'] == 'Добавить товар') {
                    $name = mysqli_real_escape_string($conn, $_POST['name']);
                    $price = (float)$_POST['price'];
                    $category = mysqli_real_escape_string($conn, $_POST['category']);
                    $description1 = mysqli_real_escape_string($conn, $_POST['description1']);
                    $description2 = mysqli_real_escape_string($conn, $_POST['description2']);
                    $img = 'default_product.jpg';
                    
                    $query = "INSERT INTO `product` (`name`, `img`, `price`, `category`, `description1`, `description2`)
                              VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssdsss", $name, $img, $price, $category, $description1, $description2);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        echo "<div class='success-message'>Товар добавлен! <a href='admin.php'>Вернуться в админ-панель</a></div>";
                    } else {
                        echo "<div class='error-message'>Ошибка при добавлении товара: " . mysqli_error($conn) . "</div>";
                    }
                    mysqli_stmt_close($stmt);
                }
                ?>
                <h2>Добавить товар</h2>
                <form action='add.php' method='post' class="admin-form">
                    <div class="form-group">
                        <label>Название товара *</label>
                        <input type='text' name='name' value="<?php echo isset($_POST['name']) ? xss_clean($_POST['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Цена *</label>
                        <input type='number' step='0.01' name='price' value="<?php echo isset($_POST['price']) ? xss_clean($_POST['price']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Категория *</label>
                        <select name='category' required>
                            <option value="">Выберите категорию</option>
                            <?php
                            $query1 = "SELECT * FROM `category`";
                            $result1 = mysqli_query($conn, $query1);
                            while ($row2 = mysqli_fetch_assoc($result1)) {
                                $selected = (isset($_POST['category']) && $_POST['category'] == $row2['name']) ? 'selected' : '';
                                echo "<option value='" . xss_clean($row2['name']) . "' $selected>" . xss_clean($row2['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Описание 1 *</label>
                        <textarea name='description1' required><?php echo isset($_POST['description1']) ? xss_clean($_POST['description1']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Описание 2</label>
                        <textarea name='description2'><?php echo isset($_POST['description2']) ? xss_clean($_POST['description2']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <button type='submit' name='add_tov2' value='Добавить товар' class="admin-btn">Добавить товар</button>
                        <a href="admin.php" class="cancel-btn">Отмена</a>
                    </div>
                </form>
                <?php
            }

            elseif (isset($_POST['red_pol']) || isset($_POST['red_pol2'])) {
                $id = (int)$_POST['id'];
                
                if (isset($_POST['red_pol2']) && $_POST['red_pol2'] == 'Сохранить изменения') {
                    $login = mysqli_real_escape_string($conn, $_POST['login']);
                    $mail = mysqli_real_escape_string($conn, $_POST['mail']);
                    $pass1 = mysqli_real_escape_string($conn, $_POST['pass1']);
                    $family = mysqli_real_escape_string($conn, $_POST['surname']);
                    $name = mysqli_real_escape_string($conn, $_POST['name']);
                    $about_name = mysqli_real_escape_string($conn, $_POST['patronymic']);
                    $status = (int)$_POST['status'];
                    
                    $query = "UPDATE `reg` SET `login`=?, `mail`=?, `pass`=?, `family`=?, `name`=?, `about_name`=?, `status`=? WHERE `id`=?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssssssii", $login, $mail, $pass1, $family, $name, $about_name, $status, $id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        echo "<div class='success-message'>Изменения сохранены! <a href='admin.php'>Вернуться в админ-панель</a></div>";
                    } else {
                        echo "<div class='error-message'>Ошибка при сохранении изменений: " . mysqli_error($conn) . "</div>";
                    }
                    mysqli_stmt_close($stmt);
                }
                
                $query = "SELECT * FROM `reg` WHERE `id`=?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                if ($user) {
                ?>
                <h2>Редактировать пользователя</h2>
                <form action='add.php' method='post' class="admin-form">
                    <input type="hidden" name="id" value="<?php echo xss_clean($user['id']); ?>">
                    <div class="form-group">
                        <label>Логин *</label>
                        <input type='text' name='login' value="<?php echo xss_clean($user['login']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type='email' name='mail' value="<?php echo xss_clean($user['mail']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль *</label>
                        <input type='text' name='pass1' value="<?php echo xss_clean($user['pass']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Фамилия *</label>
                        <input type='text' name='surname' value="<?php echo xss_clean($user['family']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Имя *</label>
                        <input type='text' name='name' value="<?php echo xss_clean($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Отчество *</label>
                        <input type='text' name='patronymic' value="<?php echo xss_clean($user['about_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Статус</label>
                        <select name="status">
                            <option value="0" <?php echo $user['status'] == 0 ? 'selected' : ''; ?>>Пользователь</option>
                            <option value="1" <?php echo $user['status'] == 1 ? 'selected' : ''; ?>>Администратор</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type='submit' name='red_pol2' value='Сохранить изменения' class="admin-btn">Сохранить изменения</button>
                        <a href="admin.php" class="cancel-btn">Отмена</a>
                    </div>
                </form>
                <?php
                }
            }

            elseif (isset($_POST['red_cat']) || isset($_POST['red_cat2'])) {
                $id = (int)$_POST['id'];
                
                if (isset($_POST['red_cat2']) && $_POST['red_cat2'] == 'Сохранить изменения') {
                    $name = mysqli_real_escape_string($conn, $_POST['name']);
                    
                    $query = "UPDATE `category` SET `name`=? WHERE `id`=?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "si", $name, $id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        echo "<div class='success-message'>Изменения сохранены! <a href='admin.php'>Вернуться в админ-панель</a></div>";
                    } else {
                        echo "<div class='error-message'>Ошибка при сохранении изменений: " . mysqli_error($conn) . "</div>";
                    }
                    mysqli_stmt_close($stmt);
                }
                
                $query = "SELECT * FROM `category` WHERE `id`=?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $category = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                if ($category) {
                ?>
                <h2>Редактировать категорию</h2>
                <form action='add.php' method='post' class="admin-form">
                    <input type="hidden" name="id" value="<?php echo xss_clean($category['id']); ?>">
                    <div class="form-group">
                        <label>Название категории *</label>
                        <input type='text' name='name' value="<?php echo xss_clean($category['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <button type='submit' name='red_cat2' value='Сохранить изменения' class="admin-btn">Сохранить изменения</button>
                        <a href="admin.php" class="cancel-btn">Отмена</a>
                    </div>
                </form>
                <?php
                }
            }

            elseif (isset($_POST['red_tov']) || isset($_POST['red_tov2'])) {
                $id = (int)$_POST['id'];
                
                if (isset($_POST['red_tov2']) && $_POST['red_tov2'] == 'Сохранить изменения') {
                    $name = mysqli_real_escape_string($conn, $_POST['name']);
                    $price = (float)$_POST['price'];
                    $category = mysqli_real_escape_string($conn, $_POST['category']);
                    $description1 = mysqli_real_escape_string($conn, $_POST['description1']);
                    $description2 = mysqli_real_escape_string($conn, $_POST['description2']);
                    
                    $query = "UPDATE `product` SET `name`=?, `price`=?, `category`=?, `description1`=?, `description2`=? WHERE `id`=?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sdsssi", $name, $price, $category, $description1, $description2, $id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        echo "<div class='success-message'>Изменения сохранены! <a href='admin.php'>Вернуться в админ-панель</a></div>";
                    } else {
                        echo "<div class='error-message'>Ошибка при сохранении изменений: " . mysqli_error($conn) . "</div>";
                    }
                    mysqli_stmt_close($stmt);
                }
                
                $query = "SELECT * FROM `product` WHERE `id`=?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $product = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                if ($product) {
                ?>
                <h2>Редактировать товар</h2>
                <form action='add.php' method='post' class="admin-form">
                    <input type="hidden" name="id" value="<?php echo xss_clean($product['id']); ?>">
                    <div class="form-group">
                        <label>Название товара *</label>
                        <input type='text' name='name' value="<?php echo xss_clean($product['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Цена *</label>
                        <input type='number' step='0.01' name='price' value="<?php echo xss_clean($product['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Категория *</label>
                        <select name='category' required>
                            <option value="">Выберите категорию</option>
                            <?php
                            $query1 = "SELECT * FROM `category`";
                            $result1 = mysqli_query($conn, $query1);
                            while ($row2 = mysqli_fetch_assoc($result1)) {
                                $selected = ($product['category'] == $row2['name']) ? 'selected' : '';
                                echo "<option value='" . xss_clean($row2['name']) . "' $selected>" . xss_clean($row2['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Описание 1 *</label>
                        <textarea name='description1' required><?php echo xss_clean($product['description1']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Описание 2</label>
                        <textarea name='description2'><?php echo xss_clean($product['description2']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <button type='submit' name='red_tov2' value='Сохранить изменения' class="admin-btn">Сохранить изменения</button>
                        <a href="admin.php" class="cancel-btn">Отмена</a>
                    </div>
                </form>
                <?php
                }
            }

            else {
                echo "<div class='error-message'>Неизвестное действие. <a href='admin.php'>Вернуться в админ-панель</a></div>";
            }
            ?>
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
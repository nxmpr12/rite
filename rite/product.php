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
    if (is_array($data)) {
        return array_map('xss_clean', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function validate_id($id) {
    return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

if(!isset($_GET['id']) || !validate_id($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int)$_GET['id'];

$query = "SELECT * FROM product WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);

if(!$product) {
    header("Location: index.php");
    exit;
}

if(isset($_POST['add_comment']) && !empty($_SESSION['login'])) {
    $comment = trim($_POST['comment'] ?? '');
    
    if(!empty($comment)) {
        $clean_comment = xss_clean($comment);
        
        $user_query = "SELECT id FROM reg WHERE login = ?";
        $user_stmt = mysqli_prepare($conn, $user_query);
        $clean_login = mysqli_real_escape_string($conn, $_SESSION['login']);
        mysqli_stmt_bind_param($user_stmt, "s", $clean_login);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user = mysqli_fetch_array($user_result);
        mysqli_stmt_close($user_stmt);
        
        if($user) {
            $insert_query = "INSERT INTO comments (product_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "iis", $product_id, $user['id'], $clean_comment);
            mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);
            
            header("Location: product.php?id=" . $product_id);
            exit;
        }
    }
}

$comments_query = "
    SELECT c.*, r.login 
    FROM comments c 
    JOIN reg r ON c.user_id = r.id 
    WHERE c.product_id = ? 
    ORDER BY c.created_at DESC
";
$comments_stmt = mysqli_prepare($conn, $comments_query);
mysqli_stmt_bind_param($comments_stmt, "i", $product_id);
mysqli_stmt_execute($comments_stmt);
$comments_result = mysqli_stmt_get_result($comments_stmt);
$comments = [];
while($row = mysqli_fetch_array($comments_result)) {
    $comments[] = $row;
}
mysqli_stmt_close($comments_stmt);

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
    <title><?php echo xss_clean($product['name']); ?> - Cyber Fitness</title>
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
    <header class="product-header">
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
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i>Профиль
                </a>
                <?php if (!empty($_SESSION['login'])): ?>
                    <?php if ($_SESSION['status'] == 1): ?>
                        <a href="admin.php" class="nav-link admin-panel">
                            <i class="fas fa-cog"></i>Админ-панель
                        </a>
                    <?php endif; ?>
                    <form action="product.php" method="post" class="logout-form">
                        <input type="hidden" name="id" value="<?php echo xss_clean($product_id); ?>">
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
            <div class="product-detail">
                <!-- Product Info -->
                <div class="cyber-product-detail">
                    <div class="product-image-section">
                        <img src="img/<?php echo xss_clean($product['img'] ?? 'default_product.jpg'); ?>" 
                             alt="<?php echo xss_clean($product['name']); ?>" 
                             class="detail-product-image">
                    </div>
                    <div class="product-info-section">
                        <h1 class="product-detail-title"><?php echo xss_clean($product['name']); ?></h1>
                        <div class="product-meta">
                            <span class="product-category-detail">
                                <i class="fas fa-tag"></i>
                                <?php echo xss_clean($product['category']); ?>
                            </span>
                            <span class="product-price-detail">
                                <?php echo number_format($product['price'], 0, ',', ' '); ?> ₽
                            </span>
                        </div>
                        <div class="product-descriptions">
                            <div class="description-block">
                                <h3><i class="fas fa-info-circle"></i> ОПИСАНИЕ</h3>
                                <p><?php echo xss_clean($product['description1']); ?></p>
                            </div>
                            <?php if (!empty($product['description2'])): ?>
                            <div class="description-block">
                                <h3><i class="fas fa-list-alt"></i> ХАРАКТЕРИСТИКИ</h3>
                                <p><?php echo xss_clean($product['description2']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="comments-section">
                    <h2 class="section-title">
                        <i class="fas fa-comments"></i>
                        КОММЕНТАРИИ
                    </h2>
                    
                    <?php if(!empty($_SESSION['login'])): ?>
                        <div class="comment-form-container">
                            <form method="POST" class="cyber-form comment-form">
                                <input type="hidden" name="id" value="<?php echo xss_clean($product_id); ?>">
                                <div class="form-group">
                                    <textarea name="comment" placeholder="ОСТАВЬТЕ ВАШ КОММЕНТАРИЙ..." 
                                              required maxlength="1000" class="cyber-input comment-textarea"><?php echo isset($_POST['comment']) ? xss_clean($_POST['comment']) : ''; ?></textarea>
                                </div>
                                <button type="submit" name="add_comment" class="cyber-button comment-submit-btn">
                                    <i class="fas fa-paper-plane"></i>ОТПРАВИТЬ КОММЕНТАРИЙ
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="login-prompt">
                            <p>ПОЖАЛУЙСТА, <a href="auto.php" class="cyber-link">ВОЙДИТЕ В СИСТЕМУ</a>, ЧТОБЫ ОСТАВИТЬ КОММЕНТАРИЙ.</p>
                        </div>
                    <?php endif; ?>

                    <div class="comments-list">
                        <?php if(empty($comments)): ?>
                            <div class="no-comments">
                                <i class="fas fa-comment-slash cyber-icon"></i>
                                <h3>КОММЕНТАРИЕВ ПОКА НЕТ</h3>
                                <p>Будьте первым, кто оставит комментарий!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($comments as $comment): ?>
                                <div class="comment-item cyber-card">
                                    <div class="comment-header">
                                        <div class="comment-author">
                                            <strong><?php echo xss_clean($comment['login']); ?></strong>
                                        </div>
                                        <div class="comment-date">
                                            <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="comment-text">
                                        <?php echo xss_clean($comment['comment_text']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
        // Fast loader
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loader = document.querySelector('.loader');
                if (loader) loader.classList.add('hidden');
            }, 500); // Faster loading
        });

        // Add particles
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.createElement('div');
            particlesContainer.className = 'particles-container';
            document.body.appendChild(particlesContainer);

            for (let i = 0; i < 15; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.animationDelay = `${Math.random() * 15}s`;
                particle.style.background = i % 3 === 0 ? 'var(--primary)' : 
                                         i % 3 === 1 ? 'var(--secondary)' : 'var(--accent)';
                particlesContainer.appendChild(particle);
            }
        });
    </script>

    <style>
        .product-detail {
            padding: 40px 0;
        }

        .cyber-product-detail {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 40px;
            background: rgba(26, 26, 26, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 0, 128, 0.3);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
        }

        .product-image-section {
            text-align: center;
        }

        .detail-product-image {
            width: 100%;
            max-width: 350px;
            height: 350px;
            object-fit: cover;
            border-radius: 15px;
            border: 3px solid var(--primary);
            box-shadow: var(--neon-glow);
            transition: all 0.3s ease;
        }

        .detail-product-image:hover {
            transform: scale(1.02);
            border-color: var(--secondary);
        }

        .product-detail-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--text);
            margin-bottom: 20px;
            font-family: 'Orbitron', monospace;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: var(--cyber-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .product-meta {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .product-category-detail {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 0, 128, 0.1);
            color: var(--primary);
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            border: 1px solid rgba(255, 0, 128, 0.3);
        }

        .product-price-detail {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--secondary);
            text-shadow: var(--neon-glow-secondary);
            font-family: 'Orbitron', monospace;
        }

        .product-descriptions {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .description-block h3 {
            color: var(--secondary);
            font-family: 'Orbitron', monospace;
            font-size: 1.2rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .description-block p {
            color: var(--text-dim);
            line-height: 1.6;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.1rem;
        }

        .comments-section {
            background: rgba(26, 26, 26, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 0, 128, 0.3);
            border-radius: 15px;
            padding: 30px;
        }

        .section-title {
            color: var(--primary);
            font-family: 'Orbitron', monospace;
            font-size: 1.8rem;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .comment-form-container {
            margin-bottom: 30px;
        }

        .comment-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .comment-submit-btn {
            width: 100%;
            justify-content: center;
        }

        .login-prompt {
            text-align: center;
            padding: 30px;
            background: rgba(255, 0, 128, 0.1);
            border: 1px solid rgba(255, 0, 128, 0.3);
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .cyber-link {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 700;
        }

        .cyber-link:hover {
            text-decoration: underline;
        }

        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .comment-item {
            padding: 20px;
            border: 1px solid rgba(0, 255, 255, 0.2);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .comment-author strong {
            color: var(--primary);
            font-family: 'Orbitron', monospace;
        }

        .comment-date {
            color: var(--text-dim);
            font-size: 0.9rem;
            font-family: 'Rajdhani', sans-serif;
        }

        .comment-text {
            color: var(--text);
            line-height: 1.6;
            font-family: 'Rajdhani', sans-serif;
        }

        .no-comments {
            text-align: center;
            padding: 40px;
        }

        @media (max-width: 968px) {
            .cyber-product-detail {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .product-meta {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .product-detail-title {
                font-size: 2rem;
            }
            
            .product-price-detail {
                font-size: 1.8rem;
            }
            
            .section-title {
                font-size: 1.5rem;
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
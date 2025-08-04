<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Проверка языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/' . $selected_lang . '_index.php';

// --- Куки ---
$cookie_lifetime = time() + 60 * 60 * 24 * 7;
if (isset($_GET['t'])) setcookie('token', $_GET['t'], $cookie_lifetime, "/", ".mindguide.online");
if (isset($_GET['uid'])) setcookie('partner_id', $_GET['uid'], $cookie_lifetime, "/", ".mindguide.online");
if (isset($_GET['click_id'])) setcookie('click_id', $_GET['click_id'], $cookie_lifetime, "/", ".mindguide.online");
if (isset($_GET['utm_source'])) setcookie('utm_source', $_GET['utm_source'], $cookie_lifetime, "/", ".mindguide.online");
if (isset($_GET['utm_medium'])) setcookie('utm_medium', $_GET['utm_medium'], $cookie_lifetime, "/", ".mindguide.online");
if (isset($_GET['utm_campaign'])) setcookie('utm_campaign', $_GET['utm_campaign'], $cookie_lifetime, "/", ".mindguide.online");
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_['index_title']; ?></title>
    <meta name="description" content="<?php echo $_['index_description']; ?>" />
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/main-index.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="script.js"></script>
    <link href="images/icon.png" rel="icon" />
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; ?>
<div class="container">

<div class="block-main">
  <div class="text-section">
    <h1><?php echo $_['index_h1']; ?></h1>
    <p><?php echo $_['index_intro']; ?></p>
    <button class="cta-button-main" id="cta-button"><?php echo $_['index_btn_try']; ?></button>
  </div>
  <div class="image-section"></div>
</div>

<div class="benefits-section">
    <h2><?php echo $_['index_h2_benefits']; ?></h2>
    <div class="features-wrapper">
      <button type="button" class="arrow prev" aria-label="<?php echo $_['index_btn_prev']; ?>">‹</button>
      <div class="features">
        <div class="feature">
          <div class="icon"><i data-lucide="brain"></i></div>
          <h3><?php echo $_['index_feature1_title']; ?></h3>
          <p><?php echo $_['index_feature1_text']; ?></p>
        </div>
        <div class="feature">
          <div class="icon"><i data-lucide="book"></i></div>
          <h3><?php echo $_['index_feature2_title']; ?></h3>
          <p><?php echo $_['index_feature2_text']; ?></p>
        </div>
        <div class="feature">
          <div class="icon"><i data-lucide="eye"></i></div>
          <h3><?php echo $_['index_feature3_title']; ?></h3>
          <p><?php echo $_['index_feature3_text']; ?></p>
        </div>
        <div class="feature">
          <div class="icon"><i data-lucide="refresh-cw"></i></div>
          <h3><?php echo $_['index_feature4_title']; ?></h3>
          <p><?php echo $_['index_feature4_text']; ?></p>
        </div>
      </div>
      <button type="button" class="arrow next" aria-label="<?php echo $_['index_btn_next']; ?>">›</button>
    </div>
</div>

<div class="block-why">
    <h2><?php echo $_['index_h2_why']; ?></h2>
    <div class="block odd">
        <div class="left"><img src="images/1.jpg" alt="<?php echo $_['index_block1_title']; ?>"></div>
        <div class="right"><span><?php echo $_['index_block1_title']; ?></span><br>
            <?php echo $_['index_block1_text']; ?><br>
            <button class="cta-button" id="cta-button"><?php echo $_['index_block1_btn']; ?></button></div>
    </div>
    <div class="block even">
        <div class="left"><span><?php echo $_['index_block2_title']; ?></span><br>
            <?php echo $_['index_block2_text']; ?><br>
            <button class="cta-button" id="cta-button"><?php echo $_['index_block2_btn']; ?></button></div>
        <div class="right"><img src="images/2.jpg" alt="<?php echo $_['index_block2_title']; ?>"></div>
    </div>
    <div class="block odd">
        <div class="left"><img src="images/3.jpg" alt="<?php echo $_['index_block3_title']; ?>"></div>
        <div class="right"><span><?php echo $_['index_block3_title']; ?></span><br>
            <?php echo $_['index_block3_text']; ?><br>
            <button class="cta-button" id="cta-button"><?php echo $_['index_block3_btn']; ?></button></div>
    </div>
    <div class="block even">
        <div class="left"><span><?php echo $_['index_block4_title']; ?></span><br>
            <?php echo $_['index_block4_text']; ?><br>
            <button class="cta-button" id="cta-button"><?php echo $_['index_block4_btn']; ?></button></div>
        <div class="right"><img src="images/4.jpg" alt="<?php echo $_['index_block4_title']; ?>"></div>
    </div>
    <div class="block odd">
        <div class="left"><img src="images/5.jpg" alt="<?php echo $_['index_block5_title']; ?>"></div>
        <div class="right"><span><?php echo $_['index_block5_title']; ?></span><br>
            <?php echo $_['index_block5_text']; ?><br>
            <button class="cta-button" id="cta-button"><?php echo $_['index_block5_btn']; ?></button></div>
    </div>
    <div class="block even">
        <div class="left"><span><?php echo $_['index_block6_title']; ?></span><br>
            <?php echo $_['index_block6_text']; ?><br>
            <button class="cta-button" id="cta-button"><?php echo $_['index_block6_btn']; ?></button></div>
        <div class="right"><img src="images/6.jpg" alt="<?php echo $_['index_block6_title']; ?>"></div>
    </div>
    <div class="block odd">
        <div class="left"><img src="images/7.jpg" alt="<?php echo $_['index_block7_title']; ?>"></div>
        <div class="right"><span><?php echo $_['index_block7_title']; ?></span><br>
            <?php echo $_['index_block7_text']; ?><br>
            <button class="cta-button" id="cta-button"><?php echo $_['index_block7_btn']; ?></button></div>
    </div>
    <div class="block even">
        <div class="left"><span><?php echo $_['index_block8_title']; ?></span><br>
            <?php echo $_['index_block8_text']; ?><br>
            <button class="cta-button" id="cta-button"><?php echo $_['index_block8_btn']; ?></button></div>
        <div class="right"><img src="images/8.jpg" alt="<?php echo $_['index_block8_title']; ?>"></div>
    </div>
</div>

<div class="reviews-section">
    <h2><?php echo $_['index_reviews_title']; ?></h2>
    <div class="swiper mySwiper">
        <div class="swiper-wrapper"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</div>

<script>
// Перенаправление на signup.php при нажатии на кнопки
document.addEventListener("DOMContentLoaded", function() {
    let buttons = document.querySelectorAll("#cta-button");
    let url = "./signup.php";
    buttons.forEach(button => {
        button.addEventListener("click", function() {
            window.location.href = url;
        });
    });
});

// Блок "Почему выбирают нас?"
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    const container = document.querySelector('.features');
    const prevBtn   = document.querySelector('.arrow.prev');
    const nextBtn   = document.querySelector('.arrow.next');
    function getStep() {
        const slide = container.querySelector('.feature');
        const style = getComputedStyle(slide);
        const w     = slide.getBoundingClientRect().width;
        const mr    = parseFloat(style.marginRight);
        return w + mr;
    }
    prevBtn.addEventListener('click', () => {
        const step = getStep();
        if (container.scrollLeft <= 0) {
            container.scrollTo({ left: container.scrollWidth, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: -step, behavior: 'smooth' });
        }
    });
    nextBtn.addEventListener('click', () => {
        const step      = getStep();
        const maxScroll = container.scrollWidth - container.clientWidth;
        if (container.scrollLeft >= maxScroll) {
            container.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: step, behavior: 'smooth' });
        }
    });
});

// Отзывы из языковых переменных (можете увеличить список до нужного кол-ва)
const reviews = [
            { name: "Ольга Кравченко", photo: "https://randomuser.me/api/portraits/women/9.jpg", rating: 5, text: "MindGuide — справжня знахідка! Зручний дизайн і чіткі інструкції допомагають мені зберігати баланс між роботою та відпочинком." },
            { name: "Марія Бондар", photo: "https://randomuser.me/api/portraits/women/10.jpg", rating: 5, text: "Дякую MindGuide за ШІ психолога. Вже за тиждень помітила, як знизився рівень стресу!" },
            { name: "Анастасія Коцюбинська", photo: "https://randomuser.me/api/portraits/women/11.jpg", rating: 4, text: "MindGuide надихає на нові цілі: простий інтерфейс і мотивувальні статті допомагають рухатися вперед." },
            { name: "Катерина Петренко", photo: "https://randomuser.me/api/portraits/women/14.jpg", rating: 4, text: "Корисний контент про саморозвиток і особисту ефективність. Рекомендую всім, хто прагне зростання!" },
            { name: "Євгенія Дорошенко", photo: "https://randomuser.me/api/portraits/women/19.jpg", rating: 5, text: "Люблю їхні статті про емоційний інтелект. Корисні поради можна відразу застосувати в житті." },
            { name: "Валерія Литвин", photo: "https://randomuser.me/api/portraits/women/20.jpg", rating: 4, text: "Простий і водночас інформативний сайт. Дизайн надихає до дії!" },
            { name: "Аліна Зайцева", photo: "https://randomuser.me/api/portraits/women/21.jpg", rating: 4, text: "Зручно, що всі матеріали доступні онлайн у будь-який час. Ідеально для зайнятих людей." },
            { name: "Вероніка Кравчук", photo: "https://randomuser.me/api/portraits/women/26.jpg", rating: 5, text: "MindGuide — мій топовий помічник! Особливо ІІ-психолог: завжди підкаже, як впоратися з тривогою, навіть серед ночі" },
            { name: "Олена Ткаченко", photo: "https://randomuser.me/api/portraits/women/31.jpg", rating: 5, text: "Як психолог, можу сказати — тут дуже професійні матеріали. Раджу всім!" },
            { name: "Світлана Іванченко", photo: "https://randomuser.me/api/portraits/women/40.jpg", rating: 5, text: "ШІ-психолог — як найкращий друг, який завжди в курсі сучасних методів психотерапії. Дуже корисний інструмент!." }
];

function createStars(rating) {
    return "★".repeat(rating) + "☆".repeat(5 - rating);
}

function renderReviews() {
    const wrapper = document.querySelector(".swiper-wrapper");
    reviews.forEach(({ name, photo, rating, text }) => {
        const slide = document.createElement("div");
        slide.classList.add("swiper-slide");
        slide.innerHTML = `
            <img src="${photo}" alt="${name}" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px;">
            <div style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">${name}</div>
            <div style="color: #FFD700; font-size: 18px; margin-bottom: 10px;">${createStars(rating)}</div>
            <div style="font-size: 14px; color: #555;">${text}</div>
        `;
        wrapper.appendChild(slide);
    });
}
renderReviews();

new Swiper(".mySwiper", {
    slidesPerView: 3,
    loop: true,
    spaceBetween: 50,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    autoplay: {
        delay: 10000,
        disableOnInteraction: false,
    },
    centerInsufficientSlides: true,
    breakpoints: {
        0: { slidesPerView: 1, spaceBetween: 10 },
        768: { slidesPerView: 2, spaceBetween: 15 },
        1024: { slidesPerView: 3, spaceBetween: 50 }
    }
});
</script>

<?php
// --- ВЫВОД COOKIE для отладки (убрать на бою!) ---
$token = $_COOKIE['token'] ?? null;
$partner_id = $_COOKIE['partner_id'] ?? null;
$click_id = $_COOKIE['click_id'] ?? null;
$utm_source = $_COOKIE['utm_source'] ?? null;
$utm_medium = $_COOKIE['utm_medium'] ?? null;
$utm_campaign = $_COOKIE['utm_campaign'] ?? null;

echo "Токен: " . $token;
echo "<br>ID партнера: " . $partner_id;
echo "<br>Click ID: " . $click_id;
echo "<br>Откуда: " . $utm_source;
echo "<br>Тип рекламы: " . $utm_medium;
echo "<br>Доп параметр: " . $utm_campaign;
?>
<br>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>
</div>
</body>
</html>

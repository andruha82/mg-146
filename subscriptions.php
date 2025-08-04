<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_['subscrtitle']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/subscriptions.css">
    <script src="script.js"></script>
    <link href="images/icon.png" rel="icon" />
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; ?>
<div class="container">

    <div class="section">
        <h1 class="title"><?php echo $_['subscr_choose_plan']; ?></h1>
        <p class="subtitle">
            <?php echo $_['subscr_get_access']; ?><br>
            <strong><?php echo $_['subscr_cheaper_than_coffee']; ?></strong>
        </p>

        <div class="pricing-toggle">
            <input type="radio" id="toggle-monthly" name="pricing" checked>
            <input type="radio" id="toggle-annual" name="pricing">
            <label for="toggle-monthly"><?php echo $_['subscr_monthly']; ?></label>
            <label for="toggle-annual"><?php echo $_['subscr_yearly']; ?> <span class="badge">-25%</span></label>
            <div class="slider"></div>
        </div>

        <!-- === МЕСЯЧНЫЕ ТАРИФЫ === -->
        <div class="plan-container" id="monthly">
            <!-- Лайт -->
            <div class="plan shadow-radius" style="background-color: #F9F9FF;">
                <div class="plan-header">
                    <div class="name"><?php echo $_['subscr_plan_light']; ?></div>
                    <div><?php echo $_['subscr_plan_light_desc']; ?></div>
                    <div><img src="images/lamp.png"></div>
                    <div class="price"><?php echo $_['subscr_price_light_month_auto']; ?></div>
                    <div><?php echo $_['subscr_auto']; ?></div>
                    <div class="price"><?php echo $_['subscr_price_light_month_noauto']; ?></div>
                    <div><?php echo $_['subscr_noauto']; ?></div>
                </div>
                <ul>
                    <li>✅ <?php echo $_['subscr_light_video']; ?></li>
                    <li>✅ <?php echo $_['subscr_light_ai']; ?></li>
                </ul>
                <button class="button" onclick="window.location.href='./pay.php?p=3'"><?php echo $_['subscr_choose_auto']; ?></button>
                <button class="button secondary" onclick="window.location.href='./pay.php?p=2'"><?php echo $_['subscr_choose_noauto']; ?></button>
            </div>

            <!-- Полный -->
            <div class="highlight shadow-radius">
                <span><?php echo $_['subscr_best_choice']; ?></span>
                <div class="plan" style="background-color: #F6E8DA;">
                    <div class="plan-header">
                        <div class="name"><?php echo $_['subscr_plan_full']; ?></div>
                        <div><?php echo $_['subscr_plan_full_desc']; ?></div>
                        <div><img src="images/books.png"></div>
                    <div class="price"><?php echo $_['subscr_price_full_month_auto']; ?></div>
                    <div><?php echo $_['subscr_auto']; ?></div>
                    <div class="price"><?php echo $_['subscr_price_full_month_noauto']; ?></div>
                    <div><?php echo $_['subscr_noauto']; ?></div>
                    </div>
                    <ul>
                        <li>✅ <?php echo $_['subscr_full_video']; ?></li>
                        <li>✅ <?php echo $_['subscr_full_ai']; ?></li>
                    </ul>
                    <button class="button" onclick="window.location.href='./pay.php?p=6'"><?php echo $_['subscr_choose_auto']; ?></button>
                    <button class="button secondary" onclick="window.location.href='./pay.php?p=5'"><?php echo $_['subscr_choose_noauto']; ?></button>
                </div>
            </div>

            <!-- Всё включено -->
            <div class="plan shadow-radius" style="background-color: #F9F9FF;">
                <div class="plan-header">
                    <div class="name"><?php echo $_['subscr_plan_all']; ?></div>
                    <div><?php echo $_['subscr_plan_all_desc']; ?></div>
                    <div><img src="images/all-in.png"></div>
                    <div class="price"><?php echo $_['subscr_price_all_month_auto']; ?></div>
                    <div><?php echo $_['subscr_auto']; ?></div>
                    <div class="price"><?php echo $_['subscr_price_all_month_noauto']; ?></div>
                    <div><?php echo $_['subscr_noauto']; ?></div>
                </div>
                <ul>
                    <li>✅ <?php echo $_['subscr_all_video']; ?></li>
                    <li>✅ <?php echo $_['subscr_all_ai']; ?></li>
                </ul>
                <button class="button" onclick="window.location.href='./pay.php?p=9'"><?php echo $_['subscr_choose_auto']; ?></button>
                <button class="button secondary" onclick="window.location.href='./pay.php?p=8'"><?php echo $_['subscr_choose_noauto']; ?></button>
            </div>
        </div>

        <!-- === ГОДОВЫЕ ТАРИФЫ === -->
        <div class="plan-container" id="yearly">
            <!-- Лайт -->
            <div class="plan shadow-radius" style="background-color: #F9F9FF;">
                <div class="plan-header">
                    <div class="name"><?php echo $_['subscr_plan_light']; ?></div>
                    <div><?php echo $_['subscr_plan_light_desc']; ?></div>
                    <div><img src="images/lamp.png"></div>
                    <div class="price-day"><?php echo $_['subscr_price_day_light']; ?></div>
                    <div class="price-month"><?php echo $_['subscr_price_month_light']; ?></div>
                    <div class="price-year"><?php echo $_['subscr_price_year_light']; ?></div>
                </div>
                <ul>
                    <li>✅ <?php echo $_['subscr_light_video_year']; ?></li>
                    <li>✅ <?php echo $_['subscr_light_ai_year']; ?></li>
                </ul>
                <button class="button" onclick="window.location.href='./pay.php?p=4'"><?php echo $_['subscr_buy_year']; ?></button>
            </div>

            <!-- Полный -->
            <div class="highlight shadow-radius">
                <span><?php echo $_['subscr_best_choice']; ?></span>
                <div class="plan" style="background-color: #F6E8DA;">
                    <div class="plan-header">
                        <div class="name"><?php echo $_['subscr_plan_full']; ?></div>
                        <div><?php echo $_['subscr_plan_full_desc']; ?></div>
                        <div><img src="images/books.png"></div>
                        <div class="price-day"><?php echo $_['subscr_price_day_full']; ?></div>
                        <div class="price-month"><?php echo $_['subscr_price_month_full']; ?></div>
                        <div class="price-year"><?php echo $_['subscr_price_year_full']; ?></div>
                    </div>
                    <ul>
                        <li>✅ <?php echo $_['subscr_full_video']; ?></li>
                        <li>✅ <?php echo $_['subscr_full_ai_year']; ?></li>
                    </ul>
                    <button class="button" onclick="window.location.href='./pay.php?p=7'"><?php echo $_['subscr_buy_year']; ?></button>
                </div>
            </div>

            <!-- Всё включено -->
            <div class="plan shadow-radius" style="background-color: #F9F9FF;">
                <div class="plan-header">
                    <div class="name"><?php echo $_['subscr_plan_all']; ?></div>
                    <div><?php echo $_['subscr_plan_all_desc']; ?></div>
                    <div><img src="images/all-in.png"></div>
                    <div class="price-day"><?php echo $_['subscr_price_day_all']; ?></div>
                    <div class="price-month"><?php echo $_['subscr_price_month_all']; ?></div>
                    <div class="price-year"><?php echo $_['subscr_price_year_all']; ?></div>
                </div>
                <ul>
                    <li>✅ <?php echo $_['subscr_all_video']; ?></li>
                    <li>✅ <?php echo $_['subscr_all_ai_year']; ?></li>
                </ul>
                <button class="button" onclick="window.location.href='./pay.php?p=10'"><?php echo $_['subscr_buy_year']; ?></button>
            </div>
        </div>

        <!-- ПРОМО-БЛОК -->
        <div class="promo-block shadow-radius">
            <h2><?php echo $_['subscr_promo_title']; ?></h2>
            <p><?php echo $_['subscr_promo_desc']; ?></p>
            <ul><?php echo $_['subscr_promo_list']; ?></ul>
        </div>

        <!-- СНОСКИ -->
        <div class="footnotes">
            <p><b>¹</b> <?php echo $_['subscr_note_auto']; ?></p>
            <p><b>²</b> <?php echo $_['subscr_note_noauto']; ?></p>
            <p><b>³</b> <?php echo $_['subscr_note_yearly']; ?></p>
        </div>
    </div>

    <script>
        function update() {
            const isAnnual = document.getElementById('toggle-annual').checked;
            document.getElementById('monthly').style.display = isAnnual ? 'none' : 'flex';
            document.getElementById('yearly').style.display = isAnnual ? 'flex' : 'none';
        }
        document.querySelectorAll('.pricing-toggle input').forEach(el => el.addEventListener('change', update));
        update();
    </script>
</div>
</body>
</html>

<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
require_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_subscr.php';

$user_id = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite | MindGuide</title>
    <link href="https://fonts.googleapis.com/css2?family=Pattaya&family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="css/invite.css">
<style>
  .section {
    text-align: center;
    padding: 15px;
    background: #f3ffef;
    color: #888888;
    max-width: 760px;
    margin: 0 auto;
    border: 1px solid #ddd;
    border-radius: 12px;
  }

  .main-card, .faq-card, .info-card {
    margin-top: 15px;
    padding: 15px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
    max-width: 730px;
    margin-left: auto;
    margin-right: auto;
    text-align: left;
    color: #6c757d;
  }

  .main-card h4, .faq-card h4, .info-card h4{
    font-weight: 600;
    line-height: 1.5;
    padding: 5px 0;
    font-size: 17px;
    color: #222;
  }

  .main-card p{
    font-weight: 300;
    line-height: 1.5;
    padding: 5px 0;
    font-size: 15px;
  }

  .faq-card ol{
    font-weight: 300;
    line-height: 1.5;
    padding: 5px 0;
    font-size: 15px;
  }

  .faq-card li{
    margin-left: 25px;
    margin-top: 10px;
  }

    .rref-copy-block {
      background: #f9fbfd;
      border: 1px solid #ddd;
      padding: 15px;
      border-radius: 12px;
      text-align: center;
      margin: 20px auto;
    }

    .ref-copy-block {
      text-align: center;
    }

.ref-link {
  display: block;
  mmax-width: fit-content; /* или 100% при необходимости */
  width: 100%;
  max-width: 430px;
  margin: 0 auto;
  background: #e9ecef;
  padding: 10px;
  border-radius: 8px;
  word-break: break-word;
  cursor: pointer;
  margin-bottom: 10px;
  transition: background 0.3s;
}

    .ref-link:hover {
      background: #d9dfe4;
    }


    .button-row {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 10px;
      align-items: center; /* 👈 это центрирует дочерние элементы по горизонтали */
    }

    .button-row button {
      display: block;
      padding: 10px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      cursor: pointer;
      transition: 0.3s;
      width: 100%;
      max-width: 430px;
    }

    .button-tg {
      background-color: #0088cc;
      color: white;
    }

    .button-copy {
      background-color: #6c757d;
      color: white;
    }

    .button-row button:hover {
      opacity: 0.9;
    }


.stat {
  display: block;
  width: 100%;
  max-width: 430px;
  margin: 0 auto;
  background-color: #f2f5f7;
  padding: 10px;
  border-radius: 8px;
  word-break: break-word;
  cursor: pointer;
  margin-bottom: 10px;
  transition: background 0.3s;
  font-size: 14px;
  text-align: left;
}

    .toast {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #343a40;
      color: #fff;
      padding: 12px 20px;
      border-radius: 8px;
      font-size: 14px;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
      z-index: 1000;
    }

    .toast.show {
      opacity: 1;
    }

@media (max-width: 768px) {
  .toast {
    display: none !important;
  }
}

</style>
    <script src="../script.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="../images/icon.png" rel="icon" />
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-member.php'; // Подключаем меню ?>

<div class="container">

  <div class="section">

    <div class="main-card" style="margin-top: 0;">
      <h4>👉👉👉 Приглашай друзей ➡️➡️➡️ получай призы 🎁🎁🎁 !</h4>
      <p><i>Каждый друг с активной подпиской приближает тебя к призам!</i></p>
    </div>

<div class="faq-card">
  <h4>💡 Как это работает?</h4>
  <ol>
    <li>Нажми <strong>«Поделиться в Telegram»</strong> и выбери, кому отправить приглашение</li>
    <li>Или нажми <strong>«Скопировать сообщение»</strong> и вставь его в WhatsApp, Instagram или другой мессенджер</li>
    <li>Если твой друг перейдёт по ссылке и активирует бесплатную подписку — он засчитается как приглашённый</li>
    <li>Если твой друг активирует любую платную подписку — он засчитается как акивный</li>
    <li>ТОП-3 участников с наибольшим числом активных друзей получают 🎁 каждый месяц!</li>
  </ol>
</div>

    <div class="info-card">
      <h4>🔗 Твоя личная ссылка</h4><br>

      <div class="ref-copy-block">
        <div class="ref-link" id="refLink" onclick="copyRefLink()">https://t.me/MindGuideOnlineBot?start=u5</div>



        <div class="button-row">
          <button class="button-tg" onclick="shareViaTelegram()">📤 Поделиться в Telegram</button>
          <button class="button-copy" onclick="copyInvite()">📋 Скопировать сообщение</button>
        </div>

        <div class="stat" style="margin-top: 30px;">👥 Приглашено: <strong>5</strong> всего, <strong>3</strong> за месяц</div>
        <div class="stat">✅ Активных: <strong>3</strong> всего, <strong>1</strong> за месяц</div>

      </div>
    </div>

  </div>

</div>

<div id="customToast" class="toast"></div>

    <script src="js/profile.js"></script>

<script>
  function showToast(message) {
    const toast = document.getElementById("customToast");
    toast.innerText = message;
    toast.classList.add("show");

    setTimeout(() => {
      toast.classList.remove("show");
    }, 2500);
  }

  function shareViaTelegram() {
    const text = encodeURIComponent(`👋 Привет!\n\n🧠 Я нашёл платформу MindGuide, где ИИ‑психолог даёт советы и помогает разбираться в себе.\n✨ Сервис реально классный — просто попробуй 😍.\n\n🎁 Бесплатный старт тут 👇`);
    const url = encodeURIComponent("https://t.me/MindGuideOnlineBot?start=u5");
    window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
  }

  function copyInvite() {
    const message = `👋 Привет!\n\n🧠 Я нашёл платформу MindGuide, где ИИ‑психолог даёт советы и помогает разбираться в себе.\n✨ Сервис реально классный — просто попробуй 😍.\n\n🎁 Бесплатный старт тут 👇\n\nhttps://t.me/MindGuideOnlineBot?start=u5`;

    navigator.clipboard.writeText(message).then(() => {
      showToast("📋 Сообщение скопировано! Отправь его через любой мессенджер");
    });
  }

  function copyRefLink() {
    const refText = document.getElementById('refLink').innerText;
    navigator.clipboard.writeText(refText).then(() => {
      showToast("🔗 Ссылка скопирована!");
    });
  }
</script>

</body>
</html>
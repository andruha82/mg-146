<?php
// Получаем имя пользователя
$userName = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($row = $stmt->fetch()) {
        $userName = $row['name'] ?? 'друг';
    }
}
?>

<style>
.typing-indicator {
  display: flex;
  align-items: center;
  margin: 10px;
  padding: 10px 14px;
  bbackground: #f1f1f1;
  border-radius: 20px;
  font-size: 11px;
  color: #555;
  max-width: 80%;
  animation: fadeIn 0.3s ease;
}

.typing-indicator span {
  display: inline-block;
  width: 6px;
  height: 6px;
  margin-right: 5px;
  background: #999;
  border-radius: 50%;
  animation: blink 1.2s infinite ease-in-out;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}
.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes blink {
  0%, 80%, 100% { opacity: 0.3; }
  40% { opacity: 1; }
}

</style>
<!-- Кнопка открытия чата -->
<button id="chat-toggle" aria-label="<?= $_['chat_button_aria-label_open']; ?>">💬</button>

<!-- Окно чата -->
<div id="chat-window" role="dialog" aria-modal="true" aria-labelledby="chat-header">
    <div class="chat-header">
        <img src="https://i.imgur.com/Jf5DZtM.png" alt="Аватар" class="header-avatar">
        <div class="header-info">
            <h4 class="header-title"><?= $_['chat_header-title']; ?></h4>
            <div class="header-status">
                <span class="status-dot"></span>
                <span class="status-text">Online</span>
            </div>
        </div>
        <button class="close-btn" aria-label="<?= $_['chat_button_aria-label_close']; ?>">×</button>
    </div>
    <div class="chat-body" id="chat-body" aria-live="polite" role="log"></div>

    <div class="chat-input">
        <div class="input-container">
            <textarea id="chat-input" placeholder="<?= $_['chat_textarea_placeholder']; ?>" aria-label="<?= $_['chat_textarea_aria-label']; ?>"></textarea>
            <button id="send-button" title="<?= $_['chat_send-button_title']; ?>" aria-label="<?= $_['chat_send-button_title']; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M3 20v-6l15-2-15-2V4l18 8-18 8z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
const userName = <?= json_encode($userName); ?>;
const articleId = <?= json_encode($articleId); ?>;
const langFromPHP = <?= json_encode($selected_lang); ?>;

const toggle = document.getElementById("chat-toggle");
const windowChat = document.getElementById("chat-window");
const closeBtn = document.querySelector(".close-btn");
const textarea = document.getElementById("chat-input");
const chatBody = document.getElementById("chat-body");
const sendButton = document.getElementById("send-button");

// Авторасширение textarea
textarea.addEventListener('input', () => {
  textarea.style.height = '40px';
  textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
});

// Загрузка истории чата
async function loadChatHistory() {
  try {
    const response = await fetch(`../ai/chat_load.php?article_id=${articleId}`);
    const data = await response.json();
    chatBody.innerHTML = '';

    if (data.messages && data.messages.length > 0) {
      for (const msg of data.messages) {
        const bubble = document.createElement("div");
        bubble.className = "bubble " + (msg.role === 'user' ? 'user-bubble' : 'assistant-bubble');
        bubble.innerHTML = DOMPurify.sanitize(marked.parse(msg.message));
        chatBody.appendChild(bubble);
      }

      const greet = document.createElement("div");
      greet.className = "bubble assistant-bubble";
      greet.innerHTML = `<?= $_['chat_hello2']; ?>`;
      chatBody.appendChild(greet);
    } else {
      const greeting = document.createElement("div");
      greeting.className = "bubble assistant-bubble";
      greeting.innerHTML = `<?= $_['chat_hello1']; ?>`;
      chatBody.appendChild(greeting);
    }

    chatBody.scrollTop = chatBody.scrollHeight;
  } catch (error) {
    console.error('Ошибка при загрузке истории чата:', error);
  }
}

toggle.addEventListener('click', () => {
  windowChat.style.display = "flex";
  setTimeout(() => windowChat.classList.add('show'), 10);
  loadChatHistory();
});

closeBtn.addEventListener('click', () => {
  windowChat.classList.remove('show');
  setTimeout(() => windowChat.style.display = 'none', 300);
});

// Отправка по Enter, перенос строки по Shift+Enter
textarea.addEventListener("keydown", function (event) {
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault();
    sendButton.click();
  }
});

sendButton.addEventListener("click", async () => {
  const message = textarea.value.trim();
  if (!message) return;

  textarea.value = "";
  textarea.style.height = "40px";

  const userBubble = document.createElement("div");
  userBubble.className = "bubble user-bubble";
  userBubble.textContent = message;
  chatBody.appendChild(userBubble);
  chatBody.scrollTop = chatBody.scrollHeight;

  // 👉 Добавляем индикатор "Вера печатает..."
  const typingIndicator = document.createElement("div");
  typingIndicator.className = "typing-indicator";
  typingIndicator.innerHTML = `<span></span><span></span><span></span> Вера О'Рэйн печатает...`;
  chatBody.appendChild(typingIndicator);
  setTimeout(() => {
    chatBody.scrollTop = chatBody.scrollHeight;
  }, 100);

  try {
    const response = await fetch("../ai/generate_reply.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        question: message,
        article_id: articleId,
        lang: langFromPHP
      })
    });

    const data = await response.json();

    // ❌ Удаляем "Вера печатает..."
    if (typingIndicator) typingIndicator.remove();

    // ✅ Эмуляция печатания
    if (data.reply) {
      const replyBubble = document.createElement("div");
      replyBubble.className = "bubble assistant-bubble";
      chatBody.appendChild(replyBubble);

      const fullText = data.reply;
      let i = 0;
      const speed = 20; // скорость печати, меньше — быстрее

      function type() {
        if (i <= fullText.length) {
          const partial = fullText.substring(0, i);
          replyBubble.innerHTML = DOMPurify.sanitize(marked.parse(partial));
          chatBody.scrollTop = chatBody.scrollHeight;
          i++;
          setTimeout(type, speed);
        }
      }

      type();
    } else if (data.error) {
      alert(data.error);
    }
  } catch (error) {
    console.error("Ошибка:", error);
    alert("Произошла ошибка при отправке запроса.");
  }
});
</script>



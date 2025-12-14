document
  .getElementById("chatbot-toggle-btn")
  .addEventListener("click", toggleChatbot);
document.getElementById("close-btn").addEventListener("click", toggleChatbot);
document.getElementById("send-btn").addEventListener("click", sendMessage);
document
  .getElementById("user-input")
  .addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      sendMessage();
    }
  });

function toggleChatbot() {
  const chatbotPopup = document.getElementById("chatbot-popup");
  chatbotPopup.style.display =
    chatbotPopup.style.display === "none" ? "block" : "none";
}

async function sendMessage() {
  const userInput = document.getElementById("user-input").value.trim();
  if (userInput !== "") {
    appendMessage("user", userInput);
    document.getElementById("user-input").value = "";
    try {
      const response = await fetch('../ChatbotAssistant.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          'ajax_message': userInput,
          'conv_id': localStorage.getItem('conv_id') || ''
        })
      });
      const data = await response.json();
      localStorage.setItem('conv_id', data.conv_id);
      appendMessage("bot", data.response);
    } catch (error) {
      appendMessage("bot", "Sorry, there was an error processing your message.");
    }
  }
}

function appendMessage(sender, message) {
  const chatBox = document.getElementById("chat-box");
  const messageElement = document.createElement("div");
  messageElement.classList.add(
    sender === "user" ? "user-message" : "bot-message"
  );
  messageElement.innerHTML = message;
  chatBox.appendChild(messageElement);
  chatBox.scrollTop = chatBox.scrollHeight;
}

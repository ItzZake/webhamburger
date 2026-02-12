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
      // Determine correct path to ChatbotAssistant.php
      // ChatbotAssistant.php is in the root directory
      // Since pages are in subdirectories (FAQ/, Home Full/, etc.), we go up one level
      const apiPath = '../ChatbotAssistant.php';
      
      console.log('Chatbot: Fetching from path:', apiPath);
      console.log('Chatbot: Current page URL:', window.location.href);
      console.log('Chatbot: Current pathname:', window.location.pathname);
      
      const response = await fetch(apiPath, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          'ajax_message': userInput,
          'conv_id': localStorage.getItem('conv_id') || ''
        })
      });
      
      // Check if response is OK
      if (!response.ok) {
        const errorText = await response.text();
        console.error('Chatbot API error:', {
          status: response.status,
          statusText: response.statusText,
          url: response.url,
          errorText: errorText.substring(0, 500) // First 500 chars
        });
        
        // More specific error message based on status
        let errorMsg = "Sorry, the chatbot service is temporarily unavailable. Please try again later.";
        if (response.status === 404) {
          errorMsg = "Sorry, the chatbot service endpoint was not found. Please contact support.";
        } else if (response.status === 500) {
          errorMsg = "Sorry, there was a server error. Please try again later.";
        }
        
        appendMessage("bot", errorMsg);
        return;
      }
      
      // Try to parse JSON
      let data;
      try {
        const responseText = await response.text();
        console.log('Chatbot raw response:', responseText.substring(0, 200)); // Log first 200 chars for debugging
        
        try {
          data = JSON.parse(responseText);
        } catch (parseError) {
          console.error('JSON parse error:', parseError);
          console.error('Full response text:', responseText);
          appendMessage("bot", "Sorry, there was an error processing the response. The server returned invalid data.");
          return;
        }
      } catch (error) {
        console.error('Error reading response:', error);
        appendMessage("bot", "Sorry, there was an error reading the response. Please try again.");
        return;
      }
      
      // Check if response has required fields
      if (!data || !data.response) {
        console.error('Invalid response format:', data);
        appendMessage("bot", "Sorry, the chatbot returned an invalid response. Please try again.");
        return;
      }
      
      // Success - display response
      if (data.conv_id) {
        localStorage.setItem('conv_id', data.conv_id);
      }
      appendMessage("bot", data.response);
    } catch (error) {
      console.error('Chatbot error:', error);
      appendMessage("bot", "Sorry, there was an error connecting to the chatbot. Please check your internet connection and try again.");
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

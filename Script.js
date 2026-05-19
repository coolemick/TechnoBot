const sendBtn = document.getElementById("send-btn");
const userInput = document.getElementById("user-input");
const chatBox = document.getElementById("chat-box");

function addMessage(message, sender, buttons = []) {
    const messageDiv = document.createElement("div");
    messageDiv.classList.add(sender === "user" ? "user-message" : "bot-message");

    const contentDiv = document.createElement("div");
    contentDiv.classList.add("message-content");
    contentDiv.textContent = message;

    messageDiv.appendChild(contentDiv);
    chatBox.appendChild(messageDiv);

    if (buttons.length > 0) {
        const buttonsContainer = document.createElement("div");
        buttonsContainer.classList.add("buttons-container");

        buttons.forEach(button => {
            const btn = document.createElement("button");
            btn.classList.add("option-button");
            btn.textContent = button.label;
            btn.addEventListener("click", () => {
                addMessage(button.value, "user");
                sendMessageWithContent(button.value);
            });
            buttonsContainer.appendChild(btn);
        });

        chatBox.appendChild(buttonsContainer);
    }

    chatBox.scrollTop = chatBox.scrollHeight;
}

function showTypingIndicator() {
    const typingDiv = document.createElement("div");
    typingDiv.classList.add("bot-message");
    typingDiv.id = "typing-indicator";

    const typingContainer = document.createElement("div");
    typingContainer.classList.add("typing-indicator");

    for (let i = 0; i < 3; i++) {
        const dot = document.createElement("div");
        dot.classList.add("typing-dot");
        typingContainer.appendChild(dot);
    }

    typingDiv.appendChild(typingContainer);
    chatBox.appendChild(typingDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function removeTypingIndicator() {
    const typingIndicator = document.getElementById("typing-indicator");
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

async function sendMessage() {
    const message = userInput.value.trim();

    if (message === "") return;

    addMessage(message, "user");
    userInput.value = "";

    sendMessageWithContent(message);
}

async function sendMessageWithContent(message) {
    showTypingIndicator();

    try {
        const response = await fetch("chat.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `message=${encodeURIComponent(message)}`
        });

        const data = await response.json();
        removeTypingIndicator();

        addMessage(data.reply, "bot", data.buttons || []);

    } catch (error) {
        removeTypingIndicator();
        addMessage("Er ging iets fout. Probeer het opnieuw.", "bot");
    }
}

sendBtn.addEventListener("click", sendMessage);

userInput.addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        sendMessage();
    }
});

userInput.addEventListener("keydown", function(e) {
    if (e.key === "Enter" && e.shiftKey) {
        e.preventDefault();
    }
});
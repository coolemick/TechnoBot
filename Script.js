const sendBtn = document.getElementById("send-btn");
const userInput = document.getElementById("user-input");
const chatBox = document.getElementById("chat-box");

function addMessage(message, sender, buttons = []) {

    const div = document.createElement("div");

    div.classList.add(
        sender === "user" ? "user-message" : "bot-message"
    );

    div.textContent = message;

    chatBox.appendChild(div);

    // Add buttons if provided
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

async function sendMessage() {

    const message = userInput.value.trim();

    if(message === "") return;

    addMessage(message, "user");

    userInput.value = "";

    sendMessageWithContent(message);
}

async function sendMessageWithContent(message) {

    try {

        const response = await fetch("chat.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `message=${encodeURIComponent(message)}`
        });

        const data = await response.json();

        addMessage(data.reply, "bot", data.buttons || []);

    } catch(error) {

        addMessage("Er ging iets fout 😢", "bot");

    }
}

sendBtn.addEventListener("click", sendMessage);

userInput.addEventListener("keypress", function(e) {
    if(e.key === "Enter") {
        sendMessage();
    }
});
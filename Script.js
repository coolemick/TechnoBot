const sendBtn = document.getElementById("send-btn");
const userInput = document.getElementById("user-input");
const chatBox = document.getElementById("chat-box");

function addMessage(message, sender) {

    const div = document.createElement("div");

    div.classList.add(
        sender === "user" ? "user-message" : "bot-message"
    );

    div.textContent = message;

    chatBox.appendChild(div);

    chatBox.scrollTop = chatBox.scrollHeight;
}

async function sendMessage() {

    const message = userInput.value.trim();

    if(message === "") return;

    addMessage(message, "user");

    userInput.value = "";

    try {

        const response = await fetch("chat.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `message=${encodeURIComponent(message)}`
        });

        const data = await response.json();

        addMessage(data.reply, "bot");

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
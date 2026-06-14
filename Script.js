const sendBtn = document.getElementById("send-btn");
const userInput = document.getElementById("user-input");
const chatBox = document.getElementById("chat-box");
const chatbotToggle = document.getElementById("chatbot-toggle");
const chatWrapper = document.getElementById("chat-wrapper");
const closeButton = document.getElementById("close-button");

const SESSION_KEY = "technobot_history";

let isChatbotOpen = false;

// ── TOGGLE ────────────────────────────────────────────────────────────────────

function toggleChatbot() {
    isChatbotOpen = !isChatbotOpen;

    if (isChatbotOpen) {
        chatWrapper.classList.add("active");
        chatbotToggle.classList.add("hidden");
        userInput.focus();
    } else {
        chatWrapper.classList.remove("active");
        chatbotToggle.classList.remove("hidden");
    }
}

chatbotToggle.addEventListener("click", toggleChatbot);
closeButton.addEventListener("click", toggleChatbot);

document.addEventListener("click", (e) => {
    if (isChatbotOpen &&
        !chatWrapper.contains(e.target) &&
        !chatbotToggle.contains(e.target)) {
        toggleChatbot();
    }
});

// ── SESSION HISTORY ───────────────────────────────────────────────────────────

function saveMessageToHistory(sender, message, buttons = [], image = null) {
    const history = loadHistory();
    history.push({ sender, message, buttons, image });
    try {
        sessionStorage.setItem(SESSION_KEY, JSON.stringify(history));
    } catch (_) { }
}

function loadHistory() {
    try {
        return JSON.parse(sessionStorage.getItem(SESSION_KEY) || "[]");
    } catch (_) {
        return [];
    }
}

function restoreHistory() {
    const history = loadHistory();
    history.forEach(({ sender, message, buttons, image }) => {
        renderMessage(message, sender, buttons, image, false);
    });
}

// ── RENDERING ─────────────────────────────────────────────────────────────────

/**
 * Render a message bubble into the chat box.
 * - Bot messages: use innerHTML so links/bold from PHP render correctly.
 * - User messages: use textContent to prevent XSS.
 */
function renderMessage(message, sender, buttons = [], image = null, persist = false) {
    const messageDiv = document.createElement("div");
    messageDiv.classList.add(sender === "user" ? "user-message" : "bot-message");

    const contentDiv = document.createElement("div");
    contentDiv.classList.add("message-content");

    if (sender === "bot") {
        // Bot content comes from our own PHP — safe to render HTML (links, bold, etc.)
        contentDiv.innerHTML = message;
        // Make sure all links open in a new tab
        contentDiv.querySelectorAll("a").forEach(a => {
            a.target = "_blank";
            a.rel = "noopener noreferrer";
        });
    } else {
        // User content — always escape to prevent XSS
        contentDiv.textContent = message;
    }

    messageDiv.appendChild(contentDiv);

    if (image) {
        const imageDiv = document.createElement("div");
        imageDiv.classList.add("message-image");
        const img = document.createElement("img");
        img.src = image;
        img.alt = "Message image";
        imageDiv.appendChild(img);
        messageDiv.appendChild(imageDiv);
    }

    chatBox.appendChild(messageDiv);

    if (buttons.length > 0) {
        renderButtons(buttons);
    }

    chatBox.scrollTop = chatBox.scrollHeight;

    if (persist) {
        saveMessageToHistory(sender, message, buttons, image);
    }
}

/**
 * Render suggestion buttons.
 * When any button is clicked, remove ALL previous button containers.
 */
function renderButtons(buttons, persist = true) {
    const container = document.createElement("div");
    container.classList.add("buttons-container");

    buttons.forEach(button => {
        const btn = document.createElement("button");
        btn.classList.add("option-button");
        btn.textContent = button.label;

        if (button.borderColor) {
            btn.style.setProperty('--button-color', button.borderColor);
            btn.style.borderColor = button.borderColor;
            btn.style.color = button.borderColor;
        }

        btn.addEventListener("click", (e) => {
            e.stopPropagation();

            document.querySelectorAll(".buttons-container").forEach(bc => {
                bc.remove();
            });

            renderMessage(button.value, "user");
            sendMessageWithContent(button.value);
        });

        container.appendChild(btn);
    });

    chatBox.appendChild(container);
    chatBox.scrollTop = chatBox.scrollHeight;
}

// ── INPUT SANITIZATION ────────────────────────────────────────────────────────

function sanitizeInput(raw) {
    return raw
        .replace(/<[^>]*>/g, "")
        .replace(/[\x00-\x1F\x7F]/g, "")
        .trim()
        .substring(0, 500);
}

// ── BANNED WORDS CHECK ────────────────────────────────────────────────────────

const BANNED_WORDS = [
    "dumb", "idioot", "sukkel", "lul", "bitch", "asshole", "bastard", "jerk", "moron", "dope",
    "fuck", "shit", "damn", "hell", "crap", "piss",
    "porn", "sex", "xxx", "adult", "nude", "naked",
    "viagra", "casino", "lottery", "poker", "blackjack",
    "click here", "buy now", "free money", "earn fast",
    "racist", "racism", "genocide", "fascist",
    "terrorist", "bomb", "kill", "die", "suicide"
];

function containsBannedWords(message) {
    const normalizedMessage = message.toLowerCase().trim();
    const messageWords = normalizedMessage.split(/\s+/).filter(w => w.length >= 2);

    for (let bannedWord of BANNED_WORDS) {
        if (messageWords.includes(bannedWord)) return true;
        if (normalizedMessage.includes(bannedWord)) return true;
    }
    return false;
}

// ── TYPING INDICATOR ──────────────────────────────────────────────────────────

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
    const indicator = document.getElementById("typing-indicator");
    if (indicator) indicator.remove();
}

// ── SEND LOGIC ────────────────────────────────────────────────────────────────

function sendMessage() {
    const raw = userInput.value;
    const message = sanitizeInput(raw);

    if (message === "") return;

    if (containsBannedWords(message)) {
        userInput.value = "";
        return;
    }

    renderMessage(message, "user");
    userInput.value = "";
    sendMessageWithContent(message);
}

async function sendMessageWithContent(message) {
    showTypingIndicator();
    const safeMessage = sanitizeInput(message);

    try {
        const response = await fetch("chat.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `message=${encodeURIComponent(safeMessage)}`
        });

        if (!response.ok) {
            const text = await response.text();
            console.error("HTTP error", response.status, text);
            removeTypingIndicator();
            renderMessage(`Server fout: ${response.status}`, "bot");
            return;
        }

        const text = await response.text();
        console.log("Raw PHP response:", text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("JSON parse failed:", text);
            removeTypingIndicator();
            renderMessage("PHP fout — check console (F12)", "bot");
            return;
        }

        await new Promise(resolve => setTimeout(resolve, 1000));

        removeTypingIndicator();
        renderMessage(data.reply, "bot", data.buttons || [], data.image || null);

    } catch (error) {
        console.error("Fetch failed:", error);
        removeTypingIndicator();
        renderMessage("Verbinding mislukt — is chat.php bereikbaar?", "bot");
    }
}

// ── EVENT LISTENERS ───────────────────────────────────────────────────────────

sendBtn.addEventListener("click", sendMessage);

userInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") sendMessage();
});

userInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && e.shiftKey) e.preventDefault();
});

// ── INIT ──────────────────────────────────────────────────────────────────────

// restoreHistory(); // Uncomment to persist chat across page refreshes
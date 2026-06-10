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

/**
 * Save a message to sessionStorage so the chat survives soft refreshes.
 * History is an array of { sender, message, buttons, image } objects.
 */
function saveMessageToHistory(sender, message, buttons = [], image = null) {
    const history = loadHistory();
    history.push({ sender, message, buttons, image });
    try {
        sessionStorage.setItem(SESSION_KEY, JSON.stringify(history));
    } catch (_) {
        // sessionStorage full or unavailable — fail silently
    }
}

function loadHistory() {
    try {
        return JSON.parse(sessionStorage.getItem(SESSION_KEY) || "[]");
    } catch (_) {
        return [];
    }
}

/** Replay saved history into the chat box on page load */
function restoreHistory() {
    const history = loadHistory();
    history.forEach(({ sender, message, buttons, image }) => {
        renderMessage(message, sender, buttons, image, false); // false = don't re-save
    });
}

// ── RENDERING ─────────────────────────────────────────────────────────────────

/**
 * Render a message bubble (and optional buttons/image) into the chat box.
 * @param {boolean} persist  Whether to also write this to sessionStorage.
 */
function renderMessage(message, sender, buttons = [], image = null, persist = true) {
    const messageDiv = document.createElement("div");
    messageDiv.classList.add(sender === "user" ? "user-message" : "bot-message");

    const contentDiv = document.createElement("div");
    contentDiv.classList.add("message-content");
    // FIX: use textContent to avoid XSS — never innerHTML with user data
    contentDiv.textContent = message;
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
 * FIX: When any button is clicked, remove ALL previous button containers.
 */
function renderButtons(buttons, persist = true) {
    const container = document.createElement("div");
    container.classList.add("buttons-container");

    buttons.forEach(button => {
        const btn = document.createElement("button");
        btn.classList.add("option-button");
        btn.textContent = button.label;

        btn.addEventListener("click", (e) => {
            // Stop event from bubbling up to document click handler
            e.stopPropagation();

            // Remove ALL button containers from chat (old suggestions disappear)
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

/**
 * FIX: Strip HTML tags and control characters from user input client-side.
 * The server also sanitizes, so this is defence-in-depth.
 */
function sanitizeInput(raw) {
    return raw
        .replace(/<[^>]*>/g, "")           // strip HTML tags
        .replace(/[\x00-\x1F\x7F]/g, "")  // strip control characters
        .trim()
        .substring(0, 500);               // hard cap at 500 chars
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

        // Check HTTP status first
        if (!response.ok) {
            const text = await response.text();
            console.error("HTTP error", response.status, text);
            removeTypingIndicator();
            renderMessage(`Server fout: ${response.status}`, "bot");
            return;
        }

        const text = await response.text(); // read as text first
        console.log("Raw PHP response:", text);  // ← check browser console

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("JSON parse failed:", text);
            removeTypingIndicator();
            renderMessage("PHP fout — check console (F12)", "bot");
            return;
        }

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

restoreHistory();
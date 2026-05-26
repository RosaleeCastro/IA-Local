const form = document.querySelector("#askForm");
const input = document.querySelector("#questionInput");
const chatLog = document.querySelector("#chatLog");
const sendButton = document.querySelector("#sendButton");
const modelName = document.querySelector("#modelName");
const datasetSize = document.querySelector("#datasetSize");
const lastSource = document.querySelector("#lastSource");
const quickButtons = document.querySelectorAll(".quick-btn");
const charCount = document.querySelector("#charCount");

function escapeHTML(text) {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatAnswer(text) {
  const safe = escapeHTML(text.trim());
  const blocks = safe.split(/\n{2,}/).filter(Boolean);

  if (blocks.length > 1) {
    return blocks.map((block) => `<p>${block.replace(/\n/g, "<br>")}</p>`).join("");
  }

  return safe
    .replace(/\s+(\d+\.\s+)/g, "<br><br>$1")
    .replace(/\s+-\s+/g, "<br>- ")
    .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
    .replace(/^<br><br>/, "");
}

function createAvatar(role) {
  const avatar = document.createElement("div");
  avatar.className = "avatar";
  avatar.setAttribute("aria-hidden", "true");
  avatar.textContent = role === "user" ? "Tú" : "IA";
  return avatar;
}

function addMessage(role, text, meta = null, matches = []) {
  const article = document.createElement("article");
  article.className = `message ${role}`;

  const bubble = document.createElement("div");
  bubble.className = "bubble";
  bubble.innerHTML = role === "assistant" ? formatAnswer(text) : escapeHTML(text);

  if (meta) {
    const metaEl = document.createElement("div");
    metaEl.className = "meta";
    metaEl.textContent = meta;
    bubble.appendChild(metaEl);
  }

  if (matches.length > 0) {
    const matchesEl = document.createElement("div");
    matchesEl.className = "matches";
    matchesEl.innerHTML = '<div class="matches-title">Referencias cercanas</div>';

    matches.forEach((item) => {
      const row = document.createElement("div");
      row.className = "match-row";

      const score = document.createElement("span");
      score.className = "match-score";
      score.textContent = `${Math.round(item.similarity * 100)}%`;

      const question = document.createElement("span");
      question.textContent = item.question;

      row.appendChild(score);
      row.appendChild(question);
      matchesEl.appendChild(row);
    });

    bubble.appendChild(matchesEl);
  }

  article.appendChild(createAvatar(role));
  article.appendChild(bubble);
  chatLog.appendChild(article);
  chatLog.scrollTop = chatLog.scrollHeight;
}

function addThinkingMessage() {
  const article = document.createElement("article");
  article.className = "message assistant thinking";
  article.appendChild(createAvatar("assistant"));

  const bubble = document.createElement("div");
  bubble.className = "bubble";
  bubble.innerHTML = `
    <span>Consultando base local</span>
    <span class="typing-dots" aria-hidden="true">
      <span></span><span></span><span></span>
    </span>
  `;

  article.appendChild(bubble);
  chatLog.appendChild(article);
  chatLog.scrollTop = chatLog.scrollHeight;
  return article;
}

function setBusy(isBusy) {
  sendButton.disabled = isBusy;
  sendButton.innerHTML = isBusy ? "<span>Pensando</span>" : "<span>Enviar</span>";
}

function updateCharCount() {
  charCount.textContent = input.value.length;
}

async function ask(question) {
  addMessage("user", question);
  setBusy(true);

  const thinking = addThinkingMessage();

  try {
    const response = await fetch("/api/ask", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ question }),
    });

    const data = await response.json();
    thinking.remove();

    if (!response.ok) {
      addMessage("assistant", data.error || "No se pudo procesar la pregunta.");
      return;
    }

    modelName.textContent = data.model;
    datasetSize.textContent = `${data.datasetSize} ejemplos`;
    lastSource.textContent = `${data.source} (${Math.round(data.similarity * 100)}%)`;

    const meta = `Fuente: ${data.source} · similitud: ${Math.round(data.similarity * 100)}%`;
    addMessage("assistant", data.answer, meta, data.matches);
  } catch (error) {
    thinking.remove();
    addMessage("assistant", "No he podido conectar con el servidor local de la interfaz.");
  } finally {
    setBusy(false);
    input.focus();
  }
}

form.addEventListener("submit", (event) => {
  event.preventDefault();
  const question = input.value.trim();
  if (!question) return;
  input.value = "";
  updateCharCount();
  ask(question);
});

input.addEventListener("input", updateCharCount);

input.addEventListener("keydown", (event) => {
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault();
    form.requestSubmit();
  }
});

quickButtons.forEach((button) => {
  button.addEventListener("click", () => {
    input.value = button.textContent.trim();
    updateCharCount();
    form.requestSubmit();
  });
});

updateCharCount();

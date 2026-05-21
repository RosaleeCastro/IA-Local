const form = document.querySelector("#askForm");
const input = document.querySelector("#questionInput");
const chatLog = document.querySelector("#chatLog");
const sendButton = document.querySelector("#sendButton");
const modelName = document.querySelector("#modelName");
const datasetSize = document.querySelector("#datasetSize");
const lastSource = document.querySelector("#lastSource");
const quickButtons = document.querySelectorAll(".quick-btn");

function addMessage(role, text, meta = null, matches = []) {
  const article = document.createElement("article");
  article.className = `message ${role}`;

  const bubble = document.createElement("div");
  bubble.className = "bubble";
  bubble.textContent = text;

  if (meta) {
    const metaEl = document.createElement("div");
    metaEl.className = "meta";
    metaEl.textContent = meta;
    bubble.appendChild(metaEl);
  }

  if (matches.length > 0) {
    const matchesEl = document.createElement("div");
    matchesEl.className = "matches";
    matchesEl.textContent = "Referencias cercanas:";

    matches.forEach((item) => {
      const row = document.createElement("div");
      row.textContent = `${Math.round(item.similarity * 100)}% - ${item.question}`;
      matchesEl.appendChild(row);
    });

    bubble.appendChild(matchesEl);
  }

  article.appendChild(bubble);
  chatLog.appendChild(article);
  chatLog.scrollTop = chatLog.scrollHeight;
}

async function ask(question) {
  addMessage("user", question);
  sendButton.disabled = true;
  sendButton.textContent = "Pensando";

  const thinking = document.createElement("article");
  thinking.className = "message assistant";
  thinking.innerHTML = '<div class="bubble">Consultando...</div>';
  chatLog.appendChild(thinking);
  chatLog.scrollTop = chatLog.scrollHeight;

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
    sendButton.disabled = false;
    sendButton.textContent = "Enviar";
    input.focus();
  }
}

form.addEventListener("submit", (event) => {
  event.preventDefault();
  const question = input.value.trim();
  if (!question) return;
  input.value = "";
  ask(question);
});

input.addEventListener("keydown", (event) => {
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault();
    form.requestSubmit();
  }
});

quickButtons.forEach((button) => {
  button.addEventListener("click", () => {
    input.value = button.textContent.trim();
    form.requestSubmit();
  });
});

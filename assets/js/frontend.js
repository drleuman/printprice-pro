document.addEventListener("DOMContentLoaded", function () {
  const nextButton = document.querySelector("#next-button");
  if (!nextButton) return;

  nextButton.addEventListener("click", function () {
    const data = window.last_ai_response;
    if (!data || typeof data !== "object") {
      alert("Error: no AI response found. Please complete the assistant.");
      return;
    }

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/seleccion-imprenta/";

    Object.entries(data).forEach(([key, value]) => {
      try {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = typeof value === "object" ? JSON.stringify(value) : value;
        form.appendChild(input);
      } catch (err) {
        console.error("Failed to serialize value for key:", key, err);
      }
    });

    document.body.appendChild(form);
    form.submit();
  });
});

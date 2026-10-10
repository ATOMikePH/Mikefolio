const menuToggle = document.getElementById("menuToggle");
const navMenu = document.getElementById("navMenu");

menuToggle.addEventListener("click", () => {
    navMenu.classList.toggle("open");
});

document.querySelectorAll("#navMenu a").forEach(link => {
    link.addEventListener("click", () => navMenu.classList.remove("open"));
});

const form = document.getElementById("contactForm");
const formMessage = document.getElementById("formMessage");

form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const button = form.querySelector("button");
    button.disabled = true;
    button.textContent = "Sending...";

    try {
        const response = await fetch(form.action, {
            method: "POST",
            body: new FormData(form)
        });

        const data = await response.json();

        formMessage.textContent = data.message;
        formMessage.style.color = data.success ? "#15966d" : "#d34d4d";

        if (data.success) form.reset();
    } catch (error) {
        formMessage.textContent = "Something went wrong. Please try again.";
        formMessage.style.color = "#d34d4d";
    } finally {
        button.disabled = false;
        button.textContent = "Send Message";
    }
});

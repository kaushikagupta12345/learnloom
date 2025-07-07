// Modal and Form Logic
const modal = document.getElementById("modal");
const formContainer = document.getElementById("form-container");
const providerBtn = document.getElementById("provider-btn");
const seekerBtn = document.getElementById("seeker-btn");
const closeModal = document.getElementById("close-modal");

// Open Modal with Correct Form
providerBtn.addEventListener("click", () => openForm("provider"));
seekerBtn.addEventListener("click", () => openForm("seeker"));
closeModal.addEventListener("click", closeForm);

function openForm(type) {
    modal.style.display = "flex";
    if (type === "provider") {
        formContainer.innerHTML = `
            <h2>Provider Registration</h2>
            <form id="provider-form" onsubmit="register(event, 'provider')">
                <input type="text" id="provider-username" placeholder="Username" required />
                <input type="email" id="provider-email" placeholder="Email" required />
                <input type="text" id="provider-branch" placeholder="Branch" required />
                <input type="password" id="provider-password" placeholder="Password" required />
                <button type="submit">Register</button>
                <p>Already have an account? <a href="#" onclick="showLogin('provider')">Login</a></p>
            </form>
        `;
    } else {
        formContainer.innerHTML = `
            <h2>Seeker Registration</h2>
            <form id="seeker-form" onsubmit="register(event, 'seeker')">
                <input type="text" id="seeker-username" placeholder="Username" required />
                <input type="email" id="seeker-email" placeholder="Email" required />
                <input type="text" id="seeker-branch" placeholder="Branch" required />
                <input type="password" id="seeker-password" placeholder="Password" required />
                <button type="submit">Register</button>
                <p>Already have an account? <a href="#" onclick="showLogin('seeker')">Login</a></p>
            </form>
        `;
    }
}

function closeForm() {
    modal.style.display = "none";
}

// Registration Logic (Sending data to PHP server)
function register(event, type) {
    event.preventDefault(); // Prevent form from submitting the default way

    const username = document.getElementById(`${type}-username`).value.trim();
    const email = document.getElementById(`${type}-email`).value.trim();
    const branch = document.getElementById(`${type}-branch`).value.trim();
    const password = document.getElementById(`${type}-password`).value.trim();

    if (!username || !email || !branch || !password) {
        alert("All fields are required!");
        return;
    }

    // Send user data to PHP server for registration
    const formData = new FormData();
    formData.append("username", username);
    formData.append("email", email);
    formData.append("branch", branch);
    formData.append("password", password);

    fetch("register.php", {
        method: "POST",
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeForm();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
}

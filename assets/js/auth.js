document.addEventListener("DOMContentLoaded", () => {
    checkAuth(); // Проверяем авторизацию при загрузке страницы

    const authButton = document.getElementById("auth-buttons");
    if (authButton) {
        authButton.addEventListener("click", () => {
            checkAuth().then((isAuthenticated) => {
                if (!isAuthenticated) {
                    createAuthModal();
                }
            });
        });
    }

    function checkAuth() {
        return fetch('/PK/check_auth.php')
            .then(response => response.json())
            .then(data => {
                const authButtons = document.getElementById('auth-buttons');
                if (data.is_authenticated) {
                    authButtons.innerHTML = `
                        <a class="btn-login" href="/PK/assets/html/dashboard.html">Мой кабинет</a>
                    `;
                    return true; // Пользователь авторизован
                } else {
                    authButtons.innerHTML = `
                        <a class="btn-login" id="loginBtn">Войти</a>
                    `;
                    return false; // Пользователь не авторизован
                }
            })
            .catch(error => {
                console.error('Ошибка при проверке авторизации:', error);
                return false;
            });
    }

    function createAuthModal() {
        const existingModal = document.getElementById("auth-modal");
        if (existingModal) existingModal.remove();

        // Создаем модальное окно
        const modalOverlay = document.createElement("div");
        modalOverlay.id = "auth-modal";
        modalOverlay.className = "modal-overlay";

        const modalContainer = document.createElement("div");
        modalContainer.className = "modal-container";

        const modalTitle = document.createElement("h2");
        modalTitle.className = "modal-title";
        modalTitle.textContent = "Вход в систему";

        const tabContainer = document.createElement("div");
        tabContainer.className = "tab-container";

        const applicantTab = document.createElement("button");
        applicantTab.className = "tab applicant-tab active-tab";
        applicantTab.textContent = "Абитуриент";

        const employeeTab = document.createElement("button");
        employeeTab.className = "tab employee-tab";
        employeeTab.textContent = "Сотрудник";

        tabContainer.appendChild(applicantTab);
        tabContainer.appendChild(employeeTab);

        // Форма для входа
        const authForm = document.createElement("form");
        authForm.className = "auth-form";

        const loginInput = document.createElement("input");
        loginInput.className = "input-field";
        loginInput.type = "text";
        loginInput.placeholder = "Логин";

        const passwordInput = document.createElement("input");
        passwordInput.className = "input-field";
        passwordInput.type = "password";
        passwordInput.placeholder = "Пароль";

        const submitButton = document.createElement("button");
        submitButton.className = "submit-button";
        submitButton.type = "submit";
        submitButton.textContent = "Войти";

        authForm.appendChild(loginInput);
        authForm.appendChild(passwordInput);
        authForm.appendChild(submitButton);

        const registerLink = document.createElement("a");
        registerLink.className = "register-link";
        registerLink.href = "#";
        registerLink.textContent = "Регистрация для абитуриентов";

        modalContainer.appendChild(modalTitle);
        modalContainer.appendChild(tabContainer);
        modalContainer.appendChild(authForm);
        modalContainer.appendChild(registerLink);
        modalOverlay.appendChild(modalContainer);
        document.body.appendChild(modalOverlay);

        // Закрытие модального окна при клике вне формы
        modalOverlay.addEventListener("click", (e) => {
            if (e.target === modalOverlay) {
                modalOverlay.remove();
            }
        });

        // Переключение между вкладками
        applicantTab.addEventListener("click", () => {
            applicantTab.classList.add("active-tab");
            employeeTab.classList.remove("active-tab");
            registerLink.style.display = "block";
        });

        employeeTab.addEventListener("click", () => {
            employeeTab.classList.add("active-tab");
            applicantTab.classList.remove("active-tab");
            registerLink.style.display = "none";
        });

        // Открытие формы регистрации
        registerLink.addEventListener("click", (e) => {
            e.preventDefault();
            openRegisterForm();
        });

        // Обработка отправки формы авторизации
        authForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const login = loginInput.value.trim();
            const password = passwordInput.value.trim();
            const role = applicantTab.classList.contains("active-tab") ? "applicant" : "employee";
            fetch('/PK/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ login, password, role })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Авторизация успешна', 'success');
                    modalOverlay.remove();
                    checkAuth(); // Проверяем авторизацию снова
                } else {
                    showNotification('Ошибка авторизации: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка при авторизации:', error);
                alert('Произошла ошибка. Попробуйте позже.');
            });
        });
    }

    function openRegisterForm() {
        const existingModal = document.getElementById("auth-modal");
        if (existingModal) existingModal.remove();

        // Создаем модальное окно для регистрации
        const registerModal = document.createElement("div");
        registerModal.className = "modal-overlay";

        const modalContainer = document.createElement("div");
        modalContainer.className = "modal-container";

        const modalTitle = document.createElement("h2");
        modalTitle.textContent = "Регистрация для абитуриентов";

        const registerForm = document.createElement("form");
        registerForm.className = "register-form";

        const usernameInput = document.createElement("input");
        usernameInput.className = "input-field";
        usernameInput.type = "text";
        usernameInput.name = "login";
        usernameInput.placeholder = "Логин";
        usernameInput.required = true;

        const passwordInput = document.createElement("input");
        passwordInput.className = "input-field";
        passwordInput.type = "password";
        passwordInput.name = "password";
        passwordInput.placeholder = "Пароль";
        passwordInput.required = true;

        const emailInput = document.createElement("input");
        emailInput.className = "input-field";
        emailInput.type = "email";
        emailInput.name = "email";
        emailInput.placeholder = "Email";
        emailInput.required = true;

        const fullnameInput = document.createElement("input");
        fullnameInput.className = "input-field";
        fullnameInput.type = "text";
        fullnameInput.name = "fullname";
        fullnameInput.placeholder = "ФИО";
        fullnameInput.required = true;

        const submitButton = document.createElement("button");
        submitButton.className = "submit-button";
        submitButton.type = "submit";
        submitButton.textContent = "Зарегистрироваться";

        registerForm.appendChild(usernameInput);
        registerForm.appendChild(passwordInput);
        registerForm.appendChild(emailInput);
        registerForm.appendChild(fullnameInput);
        registerForm.appendChild(submitButton);

        modalContainer.appendChild(modalTitle);
        modalContainer.appendChild(registerForm);
        registerModal.appendChild(modalContainer);
        document.body.appendChild(registerModal);

        // Закрытие модального окна при клике вне формы
        registerModal.addEventListener("click", (e) => {
            if (e.target === registerModal) {
                registerModal.remove();
            }
        });

        // Обработка отправки формы регистрации
        registerForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const formData = new FormData(registerForm);
            const data = {
                login: formData.get("login"),
                password: formData.get("password"),
                email: formData.get("email"),
                fullname: formData.get("fullname"),
            };

            fetch('/PK/register.php', {
                method: 'POST',
                body: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Регистрация прошла успешно', 'success');;
                    registerModal.remove();
                } else {
                    showNotification('Ошибка регистрации: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Ошибка регистрации: ' + error.message, 'error');
            });
        });
    }
});
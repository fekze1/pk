document.addEventListener("DOMContentLoaded", () => {
    const pageTitle = document.getElementById('page-title');
    const pageDescription = document.getElementById('page-description');
    const actionButtons = document.getElementById('action-buttons');

    // Функция для проверки авторизации
    function checkAuth() {
        return fetch('/PK/check_auth.php')
            .then(response => response.json())
            .then(data => {
                const authButtons = document.getElementById('auth-buttons');
                if (data.is_authenticated) {
                    // Пользователь авторизован
                    authButtons.innerHTML = `
                        <a class="btn-login" href="/PK/assets/html/dashboard.html">Мой кабинет</a>
                    `;
                    updatePageContent(data.role); // Обновляем содержимое страницы
                    return true;
                } else {
                    // Пользователь не авторизован
                    authButtons.innerHTML = `
                        <a class="btn-login" id="loginBtn">Войти</a>
                    `;
                    updatePageContent(null); // Обновляем содержимое страницы
                    return false;
                }
            })
            .catch(error => {
                console.error('Ошибка при проверке авторизации:', error);
                return false;
            });
    }

    // Функция для обновления содержимого страницы
    function updatePageContent(role) {
        if (role === 'applicant') {
            // Абитуриент
            pageTitle.textContent = 'Подача заявки';
            pageDescription.textContent = 'Выберите действие:';
            actionButtons.innerHTML = `
                <button class="btn apply-btn">Подать заявку на вступительные экзамены</button>
                <button class="btn apply-btn">Подать заявку на зачисление</button>
                <button class="btn apply-btn">Мои заявки</button>
            `;
        } else if (role === 'employee') {
            // Сотрудник приемной комиссии
            pageTitle.textContent = 'Заявки';
            pageDescription.textContent = 'Выберите действие:';
            actionButtons.innerHTML = `
                <button class="btn apply-btn">Активные заявки</button>
                <button class="btn apply-btn">Закрытые заявки</button>
                <button class="btn apply-btn">Мои заявки</button>
            `;
        } else {
            // Неавторизованный пользователь
            pageTitle.textContent = 'Подача заявки';
            pageDescription.textContent = 'Для подачи заявки необходимо авторизоваться.';
            actionButtons.innerHTML = '';
        }

        // Добавляем обработчики для кнопок
        document.querySelectorAll('.apply-btn').forEach((button, index) => {
            button.addEventListener('click', () => {
                if (index === 0) {
                    openExamApplicationModal();
                } else if (index === 1) {
                    openEnrollmentApplicationModal();
                }
            });
        });
    }

    checkAuth();

    // Функция для создания модального окна авторизации
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
        submitButton.className = "submit-app";
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
                        checkAuth(); // Проверяем авторизацию снова и обновляем интерфейс
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

    // Функция для открытия формы регистрации
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
        submitButton.className = "submit-app";
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
                        showNotification('Регистрация прошла успешно', 'success');
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

    // Загрузка факультетов для выпадающего списка
    function loadFaculties() {
        return fetch('/PK/fetch-programs.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const faculties = data.data;
                    const examFacultySelect = document.getElementById('exam-faculty');

                    faculties.forEach(faculty => {
                        const option = document.createElement('option');
                        option.value = faculty.faculty_id;
                        option.textContent = faculty.name_faculty;

                        examFacultySelect.appendChild(option.cloneNode(true));
                    });
                } else {
                    console.error('Ошибка при загрузке факультетов:', data.message);
                }
            })
            .catch(error => console.error('Ошибка при загрузке факультетов:', error));
    }

    // Открытие модального окна для подачи заявки на вступительные экзамены
    function openExamApplicationModal() {
        document.getElementById('exam-application-modal').classList.remove('hidden');
    }

    // Закрытие модальных окон
    function closeModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            // Находим форму внутри модального окна
            const form = modal.querySelector('form');
            if (form) {
                // Сбрасываем форму, чтобы убрать ошибки валидации
                form.reset();
            }
            modal.classList.add('hidden'); // Добавляем класс hidden для скрытия
        });
    }

    // Закрытие модальных окон при клике на кнопку "Закрыть"
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', (e) => {
            closeModals();
        });
    });

    // Закрытие модальных окон при клике вне контента
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModals(); // Используем функцию closeModals
            }
        });
    });
    // Логика отправки данных заявки на вступительные экзамены
    document.getElementById('exam-application-form')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        // Определяем тип заявки
        const paymentType = data.payment_type === 'paid' ? 'PAID' : 'BUDGET';
        const examType = data.exam_type === 'with_tests' ? 'WITH TESTS' : 'WITHOUT TESTS';
        const typeApplication = `${paymentType} ${examType}`;

        fetch('/PK/submit_application.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type_application: typeApplication,
                applicant_id: sessionStorage.getItem('user_id'), // Используем ID из сессии
                faculty_id: data.faculty_id,
                total_score: data.total_score
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Заявка успешно отправлена', 'success');
                closeModals(); // Закрываем модальное окно
            } else {
                showNotification('Ошибка при отправке заявки: ' + result.message, 'error');
            }
        });
    });

    // Функция для открытия модального окна подачи заявки на зачисление
    async function openEnrollmentApplicationModal() {
        const modal = document.getElementById('enrollment-application-modal');
        const container = document.getElementById('accepted-applications-container');
        const noApplicationsMessage = document.getElementById('no-accepted-applications');
        const submitButton = document.getElementById('submit-enrollment');

        // Очищаем контейнер перед загрузкой новых данных
        container.innerHTML = '';
        noApplicationsMessage.style.display = 'none';
        submitButton.disabled = true;

        try {
            // Загружаем принятые заявки пользователя
            const response = await fetch('/PK/get_accepted_applications.php');
            const data = await response.json();

            if (data.success && data.applications.length > 0) {
                for (const application of data.applications) {
                    const item = document.createElement('div');
                    item.className = 'application-item';
                    item.dataset.type = application.type_application;
                    item.dataset.facultyId = application.faculty_id;

                    // Получаем название факультета
                    const facultyName = await getFacultyName(application.faculty_id);

                    // Перевод типа заявки
                    const typeTranslation = translateApplicationType(application.type_application);

                    item.innerHTML = `
                        <p><strong>Тип заявки:</strong> ${typeTranslation}</p>
                        <p><strong>Факультет:</strong> ${facultyName}</p>
                    `;
                    container.appendChild(item);

                    // Добавляем обработчик выбора заявки
                    item.addEventListener('click', () => {
                        document.querySelectorAll('.application-item').forEach(el => el.classList.remove('selected'));
                        item.classList.add('selected'); // Выделяем выбранный элемент
                        submitButton.disabled = false; // Активируем кнопку "Добавить"
                    });
                }
            } else {
                noApplicationsMessage.style.display = 'block'; // Показываем сообщение об отсутствии заявок
            }
        } catch (error) {
            console.error('Ошибка при загрузке принятых заявок:', error);
            noApplicationsMessage.style.display = 'block';
        }

        modal.classList.remove('hidden');
    }

    // Логика отправки данных заявки на зачисление
    document.getElementById('submit-enrollment')?.addEventListener('click', () => {
        const selectedApplication = document.querySelector('.application-item.selected');
        if (!selectedApplication) return;
    
        const typeApplication = selectedApplication.dataset.type;
        const facultyId = selectedApplication.dataset.facultyId;
        let enrollmentType;
    
        // Определяем тип заявки на зачисление
        switch (typeApplication) {
            case 'PAID WITH TESTS':
                enrollmentType = 'ENROLLMENT PAID WITH TESTS';
                break;
            case 'PAID WITHOUT TESTS':
                enrollmentType = 'ENROLLMENT PAID WITHOUT TESTS';
                break;
            case 'BUDGET WITH TESTS':
                enrollmentType = 'ENROLLMENT BUDGET WITH TESTS';
                break;
            case 'BUDGET WITHOUT TESTS':
                enrollmentType = 'ENROLLMENT BUDGET WITHOUT TESTS';
                break;
            default:
                console.error('Неизвестный тип заявки:', typeApplication);
                return;
        }
    
        // Отправляем данные на сервер
        fetch('/PK/submit_enrollment_application.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                applicant_id: sessionStorage.getItem('user_id'),
                type_application: enrollmentType,
                faculty_id: facultyId
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Заявка на зачисление успешно отправлена', 'success');
                closeModals(); // Закрываем модальное окно
            } else {
                showNotification('Ошибка при отправке заявки: ' + result.message, 'error');
            }
        });
    });
    

    // Загружаем факультеты при загрузке страницы
    loadFaculties();

    // Проверяем авторизацию при загрузке страницы
    checkAuth();

    // Добавляем обработчик для кнопки "Войти"
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
});

function translateApplicationType(type) {
    switch (type) {
        case 'PAID WITH TESTS':
            return 'Платное обучение с вступительными испытаниями';
        case 'PAID WITHOUT TESTS':
            return 'Платное обучение без вступительных испытаний';
        case 'BUDGET WITH TESTS':
            return 'Бюджетное обучение с вступительными испытаниями';
        case 'BUDGET WITHOUT TESTS':
            return 'Бюджетное обучение без вступительных испытаний';
        default:
            return type;
    }
}

async function getFacultyName(facultyId) {
    try {
        const response = await fetch(`/PK/get_faculty_name.php?faculty_id=${facultyId}`);
        const data = await response.json();

        if (data.success) {
            return data.name_faculty; // Возвращаем название факультета
        } else {
            console.error('Ошибка при получении названия факультета:', data.message);
            return 'Неизвестный факультет';
        }
    } catch (error) {
        console.error('Ошибка при получении названия факультета:', error);
        return 'Неизвестный факультет';
    }
}
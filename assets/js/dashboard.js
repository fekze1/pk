document.addEventListener("DOMContentLoaded", () => {
    // Форматирование оценок
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', (e) => {
            let value = e.target.value.replace(/[^0-9]/g, ''); // Удаляем все, кроме цифр
            if (value.length > 3) value = value.slice(0, 3); // Ограничиваем до 3 цифр
    
            // Выводим только цифры без точки
            e.target.value = value;
        });
    
        input.addEventListener('blur', (e) => {
            let value = e.target.value.replace(/[^0-9]/g, ''); // Удаляем все, кроме цифр
    
            // Форматируем значение только при потере фокуса
            if (value.length >= 2) {
                const integerPart = value.slice(0, -2); // Целая часть
                const decimalPart = value.slice(-2); // Дробная часть
                value = integerPart + '.' + decimalPart;
            }
    
            e.target.value = value; // Обновляем значение поля
        });
    });

    // Загрузка данных пользователя
    loadUserData();

    // Обработчики для модальных окон
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', closeModals);
    });

    // Логика отправки данных паспорта
    document.getElementById('passport-form')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        fetch('/PK/add_passport.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Паспорт успешно добавлен', 'success');
                loadUserData(); // Обновляем данные без перезагрузки страницы
                closeModals(); // Закрываем модальное окно
            } else {
                showNotification('Ошибка при добавлении паспорта: ' + result.message, 'error');
            }
        });
    });

    // Логика отправки данных аттестата
    document.getElementById('certificate-form')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        fetch('/PK/add_certificate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Аттестат успешно добавлен', 'success');
                loadUserData(); // Обновляем данные без перезагрузки страницы
                closeModals(); // Закрываем модальное окно
            } else {
                showNotification('Ошибка при добавлении аттестата: ' + result.message, 'error');
            }
        });
    });

    // Логика отправки данных льготы
    document.getElementById('benefit-form')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        fetch('/PK/add_benefit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Льгота успешно добавлена', 'success');
                loadUserData(); // Обновляем данные без перезагрузки страницы
                closeModals(); // Закрываем модальное окно
            } else {
                showNotification('Ошибка при добавлении льготы: ' + result.message, 'error');
            }
        });
    });
});

// Функция для загрузки данных пользователя
function loadUserData() {
    fetch('/PK/get_user_data.php')
        .then(response => response.json())
        .then(data => {
            const userInfo = document.getElementById('user-info');
            if (data.success) {
                let html = `
                    <p><strong>ФИО:</strong> ${data.fullname}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Роль:</strong> ${data.role === 'applicant' ? 'Абитуриент' : 'Сотрудник'}</p>
                `;
                if (data.role === 'applicant') {
                    // Паспорт
                    if (data.passport.series_number && data.passport.issue_date) {
                        html += `
                            <p><strong>Паспорт:</strong></p>
                            <p>Серия и номер: ${data.passport.series_number || 'Не указано'}</p>
                            <p>Дата выдачи: ${data.passport.issue_date || 'Не указана'}</p>
                        `;
                    } else {
                        html += `<button id="add-passport" style="display: block; margin-bottom: 10px;">Добавить паспорт</button>`;
                    }

                    // Аттестат
                    if (data.certificate.number && data.certificate.issue_date && data.certificate.school_grade && data.certificate.exam_grade) {
                        html += `
                            <p><strong>Аттестат:</strong></p>
                            <p>Номер: ${data.certificate.number || 'Не указано'}</p>
                            <p>Дата выдачи: ${data.certificate.issue_date || 'Не указана'}</p>
                            <p>Средняя оценка по школьным предметам: ${data.certificate.school_grade || 'Не указана'}</p>
                            <p>Средняя оценка по экзаменационным предметам: ${data.certificate.exam_grade || 'Не указана'}</p>
                        `;
                    } else {
                        html += `<button id="add-certificate" style="display: block;">Добавить аттестат</button>`;
                    }

                    // Льготы
                    html += `<h3>Мои льготы</h3>`;
                    if (data.benefits && data.benefits.length > 0) {
                        html += `<ul id="benefits-list">`;
                        data.benefits.forEach(benefit => {
                            html += `<li>${benefit.name_benefit} (${benefit.number_benefit})</li>`;
                        });
                        html += `</ul>`;
                    } else {
                        html += `<p>Льготы не добавлены</p>`;
                    }
                    html += `<button id="add-benefit" style="display: block; margin-top: 10px;">Добавить льготу</button>`;
                } else if (data.role === 'employee') {
                    // Добавляем информацию о факультетах
                    if (data.faculties && data.faculties.length > 0) {
                        html += `<p><strong>Факультеты:</strong></p>`;
                        html += `<ul>`;
                        data.faculties.forEach(faculty => {
                            html += `<li>${faculty}</li>`;
                        });
                        html += `</ul>`;
                    } else {
                        html += `<p>Вы не привязаны ни к одному факультету.</p>`;
                    }
                } else {
                    userInfo.innerHTML = `<p>${data.message}</p>`;
                }
                userInfo.innerHTML = html;

                // Обработчики для кнопок
                document.getElementById('add-passport')?.addEventListener('click', openPassportModal);
                document.getElementById('add-certificate')?.addEventListener('click', openCertificateModal);
                document.getElementById('add-benefit')?.addEventListener('click', openBenefitModal);
            } else {
                userInfo.innerHTML = `<p>${data.message}</p>`;
            }
        })
        .catch(error => console.error('Ошибка при получении данных:', error));
}

// Открытие модального окна для паспорта
function openPassportModal() {
    document.getElementById('passport-modal').classList.remove('hidden');
}

// Открытие модального окна для аттестата
function openCertificateModal() {
    document.getElementById('certificate-modal').classList.remove('hidden');
}

// Открытие модального окна для льготы
function openBenefitModal() {
    document.getElementById('benefit-modal').classList.remove('hidden');
}

// Закрытие всех модальных окон
function closeModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.add('hidden');
    });
}
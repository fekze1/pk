// notifications.js

// Функция для создания уведомлений
function showNotification(message, type = 'info', duration = 5000) {
    // Создаем контейнер для уведомлений, если его еще нет
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        document.body.appendChild(notificationContainer);
    }

    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.classList.add('notification', `notification-${type}`);
    notification.innerHTML = `
        <span>${message}</span>
        <button class="close-notification">&times;</button>
    `;

    // Добавляем уведомление в контейнер
    notificationContainer.appendChild(notification);

    // Анимация появления
    setTimeout(() => notification.classList.add('show'), 10);

    // Автоматическое удаление уведомления
    const timeout = setTimeout(() => removeNotification(notification), duration);

    // Кнопка закрытия
    const closeButton = notification.querySelector('.close-notification');
    closeButton.addEventListener('click', () => {
        clearTimeout(timeout); // Отменяем таймер
        removeNotification(notification);
    });
}

// Функция для удаления уведомления
function removeNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        notification.remove();
        // Если контейнер пуст, удаляем его
        const container = document.getElementById('notification-container');
        if (container && container.children.length === 0) {
            container.remove();
        }
    }, 300); // Время анимации исчезновения
}

document.addEventListener('DOMContentLoaded', () => {
    fetch('/PK/fetch-programs.php')
        .then(response => response.json())
        .then(data => {
            const programsContainer = document.querySelector('.program-list');
            if (!programsContainer) {
                console.error('Элемент .program-list не найден!');
                return;
            }

            if (data.status === 'success' && data.data.length > 0) {
                // Если есть данные, выводим их
                programsContainer.innerHTML = ''; // Очистить контейнер
                data.data.forEach(program => {
                    const programItem = document.createElement('div');
                    programItem.classList.add('program-item');
                    programItem.innerHTML = `
                        <h3>${program.name_faculty}</h3>
                        <p><strong>Средний балл для бюджета:</strong> ${program.avggrade_for_budget}</p>
                        <p><strong>Средний балл для платного:</strong> ${program.avggrade_for_paid}</p>
                        <p><strong>Проходной балл ЕГЭ для бюджета:</strong> ${program.examgrade_for_budget}</p>
                        <p><strong>Проходной балл ЕГЭ для платного:</strong> ${program.examgrade_for_paid}</p>
                    `;
                    programsContainer.appendChild(programItem);
                });
            } else {
                // Если данных нет, показываем сообщение
                programsContainer.innerHTML = '<p>На данный момент нет открытых направлений.</p>';
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки данных:', error);
            const programsContainer = document.querySelector('.program-list');
            if (programsContainer) {
                programsContainer.innerHTML = '<p>Ошибка загрузки данных. Попробуйте позже.</p>';
            }
        });
});
let inactivityTime = function () {
    let time;
    window.onload = resetTimer;
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;

    function logout() {
        fetch('/PK/logout.php', { method: 'POST' }).then(() => {
            window.location.href = '/PK/assets/html/index.html';
        });
    }

    function resetTimer() {
        clearTimeout(time);
        time = setTimeout(logout, 120000); // 2 минуты (120000 мс)
    }
};

inactivityTime();
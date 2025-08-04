document.addEventListener('DOMContentLoaded', function() {
    const menuIcon = document.getElementById('menu-icon');
    const navLinks = document.getElementById('nav-links');
    const dropdowns = document.querySelectorAll('.nav-links > li');

    // Функция для закрытия всех выпадающих меню
    function closeAllDropdowns(except = null) {
        dropdowns.forEach(dropdown => {
            if (dropdown !== except) {
                dropdown.classList.remove('active');
                const submenu = dropdown.querySelector('.dropdown');
                if (submenu) submenu.style.display = 'none';
            }
        });
    }

    // Обработчик клика на иконку меню (бургер)
    if (menuIcon) {
        menuIcon.addEventListener('click', function() {
            this.classList.toggle('change');
            navLinks.classList.toggle('active');
        });
    }

// Обработчик клика на элементы меню с выпадающими списками

    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(event) {
            if (window.innerWidth <= 1024) { // Только для мобильной версии
                event.stopPropagation(); // Останавливаем всплытие события

                const submenu = this.querySelector('.dropdown');
                if (!submenu) return;

                // Если меню уже активно, просто закрываем его
                if (this.classList.contains('active')) {
                    this.classList.remove('active');
                    submenu.style.display = 'none';
                } else {
                    // Закрываем все другие выпадающие меню
                    closeAllDropdowns();
                    this.classList.add('active');
                    submenu.style.display = 'block';
                }
            }
        });
    });


// Закрытие выпадающих меню при клике вне меню

    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 1024) { // Только для мобильной версии
            if (!event.target.closest('.nav-links')) {
                closeAllDropdowns();
            }
        }
    });
});




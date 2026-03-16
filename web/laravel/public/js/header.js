document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('userMenuButton');
    const dropdown = document.getElementById('userDropdown');

    if (!button || !dropdown) return;

    button.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('is-open');
    });

    document.addEventListener('click', function () {
        dropdown.classList.remove('is-open');
    });
});

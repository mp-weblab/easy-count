document.addEventListener('DOMContentLoaded', function() {
    const el = document.querySelector('.easy-count-wrapper .easy-count-title');
    if (el) {
        el.style.color = 'red';
        el.style.fontWeight = 'bold';
        console.log('Style inline appliqué sur .easy-count-title');
    } else {
        console.log('.easy-count-title non trouvé');
    }
});

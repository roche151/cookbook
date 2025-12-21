// Tag filter functionality for recipe filtering
document.addEventListener('DOMContentLoaded', function() {
    const tagCheckboxes = document.querySelectorAll('input[name="tags[]"]');
    
    tagCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.classList.remove('bg-secondary', 'border-secondary-subtle');
                label.classList.add('bg-primary', 'text-white');
            } else {
                label.classList.remove('bg-primary', 'text-white');
                label.classList.add('bg-secondary', 'border-secondary-subtle');
            }
        });
    });
});

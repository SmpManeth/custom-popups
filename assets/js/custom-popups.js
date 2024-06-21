// Handle popups display and close functionality
document.addEventListener('DOMContentLoaded', function() {
    var popups = document.querySelectorAll('.popup');
    var overlay = document.getElementById('popup-overlay');
    
    if (popups.length > 0) {
        overlay.style.display = 'block';
    }

    popups.forEach(function(popup) {
        popup.style.display = 'block';
        var closeBtn = popup.querySelector('.popup-close');
        closeBtn.addEventListener('click', function() {
            popup.style.display = 'none';
            overlay.style.display = 'none';
        });
    });
});

// Initialize color picker
jQuery(document).ready(function($){
    $('.custom-color-picker').wpColorPicker();
});

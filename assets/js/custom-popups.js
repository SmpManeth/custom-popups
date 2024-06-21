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

    // Handle the status toggle click
    document.querySelectorAll('.toggle-popup-status').forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            
            var postId = this.getAttribute('data-post-id');
            var newStatus = this.getAttribute('data-new-status');
            var nonce = this.getAttribute('data-nonce');
            
            var data = {
                action: 'toggle_popup_status',
                post_id: postId,
                new_status: newStatus,
                nonce: nonce
            };
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data).toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.textContent = newStatus === 'on' ? 'Active' : 'Inactive';
                    this.setAttribute('data-new-status', newStatus === 'on' ? 'off' : 'on');
                    //reload the current wordpress page
                    location.reload();
                } else {
                    alert('Error toggling status');
                }
            });
        });
    });
});

jQuery(document).ready(function($){
    $('.custom-color-picker').wpColorPicker();
});

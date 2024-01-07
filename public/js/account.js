var imageForm = document.querySelector('form#change-picture');
var imageError = document.querySelector('.image-error');
var tabButtons = document.querySelectorAll('button.tab-button');
var selectedTab = null;
var fileTypes = ['image/png', 'image/jpeg', 'image/gif'];

for (var i = 0; i < tabButtons.length; ++i) {
    tabButtons[i].addEventListener('click', function (e) {
        if (selectedTab) {
            selectedTab.style.display = 'none';
        }
        selectedTab = document.querySelector(e.currentTarget.getAttribute('data-for'));
        selectedTab.style.display = 'block';
    });
}

imageForm.addEventListener('submit', function (e) {
    e.preventDefault();

    var form = new FormData(imageForm);
    var imageInput = form.get('image');

    if (!fileTypes.includes(imageInput.type)) {
        imageError.innerText = lang('imageWrongType');
        return;
    }
    if (imageInput.size >= 262144) {
        imageError.innerText = lang('imageTooLarge');
        return;
    }

    var request = new XMLHttpRequest();
    request.open('POST', '/upload-image', true);

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);
            window.location.reload();
        } else {
            console.log('error');
        }
    };

    request.send(form);
});

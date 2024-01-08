var roleSwitchButtons = document.querySelectorAll('.role-switch-button');
var deleteButtons = document.querySelectorAll('.delete-button');

var switchRoleUser = function (e) {
    e.preventDefault();
    e.currentTarget.disabled = true;

    var request = new XMLHttpRequest();
    var form = new FormData();
    form.append('user_id', e.currentTarget.parentElement.parentElement.getAttribute('data-user'));
    form.append('role_id', e.currentTarget.getAttribute('data-role'));
    request.open('POST', '/moderation/user/switch', true);

    var buttonElement = e.currentTarget;

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            var data = JSON.parse(this.response);
            buttonElement.parentElement.parentElement.children[1].innerText = data['success'];
            buttonElement.disabled = false;
        } else {
            console.log('error');
        }
    };

    request.send(form);
};

var deleteUser = function (e) {
    e.preventDefault();
    e.currentTarget.disabled = true;

    var request = new XMLHttpRequest();
    var form = new FormData();
    form.append('user_id', e.currentTarget.parentElement.getAttribute('data-user'));
    request.open('POST', '/moderation/user/delete', true);

    var buttonElement = e.currentTarget;

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            buttonElement.parentElement.parentElement.removeChild(buttonElement.parentElement);
        } else {
            console.log('error');
        }
    };

    request.send(form);
};

roleSwitchButtons.forEach(function(button) {
    button.addEventListener('click', switchRoleUser);
});

deleteButtons.forEach(function(button) {
    button.addEventListener('click', deleteUser);
});

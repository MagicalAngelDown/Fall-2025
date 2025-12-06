const emailRegex = /^\w+([\-+.']\w+)*@\w+([\-]\w+)*\.\w+([\-]\w+)*$/;

function clearErrors() {
    const errorSpans = document.querySelectorAll('.error');
    errorSpans.forEach(span => {
        span.textContent = '';
    });
}

function validateRegisterForm(event) {
    clearErrors();

    let isValid = true;

    const email = document.getElementById('persEmail').value.trim();
    const password = document.getElementById('persPassword').value;
    const fName = document.getElementById('persFName').value.trim();
    const lName = document.getElementById('persLName').value.trim();
    const phone = document.getElementById('persPhone').value.trim();
    const office = document.getElementById('persOffice').value.trim();
    const dept = document.getElementById('persDept').value;

    if (!email) {
        document.getElementById('emailError').textContent = 'Email is required.';
        isValid = false;
    } else if (!emailRegex.test(email)) {
        document.getElementById('emailError').textContent = 'Invalid email format.';
        isValid = false;
    }

    if (!password) {
        document.getElementById('passwordError').textContent = 'Password is required.';
        isValid = false;
    } else if (password.length < 8) {
        document.getElementById('passwordError').textContent = 'Password must be at least 8 characters.';
        isValid = false;
    }

    if (!fName) {
        document.getElementById('fNameError').textContent = 'First name is required.';
        isValid = false;
    }

    if (!lName) {
        document.getElementById('lNameError').textContent = 'Last name is required.';
        isValid = false;
    }

    if (!phone) {
        document.getElementById('phoneError').textContent = 'Phone is required.';
        isValid = false;
    }

    if (!office) {
        document.getElementById('officeError').textContent = 'Office location is required.';
        isValid = false;
    }

    if (!dept) {
        document.getElementById('deptError').textContent = 'Department is required.';
        isValid = false;
    }

    if (!isValid) {
        event.preventDefault(); 
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', validateRegisterForm);
    }
});
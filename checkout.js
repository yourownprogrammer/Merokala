const nameInput = document.getElementById('fullname');
const phoneInput = document.getElementById('phone');
const addressInput = document.getElementById('address');
const submitBtn = document.getElementById('submitBtn');

const nameError = document.getElementById('nameError');
const phoneError = document.getElementById('phoneError');
const addressError = document.getElementById('addressError');

function validateForm(){
    let valid = true;

    // Name validation
    if(nameInput.value.trim().length < 2){
        nameError.innerText = "Enter valid name";
        nameError.style.display = "block";
        valid = false;
    } else {
        nameError.style.display = "none";
    }
    // Nepali phone validation (97/98 + 8 digits)
    const phoneValue = phoneInput.value.trim();

    if (phoneValue === "") {
        phoneError.innerText = "Phone number is required";
        phoneError.style.display = "block";
        valid = false;

    } else if (!/^[0-9]+$/.test(phoneValue)) {
        phoneError.innerText = "Only numbers allowed";
        phoneError.style.display = "block";
        valid = false;

    } else if (!/^(97|98)/.test(phoneValue)) {
        phoneError.innerText = "Must start with 97 or 98";
        phoneError.style.display = "block";
        valid = false;

    } else if (phoneValue.length !== 10) {
        phoneError.innerText = "Must be exactly 10 digits";
        phoneError.style.display = "block";
        valid = false;

    } else {
        phoneError.style.display = "none";
    }
    // Address validation
    if(addressInput.value.trim().length < 10){
        addressError.innerText = "Address must be at least 10 characters";
        addressError.style.display = "block";
        valid = false;
    } else {
        addressError.style.display = "none";
    }
    submitBtn.disabled = !valid;
}
if(nameInput){
    nameInput.addEventListener('input', validateForm);
    phoneInput.addEventListener('input', validateForm);
    addressInput.addEventListener('input', validateForm);
}
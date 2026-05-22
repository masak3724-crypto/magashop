document.addEventListener('DOMContentLoaded', function () {
    initCartQuantitySteppers();
    initCheckoutForm();
    initPaymentForm();
    initProfileForm();
});

function initCartQuantitySteppers() {
    document.querySelectorAll('.cart-qty-form').forEach(function (form) {
        var input = form.querySelector('.quantity-input');
        if (!input) {
            return;
        }

        form.querySelectorAll('.minus-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var val = parseInt(input.value, 10) || 1;
                if (val > 1) {
                    input.value = val - 1;
                    form.submit();
                }
            });
        });

        form.querySelectorAll('.plus-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var val = parseInt(input.value, 10) || 1;
                var max = parseInt(input.getAttribute('max'), 10) || 100;
                if (val < max) {
                    input.value = val + 1;
                    form.submit();
                }
            });
        });
    });
}

function initCheckoutForm() {
    var form = document.getElementById('checkout-form');
    if (!form) {
        return;
    }

    var lastName = form.querySelector('[name="last_name"]');
    var postalCode = form.querySelector('[name="postal_code"]');
    var city = form.querySelector('[name="city"]');
    var cities = [];

    try {
        cities = JSON.parse(form.dataset.cities || '[]');
    } catch (e) {
        cities = [];
    }

    var cyrillicNamePattern = /^[а-яА-ЯёЁ\s\-]+$/;

    if (lastName) {
        lastName.addEventListener('input', function () {
            var raw = lastName.value;
            var cleaned = raw.replace(/[^а-яА-ЯёЁ\s\-]/g, '');

            if (raw !== cleaned) {
                showFieldError(lastName, 'В фамилии допускаются только буквы');
            } else {
                clearFieldError(lastName);
            }

            lastName.value = cleaned;
        });
    }

    if (postalCode) {
        postalCode.addEventListener('input', function () {
            postalCode.value = postalCode.value.replace(/\D/g, '').slice(0, 6);
            validatePostalCode(postalCode);
        });
        postalCode.addEventListener('blur', function () {
            validatePostalCode(postalCode);
        });
    }

    if (city) {
        city.addEventListener('input', function () {
            filterCitySuggestions(city, cities);
            validateCity(city, cities);
        });
        city.addEventListener('blur', function () {
            validateCity(city, cities);
        });
    }

    form.addEventListener('submit', function (e) {
        var valid = true;

        if (lastName && !cyrillicNamePattern.test(lastName.value.trim())) {
            showFieldError(lastName, 'В фамилии допускаются только буквы');
            valid = false;
        }

        if (postalCode && !validatePostalCode(postalCode, true)) {
            valid = false;
        }

        if (city && !validateCity(city, cities, true)) {
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
}

function initPaymentForm() {
    var form = document.getElementById('payment-form');
    if (!form) {
        return;
    }

    var cardNumber = form.querySelector('[name="card_number"]');
    var cardName = form.querySelector('[name="card_name"]');
    var cardExpiry = form.querySelector('[name="card_expiry"]');
    var cardCvv = form.querySelector('[name="card_cvv"]');

    if (cardNumber) {
        cardNumber.addEventListener('input', function () {
            cardNumber.value = formatCardNumber(cardNumber.value);
            validateCardNumber(cardNumber);
        });
    }

    if (cardName) {
        cardName.addEventListener('input', function () {
            var raw = cardName.value;
            var cleaned = raw.replace(/[^a-zA-Z\s]/g, '').toUpperCase();

            if (raw !== cleaned) {
                showFieldError(cardName, 'Допускаются только английские буквы');
            } else {
                clearFieldError(cardName);
            }

            cardName.value = cleaned;
        });
    }

    if (cardExpiry) {
        cardExpiry.addEventListener('input', function () {
            cardExpiry.value = formatCardExpiry(cardExpiry.value);
            validateCardExpiry(cardExpiry);
        });
    }

    if (cardCvv) {
        cardCvv.addEventListener('input', function () {
            cardCvv.value = cardCvv.value.replace(/\D/g, '').slice(0, 3);
            validateCardCvv(cardCvv);
        });
    }

    form.addEventListener('submit', function (e) {
        var valid = true;

        if (cardNumber && !validateCardNumber(cardNumber)) {
            valid = false;
        }
        if (cardName && !cardName.value.trim()) {
            showFieldError(cardName, 'Укажите имя владельца');
            valid = false;
        }
        if (cardExpiry && !validateCardExpiry(cardExpiry)) {
            valid = false;
        }
        if (cardCvv && !validateCardCvv(cardCvv)) {
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
}

function formatCardNumber(value) {
    var digits = value.replace(/\D/g, '').slice(0, 16);
    var parts = [];

    for (var i = 0; i < digits.length; i += 4) {
        parts.push(digits.slice(i, i + 4));
    }

    return parts.join(' ');
}

function formatCardExpiry(value) {
    var digits = value.replace(/[^\d]/g, '').slice(0, 4);

    if (digits.length <= 2) {
        return digits;
    }

    return digits.slice(0, 2) + '/' + digits.slice(2);
}

function validatePostalCode(input, required) {
    if (!input.value.length) {
        if (required) {
            showFieldError(input, 'Индекс должен содержать ровно 6 цифр');
            return false;
        }
        clearFieldError(input);
        return true;
    }

    if (input.value.length === 6) {
        clearFieldError(input);
        return true;
    }

    showFieldError(input, 'Индекс должен содержать ровно 6 цифр');
    return false;
}

function validateCity(input, cities, required) {
    var value = input.value.trim();

    if (!value) {
        if (required) {
            showFieldError(input, 'Выберите город из списка');
            return false;
        }
        clearFieldError(input);
        return true;
    }

    var match = cities.find(function (city) {
        return city.toLowerCase() === value.toLowerCase();
    });

    if (!match) {
        showFieldError(input, 'Выберите город из списка');
        return false;
    }

    input.value = match;
    clearFieldError(input);
    return true;
}

function validateAddress(input, required) {
    var value = input.value.trim();
    var allowedPattern = /^[а-яА-ЯёЁa-zA-Z0-9\s.,\-\/№]+$/u;
    var hasLetter = /[а-яА-ЯёЁa-zA-Z]/u;

    if (!value) {
        if (required) {
            showFieldError(input, 'Укажите адрес');
            return false;
        }
        clearFieldError(input);
        return true;
    }

    if (value.length < 5) {
        showFieldError(input, 'Адрес слишком короткий (минимум 5 символов)');
        return false;
    }

    if (!allowedPattern.test(value) || !hasLetter.test(value)) {
        showFieldError(input, 'Укажите корректный адрес (улица, дом)');
        return false;
    }

    clearFieldError(input);
    return true;
}

function filterCitySuggestions(input, cities) {
    var list = document.getElementById(input.getAttribute('list'));
    if (!list) {
        return;
    }

    var query = input.value.trim().toLowerCase();
    list.innerHTML = '';

    cities
        .filter(function (city) {
            return !query || city.toLowerCase().includes(query);
        })
        .slice(0, 12)
        .forEach(function (city) {
            var option = document.createElement('option');
            option.value = city;
            list.appendChild(option);
        });
}

function validateCardNumber(input) {
    var digits = input.value.replace(/\D/g, '');

    if (digits.length === 16) {
        clearFieldError(input);
        return true;
    }

    showFieldError(input, 'Номер карты должен содержать 16 цифр');
    return false;
}

function validateCardExpiry(input) {
    if (/^\d{2}\/\d{2}$/.test(input.value)) {
        clearFieldError(input);
        return true;
    }

    showFieldError(input, 'Укажите срок в формате MM/YY');
    return false;
}

function validateCardCvv(input) {
    if (/^\d{3}$/.test(input.value)) {
        clearFieldError(input);
        return true;
    }

    showFieldError(input, 'CVV должен содержать 3 цифры');
    return false;
}

function initProfileForm() {
    var form = document.getElementById('profile-form');
    if (!form) {
        return;
    }

    var cities = [];
    var phoneCountries = [];

    try {
        cities = JSON.parse(form.dataset.cities || '[]');
        phoneCountries = JSON.parse(form.dataset.phoneCountries || '[]');
    } catch (e) {
        cities = [];
        phoneCountries = [];
    }

    var city = form.querySelector('[name="city"]');
    var address = form.querySelector('[name="address"]');
    var postalCode = form.querySelector('[name="postal_code"]');
    var phoneNational = document.getElementById('phone-national-input');
    var phoneHidden = document.getElementById('phone-full-input');
    var phoneFlag = document.getElementById('phone-country-flag');
    var phoneDial = document.getElementById('phone-country-dial');

    var selectedCountry = phoneCountries.find(function (country) {
        return country.code === (form.dataset.phoneCountry || 'RU');
    }) || phoneCountries[0];

    function setSelectedCountry(country) {
        selectedCountry = country;
        if (phoneFlag) {
            phoneFlag.textContent = country.flag;
        }
        if (phoneDial) {
            phoneDial.textContent = '+' + country.dial;
        }
        if (phoneNational) {
            phoneNational.placeholder = buildPhonePlaceholder(country);
            phoneNational.value = formatNationalDigits(phoneNational.value.replace(/\D/g, ''), country);
            syncPhoneHidden();
        }
    }

    if (selectedCountry) {
        setSelectedCountry(selectedCountry);
    }

    if (form.dataset.phoneNational && phoneNational) {
        phoneNational.value = formatNationalDigits(form.dataset.phoneNational, selectedCountry);
        syncPhoneHidden();
    }

    form.querySelectorAll('.phone-country-option').forEach(function (button) {
        button.addEventListener('click', function () {
            var country = phoneCountries.find(function (item) {
                return item.code === button.dataset.code;
            });
            if (!country) {
                return;
            }
            setSelectedCountry(country);
        });
    });

    if (phoneNational) {
        phoneNational.addEventListener('input', function () {
            phoneNational.value = formatNationalDigits(phoneNational.value.replace(/\D/g, ''), selectedCountry);
            syncPhoneHidden();
            validatePhoneField(phoneNational, phoneHidden, selectedCountry, false);
        });
        phoneNational.addEventListener('blur', function () {
            validatePhoneField(phoneNational, phoneHidden, selectedCountry, false);
        });
    }

    if (city) {
        city.addEventListener('input', function () {
            filterCitySuggestions(city, cities);
            validateCity(city, cities, false);
        });
        city.addEventListener('blur', function () {
            validateCity(city, cities, false);
        });
    }

    if (address) {
        address.addEventListener('input', function () {
            var raw = address.value;
            var cleaned = raw.replace(/[^а-яА-ЯёЁa-zA-Z0-9\s.,\-\/№]/g, '');
            if (raw !== cleaned) {
                showFieldError(address, 'Адрес содержит недопустимые символы');
            } else {
                validateAddress(address, false);
            }
            address.value = cleaned;
        });
        address.addEventListener('blur', function () {
            validateAddress(address, false);
        });
    }

    if (postalCode) {
        postalCode.addEventListener('input', function () {
            postalCode.value = postalCode.value.replace(/\D/g, '').slice(0, 6);
            validatePostalCode(postalCode, false);
        });
        postalCode.addEventListener('blur', function () {
            validatePostalCode(postalCode, false);
        });
    }

    form.addEventListener('submit', function (e) {
        syncPhoneHidden();
        var valid = true;

        if (!validatePhoneField(phoneNational, phoneHidden, selectedCountry, false)) {
            valid = false;
        }
        if (city && !validateCity(city, cities, false)) {
            valid = false;
        }
        if (address && !validateAddress(address, false)) {
            valid = false;
        }
        if (postalCode && !validatePostalCode(postalCode, false)) {
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });

    function syncPhoneHidden() {
        if (!phoneHidden || !phoneNational || !selectedCountry) {
            return;
        }

        var digits = phoneNational.value.replace(/\D/g, '');

        if (!digits.length) {
            phoneHidden.value = '';
            return;
        }

        phoneHidden.value = '+' + selectedCountry.dial + ' ' + formatNationalDigits(digits, selectedCountry);
    }
}

function buildPhonePlaceholder(country) {
    var digit = 1;

    return country.groups.map(function (size) {
        var part = '';
        for (var i = 0; i < size; i++) {
            part += String(digit % 10);
            digit += 1;
        }
        return part;
    }).join(' ');
}

function formatNationalDigits(digits, country) {
    if (!country || !digits) {
        return '';
    }

    digits = digits.slice(0, country.nationalLength);
    var parts = [];
    var offset = 0;

    country.groups.forEach(function (size) {
        var part = digits.slice(offset, offset + size);
        if (part) {
            parts.push(part);
        }
        offset += size;
    });

    return parts.join(' ');
}

function validatePhoneField(nationalInput, hiddenInput, country, required) {
    if (!nationalInput || !hiddenInput || !country) {
        return true;
    }

    var digits = nationalInput.value.replace(/\D/g, '');

    if (!digits.length) {
        hiddenInput.value = '';
        if (required) {
            showFieldError(nationalInput, 'Укажите номер телефона');
            return false;
        }
        clearFieldError(nationalInput);
        return true;
    }

    if (digits.length !== country.nationalLength) {
        showFieldError(nationalInput, 'Введите номер полностью');
        return false;
    }

    hiddenInput.value = '+' + country.dial + ' ' + formatNationalDigits(digits, country);
    clearFieldError(nationalInput);
    return true;
}

function showFieldError(input, message) {
    input.classList.add('is-invalid');
    var container = input.closest('.phone-field') || input.closest('.phone-input-group') || input.parentElement;
    var feedback = container.querySelector('.invalid-feedback');

    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        container.appendChild(feedback);
    }

    feedback.textContent = message;
}

function clearFieldError(input) {
    input.classList.remove('is-invalid');
    var container = input.closest('.phone-field') || input.closest('.phone-input-group') || input.parentElement;
    var feedback = container.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = '';
    }
}

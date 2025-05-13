document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

window.addEventListener('scroll', function() {
    const header = document.querySelector('header');
    header.classList.toggle('sticky', window.scrollY > 0);
});


const forms = document.querySelectorAll('form');
forms.forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Dziękujemy za wiadomość! Skontaktujemy się z Tobą wkrótce.');
        this.reset();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const carModel = document.getElementById('calcCarModel');
    const daysRange = document.getElementById('calcDaysRange');
    const daysValue = document.getElementById('calcDaysValue');
    const insurance = document.getElementById('calcInsurance');
    const totalCost = document.getElementById('calcTotalCost');

    function calculateCost() {
        const dailyPrice = parseFloat(carModel.value);
        const days = parseInt(daysRange.value);
        const insuranceRate = parseFloat(insurance.value);
        
        const total = dailyPrice * days * insuranceRate;
        totalCost.textContent = Math.round(total) + ' zł';
    }

    // Event listeners
    daysRange.addEventListener('input', function() {
        daysValue.textContent = this.value;
        calculateCost();
    });

    carModel.addEventListener('change', calculateCost);
    insurance.addEventListener('change', calculateCost);

    // Inicjalne obliczenie
    calculateCost();
});

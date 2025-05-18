// Obsługa przycisków "Rezerwuj"
document.querySelectorAll('.btn-book').forEach(btn => {
    btn.addEventListener('click', function() {
        const carId = this.dataset.carId;
        const carName = this.dataset.carName;
        
        // Otwórz modal
        document.getElementById('modalCarName').textContent = carName;
        document.getElementById('bookingModal').style.display = 'block';
        
        // Inicjalizacja kalendarza w modal
        const calendar = new FullCalendar.Calendar(document.getElementById('bookingCalendar'), {
            initialView: 'dayGridMonth',
            selectable: true,
            dateClick: function(info) {
                document.getElementById('bookingDate').textContent = info.dateStr;
                
                // Pobierz cenę auta (możesz też pobrać z data-car-price)
                fetch(`php/api/get_price.php?car_id=${carId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('bookingPrice').textContent = data.price;
                    });
            }
        });
        calendar.render();
    });
});

// Obsługa potwierdzenia rezerwacji
document.getElementById('confirmBooking').addEventListener('click', function() {
    const carId = document.querySelector('.btn-book').dataset.carId;
    const date = document.getElementById('bookingDate').textContent;
    
    fetch('php/api/booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            car_id: carId,
            date: date,
            user_id: <?= $_SESSION['user_id'] ?? 'null' ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Rezerwacja potwierdzona!');
            location.reload();
        } else {
            alert('Błąd: ' + data.message);
        }
    });
});
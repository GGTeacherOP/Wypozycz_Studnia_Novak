document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    const carSelect = document.getElementById('car-select');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: async function(fetchInfo) {
            const response = await fetch(`/php/api/availability.php?car_id=${carSelect.value}&start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`);
            return await response.json();
        },
        dateClick: async function(info) {
            if(confirm(`Zarezerwować auto na ${info.dateStr}?`)) {
                const response = await fetch('/php/api/booking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        car_id: carSelect.value,
                        date: info.dateStr,
                        user_id: <?= $_SESSION['user_id'] ?? 'null' ?>
                    })
                });
                
                const result = await response.json();
                if(result.success) {
                    calendar.refetchEvents();
                    alert('Rezerwacja potwierdzona!');
                } else {
                    alert('Błąd: ' + result.message);
                }
            }
        }
    });
    
    calendar.render();
    carSelect.addEventListener('change', () => calendar.refetchEvents());
});
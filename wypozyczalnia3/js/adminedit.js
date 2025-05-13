document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const offerId = urlParams.get('id');
    const form = document.getElementById('edit-offer-form');
    const imagePreview = document.getElementById('image-preview');

    // Dane ofert - w praktyce powinny być pobierane z API/backendu
    const offers = [
        {
            id: 1,
            title: "Cessna 172 Skyhawk",
            type: "turystyczny",
            capacity: "1-4",
            range: "1200 km",
            speed: "230 km/h",
            price: "800 zł/h",
            image: "https://upload.wikimedia.org/wikipedia/commons/a/ae/Cessna_172S_Skyhawk_SP%2C_Private_JP6817606.jpg"
        },
        // ... pozostałe oferty
    ];

    // Znajdź ofertę do edycji
    const offerToEdit = offers.find(offer => offer.id === parseInt(offerId));

    if (offerToEdit) {
        // Wypełnij formularz danymi
        document.getElementById('offer-id').value = offerToEdit.id;
        document.getElementById('title').value = offerToEdit.title;
        document.getElementById('type').value = offerToEdit.type;
        document.getElementById('capacity').value = offerToEdit.capacity;
        document.getElementById('price').value = offerToEdit.price;
        document.getElementById('range').value = offerToEdit.range;
        document.getElementById('speed').value = offerToEdit.speed;
        document.getElementById('image').value = offerToEdit.image;
        
        // Wyświetl podgląd zdjęcia
        updateImagePreview(offerToEdit.image);
    } else {
        alert('Nie znaleziono oferty do edycji!');
        window.location.href = 'index.html';
    }

    // Aktualizuj podgląd zdjęcia przy zmianie URL
    document.getElementById('image').addEventListener('input', function() {
        updateImagePreview(this.value);
    });

    // Obsługa wysłania formularza
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Tutaj w praktyce powinno być wysłanie danych do API/backendu
        alert('Zmiany zostały zapisane!');
        window.location.href = '../index.html';
    });

    // Funkcja do aktualizacji podglądu zdjęcia
    function updateImagePreview(imageUrl) {
        if (imageUrl) {
            imagePreview.innerHTML = `<img src="${imageUrl}" alt="Podgląd" class="thumbnail-preview">`;
        } else {
            imagePreview.innerHTML = '<p>Brak zdjęcia</p>';
        }
    }
});
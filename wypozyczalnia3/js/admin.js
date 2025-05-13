document.addEventListener('DOMContentLoaded', function() {
    // Dane przykładowe - w rzeczywistości można pobierać z API
    const offers = [
        {
            id: 1,
            title: "Cessna 172 Skyhawk",
            type: "turystyczny",
            capacity: "1-4",
            price: "800 zł/h",
            image: "../img/cessna172.jpg"
        },
        {
            id: 2,
            title: "Pilatus PC-12",
            type: "biznesowy",
            capacity: "5-10",
            price: "2500 zł/h",
            image: "../img/pilatus.jpg"
        },
        {
            id: 3,
            title: "Beechcraft King Air 350",
            type: "biznesowy",
            capacity: "5-10",
            price: "3500 zł/h",
            image: "../img/kingair.jpg"
        },
        {
            id: 4,
            title: "Embraer Phenom 300",
            type: "odrzutowy",
            capacity: "5-10",
            price: "6000 zł/h",
            image: "../img/phenom300.jpg"
        },
        {
            id: 5,
            title: "Cirrus SR22",
            type: "turystyczny",
            capacity: "1-4",
            price: "1200 zł/h",
            image: "../img/cirrus.jpg"
        },
        {
            id: 6,
            title: "Gulfstream G650",
            type: "odrzutowy",
            capacity: "10+",
            price: "15000 zł/h",
            image: "../img/gulfstream.jpg"
        }
    ];

    const offersContainer = document.getElementById('admin-offers-container');

    // Wyświetl wszystkie oferty w panelu admina
    function displayAdminOffers() {
        offersContainer.innerHTML = '';
        
        offers.forEach(offer => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>${offer.id}</td>
                <td><img src="${offer.image}" alt="${offer.title}" class="thumbnail"></td>
                <td>${offer.title}</td>
                <td>${offer.type}</td>
                <td>${offer.capacity}</td>
                <td>${offer.price}</td>
                <td class="actions">
                    <a href="edytuj.html?id=${offer.id}" class="btn btn-edit">Edytuj</a>
                    <button class="btn btn-danger" data-id="${offer.id}">Usuń</button>
                </td>
            `;
            
            offersContainer.appendChild(row);
        });
        
        // Dodaj event listenery do przycisków usuwania
        document.querySelectorAll('.btn-danger').forEach(btn => {
            btn.addEventListener('click', function() {
                const offerId = parseInt(this.getAttribute('data-id'));
                if (confirm('Czy na pewno chcesz usunąć tę ofertę?')) {
                    // Tutaj można dodać logikę usuwania
                    alert(`Oferta o ID ${offerId} została usunięta.`);
                }
            });
        });
    }

    // Inicjalne wyświetlenie ofert
    displayAdminOffers();
});


document.addEventListener('DOMContentLoaded', function() {
    // Dane ofert - w praktyce powinny być pobierane z API/backendu
    let offers = [
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

    const offersContainer = document.getElementById('admin-offers-container');
    let editedOfferId = null;

    // Wyświetl oferty w tabeli
    function displayAdminOffers() {
        offersContainer.innerHTML = '';
        
        offers.forEach(offer => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${offer.id}</td>
                <td><img src="${offer.image}" alt="${offer.title}" class="thumbnail"></td>
                <td>${offer.title}</td>
                <td>${offer.type}</td>
                <td>${offer.capacity}</td>
                <td>${offer.price}</td>
                <td class="actions">
                    <button class="btn btn-edit" data-id="${offer.id}">Edytuj</button>
                    <button class="btn btn-danger" data-id="${offer.id}">Usuń</button>
                </td>
            `;
            offersContainer.appendChild(row);
        });

        // Dodaj event listeners do przycisków
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', editOffer);
        });

        document.querySelectorAll('.btn-danger').forEach(btn => {
            btn.addEventListener('click', deleteOffer);
        });
    }

    // Edytuj ofertę
    function editOffer(e) {
        const offerId = parseInt(e.target.getAttribute('data-id'));
        const offer = offers.find(o => o.id === offerId);
        
        // Przekieruj do strony edycji z parametrem ID
        window.location.href = `adminedytuj.html?id=${offerId}`;
    }

    // Usuń ofertę
    function deleteOffer(e) {
        if(confirm('Czy na pewno chcesz usunąć tę ofertę?')) {
            const offerId = parseInt(e.target.getAttribute('data-id'));
            offers = offers.filter(offer => offer.id !== offerId);
            displayAdminOffers();
            alert('Oferta została usunięta!');
        }
    }

    // Inicjalne ładowanie ofert
    displayAdminOffers();
});


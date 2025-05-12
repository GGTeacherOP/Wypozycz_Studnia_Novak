document.addEventListener('DOMContentLoaded', function() {
    
    // Dane przykładowe - w rzeczywistości można pobierać z API
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
        {
            id: 2,
            title: "Pilatus PC-12",
            type: "biznesowy",
            capacity: "5-10",
            range: "3300 km",
            speed: "500 km/h",
            price: "2500 zł/h",
            image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT6Njb5roKAH4y2BGZmFlJhJFKhNe_erQUdQR1BJNoD4Ax87ssOttlRuI9U4aXWvtun3LI&usqp=CAU"
        },
        {
            id: 3,
            title: "Beechcraft King Air 350",
            type: "biznesowy",
            capacity: "5-10",
            range: "3000 km",
            speed: "550 km/h",
            price: "3500 zł/h",
            image: "https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcT1uXeTNw4sKy61AKGxUNCtO2dSJEVOTCcaZ0x_aWr_18buh8mvJPZ23PA9smcFEWbMPsdl-fENhhc0wmQNCwD80fWzZJ0QSarhng93yw"
        },
        {
            id: 4,
            title: "Embraer Phenom 300",
            type: "odrzutowy",
            capacity: "5-10",
            range: "3600 km",
            speed: "840 km/h",
            price: "6000 zł/h",
            image: "https://static1.simpleflyingimages.com/wordpress/wp-content/uploads/2024/04/artboard-1-the-embraer-phenom-300.png"
        },
        {
            id: 5,
            title: "Cirrus SR22",
            type: "turystyczny",
            capacity: "1-4",
            range: "1900 km",
            speed: "340 km/h",
            price: "1200 zł/h",
            image: "https://images.aircharterservice.com/global/aircraft-guide/private-charter/cirrus-sr22.jpg"
        },
        {
            id: 6,
            title: "Gulfstream G650",
            type: "odrzutowy",
            capacity: "10+",
            range: "13000 km",
            speed: "950 km/h",
            price: "15000 zł/h",
            image: "https://www.aeroflap.com.br/wp-content/uploads/2025/02/gulfstream-g650-001-1024x683.jpg"
        }
    ];

    const offerContainer = document.getElementById('offer-container');
    const typeFilter = document.getElementById('type-filter');
    const capacityFilter = document.getElementById('capacity-filter');
    const filterBtn = document.getElementById('filter-btn');

    // Wyświetl wszystkie oferty
    function displayOffers(offersToDisplay) {
        offerContainer.innerHTML = '';
        
        offersToDisplay.forEach(offer => {
            const offerCard = document.createElement('div');
            offerCard.className = 'offer-card';
            
            offerCard.innerHTML = `
                <div class="offer-img">
                    <img src="${offer.image}" alt="${offer.title}">
                </div>
                <div class="offer-details">
                    <h3>${offer.title}</h3>
                    <div class="offer-meta">
                        <span><i class="fas fa-users"></i> ${offer.capacity}</span>
                        <span>${offer.type}</span>
                    </div>
                    <div class="offer-meta">
                        <span><i class="fas fa-tachometer-alt"></i> ${offer.speed}</span>
                        <span><i class="fas fa-route"></i> ${offer.range}</span>
                    </div>
                    <div class="offer-price">${offer.price}</div>
                    <a href="#" class="offer-btn">Zarezerwuj</a>
                </div>
            `;
            
            offerContainer.appendChild(offerCard);
        });
    }

    // Filtrowanie ofert
    function filterOffers() {
        const selectedType = typeFilter.value;
        const selectedCapacity = capacityFilter.value;
        
        let filteredOffers = offers;
        
        if (selectedType !== 'all') {
            filteredOffers = filteredOffers.filter(offer => offer.type === selectedType);
        }
        
        if (selectedCapacity !== 'all') {
            const [min, max] = selectedCapacity.split('-').map(Number);
            
            filteredOffers = filteredOffers.filter(offer => {
                const capacityRange = offer.capacity.split('-').map(Number);
                const offerMin = capacityRange[0];
                const offerMax = capacityRange[1] || offerMin;
                
                if (selectedCapacity === '10+') {
                    return offerMin >= 10;
                } else {
                    return (offerMin >= min && offerMax <= max);
                }
            });
        }
        
        displayOffers(filteredOffers);
    }

    // Inicjalne wyświetlenie ofert
    displayOffers(offers);

    // Event listeners
    filterBtn.addEventListener('click', filterOffers);
    
    // Smooth scrolling dla linków
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
});

  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('offer-btn')) {
        e.preventDefault();
        window.location.href = e.target.getAttribute('reservation.html');
    }
});

// Na końcu pliku main.js dodaj:
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('offer-btn')) {
        e.preventDefault();
        const planeId = e.target.getAttribute('data-id');
        localStorage.setItem('selectedPlaneId', planeId);
        window.location.href = 'reservation.html';
    }
});
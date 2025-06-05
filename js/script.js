// You can add any client-side functionality here
document.addEventListener('DOMContentLoaded', function() {
    // Example: Auto-focus on first input in forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const firstInput = form.querySelector('input, select, textarea');
        if (firstInput) {
            firstInput.focus();
        }
    });
});
// Tab functionality
function openTab(tabName) {
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }
    
    const tabButtons = document.getElementsByClassName('tab-btn');
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove('active');
    }
    
    document.getElementById(tabName).classList.add('active');
    event.currentTarget.classList.add('active');
}

// User management forms
function showEditForm(userId) {
    document.getElementById('edit-form-' + userId).style.display = 'block';
}

function hideEditForm(userId) {
    document.getElementById('edit-form-' + userId).style.display = 'none';
}

function showPasswordForm(userId) {
    document.getElementById('password-form-' + userId).style.display = 'block';
}

function hidePasswordForm(userId) {
    document.getElementById('password-form-' + userId).style.display = 'none';
}

// Card management forms
function showCardEditForm(cardId) {
    document.getElementById('card-edit-form-' + cardId).style.display = 'block';
}

function hideCardEditForm(cardId) {
    document.getElementById('card-edit-form-' + cardId).style.display = 'none';
}

// Close popups when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('popup-form')) {
        event.target.style.display = 'none';
    }
});
// Card Management Functions
function showEditCardModal(cardId) {
    // Fetch card details via AJAX
    fetch(`get_card_details.php?card_id=${cardId}`)
        .then(response => response.json())
        .then(card => {
            document.getElementById('edit_card_id').value = card.card_id;
            document.getElementById('edit_card_number').value = card.card_number;
            document.getElementById('edit_current_balance').value = '₹' + card.balance.toFixed(2);
            document.getElementById('edit_card_status').value = card.status;
            document.getElementById('edit_expiry_date').value = card.expiry_date;
            document.getElementById('editCardModal').style.display = 'block';
        })
        .catch(error => {
            alert('Error loading card details: ' + error);
        });
}

function showRechargeModal(cardId) {
    // Fetch card details via AJAX
    fetch(`get_card_details.php?card_id=${cardId}`)
        .then(response => response.json())
        .then(card => {
            document.getElementById('recharge_card_id').value = card.card_id;
            document.getElementById('recharge_card_number').value = card.card_number;
            document.getElementById('recharge_current_balance').value = '₹' + card.balance.toFixed(2);
            document.getElementById('rechargeCardModal').style.display = 'block';
        })
        .catch(error => {
            alert('Error loading card details: ' + error);
        });
}

function confirmCardAction(cardId, action) {
    if (confirm(`Are you sure you want to ${action} this card?`)) {
        window.location.href = `?change_card_status=${action}&card_id=${cardId}`;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const cardSearch = document.getElementById('cardSearch');
    const statusFilter = document.getElementById('statusFilter');
    const cardsTable = document.getElementById('cardsTable');
    
    if (cardSearch && statusFilter && cardsTable) {
        cardSearch.addEventListener('input', filterCards);
        statusFilter.addEventListener('change', filterCards);
    }
    
    function filterCards() {
        const searchTerm = cardSearch.value.toLowerCase();
        const statusValue = statusFilter.value;
        
        Array.from(cardsTable.querySelectorAll('tbody tr')).forEach(row => {
            const cardNumber = row.cells[0].textContent.toLowerCase();
            const userName = row.cells[1].textContent.toLowerCase();
            const status = row.getAttribute('data-status');
            
            const matchesSearch = cardNumber.includes(searchTerm) || userName.includes(searchTerm);
            const matchesStatus = statusValue === '' || status === statusValue;
            
            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
    }
});

// Form validation
document.getElementById('createCardForm')?.addEventListener('submit', function(e) {
    const cardNumber = document.getElementById('card_number').value;
    if (!/^[A-Za-z0-9]{16}$/.test(cardNumber)) {
        alert('Card number must be exactly 16 alphanumeric characters');
        e.preventDefault();
    }
});
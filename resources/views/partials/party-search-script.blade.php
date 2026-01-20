
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('party_search');
    const hiddenInput = document.getElementById('party_id');
    const suggestionsBox = document.getElementById('party_suggestions');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value;

        if (query.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/api/parties/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionsBox.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(party => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.classList.add('list-group-item', 'list-group-item-action');
                            item.innerHTML = `<strong>${party.name}</strong><br><small class="text-muted">${party.address || ''}</small>`;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                searchInput.value = party.name;
                                hiddenInput.value = party.id;
                                suggestionsBox.style.display = 'none';
                                
                                // Trigger any change events if needed (e.g. for fetching specific party details)
                                hiddenInput.dispatchEvent(new Event('change'));
                            });
                            suggestionsBox.appendChild(item);
                        });
                        suggestionsBox.style.display = 'block';
                    } else {
                        suggestionsBox.style.display = 'none';
                    }
                });
        }, 300);
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
    
    // If validation failed and we have an old ID, we might want to fetch the name.
    // For now, let's just properly initialize empty.
    if(hiddenInput.value){
        // Ideally we fetch the name for the ID here to pre-fill the search box
         fetch(`/api/parties/${hiddenInput.value}/details`)
            .then(response => response.json())
            .then(data => {
                searchInput.value = data.name;
            });
    }
});
</script>

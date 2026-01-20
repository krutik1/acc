<script>
/**
 * Generic Autocomplete Logic
 * Usage: Add class 'enable-autocomplete' to inputs.
 * Attributes:
 *  - data-table: Table config key (e.g., 'challan_item_description')
 *  - data-column: Column to search (deprecated, controlled by backend map)
 *  - data-type: 'challan_item_description' | 'payment_notes' etc.
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Create suggestion container if it doesn't exist
    let suggestionBox = document.getElementById('global_suggestion_box');
    if (!suggestionBox) {
        suggestionBox = document.createElement('div');
        suggestionBox.id = 'global_suggestion_box';
        suggestionBox.style.position = 'absolute';
        suggestionBox.style.zIndex = '10000';
        suggestionBox.style.backgroundColor = 'white';
        suggestionBox.style.border = '1px solid #ddd';
        suggestionBox.style.borderRadius = '4px';
        suggestionBox.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        suggestionBox.style.display = 'none';
        suggestionBox.style.maxHeight = '200px';
        suggestionBox.style.overflowY = 'auto';
        document.body.appendChild(suggestionBox);
    }

    let activeInput = null;
    let debounceTimer = null;
    let currentFocus = -1;

    // Delegate event listener for inputs (handles dynamic fields)
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('enable-autocomplete')) {
            handleInput(e.target);
        }
    });

    document.addEventListener('keydown', function(e) {
        if (!activeInput) return;
        
        // Handle arrow keys and enter
        let items = suggestionBox.querySelectorAll('.suggestion-item');
        if (suggestionBox.style.display === 'block') {
            if (e.key === 'ArrowDown') {
                currentFocus++;
                addActive(items);
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                currentFocus--;
                addActive(items);
                e.preventDefault();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentFocus > -1) {
                    if (items[currentFocus]) items[currentFocus].click();
                } else if (items.length > 0) {
                     // If enter pressed without explicit selection, choose first if exact match?
                     // Request said "write that suggestion with enter key".
                     // Let's select the first one if user hits enter, essentially acting like tab-complete
                     // BUT only if they haven't highlighted anything specific.
                     // A safer UX is often just submitting the form if nothing selected, 
                     // but the user requirement is specific. 
                     // Let's stick to standard behavior: Select only if highlighted.
                }
            } else if (e.key === 'Escape') {
                closeSuggestions();
            }
        }
    });

    // Close suggestions on click outside
    document.addEventListener('click', function(e) {
        if (e.target !== activeInput && e.target !== suggestionBox) {
            closeSuggestions();
        }
    });

    function handleInput(input) {
        activeInput = input;
        const type = input.dataset.type;
        const query = input.value;

        clearTimeout(debounceTimer);
        
        if (!query || query.length < 1) {
            closeSuggestions();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/api/suggestions?type=${type}&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    showSuggestions(data, input);
                })
                .catch(err => console.error(err));
        }, 300);
    }

    function showSuggestions(suggestions, input) {
        if (suggestions.length === 0) {
            closeSuggestions();
            return;
        }

        currentFocus = -1;
        suggestionBox.innerHTML = '';
        
        // Position box
        const rect = input.getBoundingClientRect();
        suggestionBox.style.left = rect.left + window.scrollX + 'px';
        suggestionBox.style.top = rect.bottom + window.scrollY + 'px';
        suggestionBox.style.width = rect.width + 'px';
        
        suggestions.forEach(text => {
            const div = document.createElement('div');
            div.classList.add('suggestion-item');
            div.style.padding = '8px 12px';
            div.style.cursor = 'pointer';
            div.style.borderBottom = '1px solid #f0f0f0';
            
            // Highlight match
            const regex = new RegExp(`(${activeInput.value})`, 'gi');
            div.innerHTML = text.replace(regex, '<strong>$1</strong>');
            
            div.innerHTML += `<input type='hidden' value='${text}'>`;
            
            div.addEventListener('click', function(e) {
                activeInput.value = this.getElementsByTagName("input")[0].value;
                closeSuggestions();
                // Optionally move focus to next input?
            });

            // Hover effect
            div.addEventListener('mouseover', function() {
                this.style.backgroundColor = '#e9ecef';
            });
            div.addEventListener('mouseout', function() {
                this.style.backgroundColor = 'white';
            });

            suggestionBox.appendChild(div);
        });

        suggestionBox.style.display = 'block';
    }

    function addActive(items) {
        if (!items) return false;
        removeActive(items);
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = items.length - 1;
        items[currentFocus].classList.add("autocomplete-active");
        items[currentFocus].style.backgroundColor = '#e9ecef'; // Visual feedback
        items[currentFocus].scrollIntoView({ block: 'nearest' });
    }

    function removeActive(items) {
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove("autocomplete-active");
            items[i].style.backgroundColor = 'white';
        }
    }

    function closeSuggestions() {
        suggestionBox.style.display = 'none';
        activeInput = null;
    }
});
</script>

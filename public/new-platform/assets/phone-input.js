/**
 * Website Phone Input - Vanilla JS Interaction
 * file: public/assets/js/website/phone-input.js
 */

(function() {
    'use strict';

    window.initWebsitePhoneInputs = function() {
        const components = document.querySelectorAll('.website-phone-input-root');

        components.forEach(root => {
            if(root.dataset.init === 'true') return;
            root.dataset.init = 'true';

            const trigger = root.querySelector('.website-phone-country-trigger');
            const dropdown = root.querySelector('.website-phone-dropdown');
            const searchInput = root.querySelector('.phone-search-input');
            const list = root.querySelector('.phone-list');
            const hiddenIso = root.querySelector('.phone-iso-hidden');
            
            // Visual Elements
            const flagImg = trigger.querySelector('.phone-flag-icon');
            const dialText = trigger.querySelector('.phone-dial-text');

            // --- Methods ---

            const closeDropdown = () => {
                trigger.setAttribute('aria-expanded', 'false');
            };

            const openDropdown = () => {
                // Close others
                document.querySelectorAll('.website-phone-country-trigger[aria-expanded="true"]').forEach(t => {
                    if(t !== trigger) t.setAttribute('aria-expanded', 'false');
                });
                
                trigger.setAttribute('aria-expanded', 'true');
                dropdown.scrollTop = 0;
                searchInput.value = '';
                filterList(''); // reset filter
                setTimeout(() => searchInput.focus(), 50);

                // Scroll to selected
                const selected = list.querySelector('.phone-item.selected');
                if(selected) {
                    selected.scrollIntoView({ block: 'center' });
                }
            };

            const toggleDropdown = (e) => {
                e.preventDefault();
                e.stopPropagation();
                if(trigger.getAttribute('aria-expanded') === 'true') {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            };

            const selectItem = (item) => {
                const iso = item.dataset.iso;
                const dial = item.dataset.dial;
                
                // Update Hidden Input
                hiddenIso.value = iso;
                
                // Update UI
                dialText.textContent = dial;
                flagImg.src = `https://flagcdn.com/w20/${iso.toLowerCase()}.png`;

                // Update Selection State
                list.querySelectorAll('.phone-item').forEach(li => li.classList.remove('selected'));
                item.classList.add('selected');

                closeDropdown();
            };

            const filterList = (query) => {
                const q = query.toLowerCase();
                const items = list.querySelectorAll('.phone-item');
                items.forEach(item => {
                    const text = item.dataset.name.toLowerCase();
                    const dial = item.dataset.dial.replace('+', '');
                    if(text.includes(q) || dial.includes(q)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            };

            // --- Event Listeners ---
            
            trigger.addEventListener('click', toggleDropdown);

            searchInput.addEventListener('input', (e) => filterList(e.target.value));
            searchInput.addEventListener('click', (e) => e.stopPropagation());

            list.querySelectorAll('.phone-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectItem(item);
                });
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if(!root.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Keyboard Navigation (Basic)
            root.addEventListener('keydown', (e) => {
                if(e.key === 'Escape') closeDropdown();
            });
        });
    };

    // Auto Init on Load
    document.addEventListener('DOMContentLoaded', () => {
        window.initWebsitePhoneInputs();
    });

})();

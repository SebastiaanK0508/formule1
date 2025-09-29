document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const driverList = document.getElementById('driverList');
    const driverItems = Array.from(driverList.getElementsByTagName('li'));
    const renderList = (items) => {
        driverList.innerHTML = '';
        items.forEach(item => driverList.appendChild(item));
    };
    const applyFiltersAndSort = () => {
        const searchTerm = searchInput.value.toLowerCase();
        const sortBy = sortSelect.value;
        let filteredAndSortedDrivers = [...driverItems];
        filteredAndSortedDrivers = filteredAndSortedDrivers.filter(item => {
            const name = item.dataset.name.toLowerCase();
            return name.includes(searchTerm);
        });
        if (sortBy === 'az') {
            filteredAndSortedDrivers.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
        } else if (sortBy === 'za') {
            filteredAndSortedDrivers.sort((a, b) => b.dataset.name.localeCompare(a.dataset.name));
        } else if (sortBy === 'oldest') {
            filteredAndSortedDrivers.sort((a, b) => new Date(a.dataset.dob) - new Date(b.dataset.dob));
        } else if (sortBy === 'youngest') {
            filteredAndSortedDrivers.sort((a, b) => new Date(b.dataset.dob) - new Date(a.dataset.dob));
        }
        renderList(filteredAndSortedDrivers);
    };
    searchInput.addEventListener('input', applyFiltersAndSort);
    sortSelect.addEventListener('change', applyFiltersAndSort);
    applyFiltersAndSort();
});
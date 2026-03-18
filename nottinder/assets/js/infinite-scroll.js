let currentPage = window.currentPage || 1;
const totalPages = window.totalPages || 1;
const filterParams = window.filterParams || {};
let loading = false;
let hasMore = currentPage < totalPages;

// throttle scroll events for performance
let scrollTimeout;
window.addEventListener('scroll', () => {
    if (scrollTimeout) clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(checkScroll, 100);
});

function checkScroll() {
    if (loading || !hasMore) return;

    const scrollY = window.scrollY;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    // when user is within 300px of bottom, load more profiles
    if (scrollY + windowHeight >= documentHeight - 300) {
        loadMoreProfiles();
    }
}

function loadMoreProfiles() {
    loading = true;
    document.getElementById('loading').style.display = 'block';

    const nextPage = currentPage + 1;

    // build query string from filter params
    const params = new URLSearchParams({
        page: nextPage,
        preference: filterParams.preference || 'any',
        min_salary: filterParams.min_salary || 0,
        sort_by: filterParams.sort_by || 'newest'
    });

    fetch(BASE_URL + `/pages/browse/load_more.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                // append new profiles
                document.getElementById('profiles-container').insertAdjacentHTML('beforeend', data.html);
                currentPage = nextPage;
                hasMore = data.has_more;
            } else {
                hasMore = false;
            }
            loading = false;
            document.getElementById('loading').style.display = 'none';
        })
        .catch(error => {
            console.error('Error loading profiles:', error);
            loading = false;
            document.getElementById('loading').style.display = 'none';
        });
}
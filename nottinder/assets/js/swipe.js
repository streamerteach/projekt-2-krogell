document.addEventListener('click', function(e) {
    const leftBtn = e.target.closest('.swipe-left');
    const rightBtn = e.target.closest('.swipe-right');
    const container = document.getElementById('swipe-container');
    
    if (!container) return;

    if (leftBtn) {
        e.preventDefault();
        loadNextProfile(container);
    }
    
    if (rightBtn) {
        e.preventDefault();
        const profileId = rightBtn.dataset.profileId;
        
        // send like request
        fetch('/nottinder/api/like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ profile_id: profileId, action: 'like' })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Like failed:', data.error);
            }
            // load next profile regardless
            loadNextProfile(container);
        })
        .catch(error => {
            console.error('Error:', error);
            loadNextProfile(container);
        });
    }
});

function loadNextProfile(container) {
    // show loading spinner
    container.innerHTML = '<div class="loading-indicator"><div class="spinner"></div><p>Laddar...</p></div>';
    
    fetch('/nottinder/api/next_profile.php')
        .then(response => response.json())
        .then(data => {
            container.innerHTML = data.html || '<div class="card"><p>Kunde inte ladda profil.</p></div>';
        })
        .catch(error => {
            container.innerHTML = '<div class="card"><p>Något gick fel. Försök igen.</p></div>';
        });
}
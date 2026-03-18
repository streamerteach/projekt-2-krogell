// like/unlike functionality
document.addEventListener('click', function(e) {
    const button = e.target.closest('.btn-like');
    if (!button) return;

    e.preventDefault();

    const profileId = button.dataset.profileId;
    const card = button.closest('.profile-card');
    const likesSpan = card.querySelector('.profile-likes');

    if (!likesSpan) return;

    // determine current action
    const isLiked = button.classList.contains('liked');
    const action = isLiked ? 'unlike' : 'like';

    // disable button to prevent any doubleclicking issues
    button.disabled = true;

    fetch('/nottinder/api/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ profile_id: profileId, action: action })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // update like count
            likesSpan.innerHTML = `❤️ ${data.likes}`;

            // toggle button state
            if (action === 'like') {
                button.classList.add('liked');
                button.innerHTML = '💔 Ogilla';
            } else {
                button.classList.remove('liked');
                button.innerHTML = '❤️ Gilla';
            }
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    })
    .finally(() => {
        button.disabled = false;
    });
});
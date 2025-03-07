document.addEventListener('DOMContentLoaded', function() {
    const btnPastTab = document.querySelector('.stagent-toggle-past');
    const btnUpcomingTab = document.querySelector('.stagent-toggle-upcoming');
    const upcomingList = document.querySelector('.stagent-bookings-upcoming');
    const pastList = document.querySelector('.stagent-bookings-past');
    const loadMoreBtn = document.querySelector('.stagent-load-more');

    if (!btnPastTab || !btnUpcomingTab || !upcomingList || !pastList || !loadMoreBtn) {
        return;
    }

    let activeTab = 'upcoming';
    let hasMoreUpcoming = true;
    let hasMorePast = true;

    function updateLoadMoreVisibility() {
        if (activeTab === 'upcoming') {
            loadMoreBtn.classList.toggle('hidden', !hasMoreUpcoming);
            loadMoreBtn.classList.toggle('block', hasMoreUpcoming);
        } else {
            loadMoreBtn.classList.toggle('hidden', !hasMorePast);
            loadMoreBtn.classList.toggle('block', hasMorePast);
        }
    }

    btnPastTab.addEventListener('click', function(e) {
        e.preventDefault();
        activeTab = 'past';
        btnUpcomingTab.classList.remove('active');
        btnPastTab.classList.add('active');
        upcomingList.classList.add('hidden');
        pastList.classList.remove('hidden');
        updateLoadMoreVisibility();
    });

    btnUpcomingTab.addEventListener('click', function(e) {
        e.preventDefault();
        activeTab = 'upcoming';
        btnPastTab.classList.remove('active');
        btnUpcomingTab.classList.add('active');
        pastList.classList.add('hidden');
        upcomingList.classList.remove('hidden');
        updateLoadMoreVisibility();
    });

    loadMoreBtn.addEventListener('click', function(e) {
        e.preventDefault();

        let page = parseInt(loadMoreBtn.dataset.upcomingPage, 10);
        if (activeTab === 'past') {
            page = parseInt(loadMoreBtn.dataset.pastPage, 10);
        }

        const team = loadMoreBtn.dataset.team;
        const artists = loadMoreBtn.dataset.artists;
        const perPage = parseInt(loadMoreBtn.dataset.perPage, 10);

        const formData = new FormData();
        formData.append('action', 'stagent_load_more');
        formData.append('nonce', stagentData.nonce);
        formData.append('team', team);
        formData.append('artists', artists);
        formData.append('per_page', perPage);
        formData.append('show', activeTab);
        formData.append('page', page + 1);

        fetch(stagentData.ajaxUrl, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error: ' + (data.data || 'Unknown error occurred'));
                    return;
                }

                const newItemsHtml = data.data.html || '';
                const itemCount = data.data.count || 0;
                const hasMore = data.data.has_more || false;

                if (activeTab === 'upcoming') {
                    upcomingList.insertAdjacentHTML('beforeend', newItemsHtml);
                    loadMoreBtn.dataset.upcomingPage = page + 1;
                    hasMoreUpcoming = hasMore;
                } else {
                    pastList.insertAdjacentHTML('beforeend', newItemsHtml);
                    loadMoreBtn.dataset.pastPage = page + 1;
                    hasMorePast = hasMore;
                }

                updateLoadMoreVisibility();
            })
            .catch(err => {
                console.error('AJAX Error:', err);
                alert('Failed to load more bookings.');
            });
    });

    updateLoadMoreVisibility();
});
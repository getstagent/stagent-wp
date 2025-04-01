document.addEventListener('DOMContentLoaded', function() {
    const btnPastTab = document.querySelector('.stagent-toggle-past');
    const btnUpcomingTab = document.querySelector('.stagent-toggle-upcoming');
    const upcomingList = document.querySelector('.stagent-bookings-upcoming');
    const pastList = document.querySelector('.stagent-bookings-past');
    const loadMoreBtn = document.querySelector('.stagent-load-more');

    if (!btnUpcomingTab || !upcomingList || !loadMoreBtn) {
        return;
    }

    const pastTabExists = !!btnPastTab && !!pastList;

    let activeTab = 'upcoming';
    let hasMoreUpcoming = true;
    let hasMorePast = true;

    function updateLoadMoreVisibility() {
        if (activeTab === 'upcoming') {
            loadMoreBtn.style.display = hasMoreUpcoming ? 'block' : 'none';
        } else if (pastTabExists) {
            loadMoreBtn.style.display = hasMorePast ? 'block' : 'none';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }

    if (pastTabExists) {
        btnPastTab.addEventListener('click', function(e) {
            e.preventDefault();
            activeTab = 'past';
            btnUpcomingTab.classList.remove('active');
            btnPastTab.classList.add('active');
            upcomingList.style.display = 'none';
            pastList.style.display = '';
            updateLoadMoreVisibility();
        });
    }

    btnUpcomingTab.addEventListener('click', function(e) {
        e.preventDefault();
        activeTab = 'upcoming';
        if (pastTabExists) btnPastTab.classList.remove('active');
        btnUpcomingTab.classList.add('active');
        if (pastTabExists) pastList.style.display = 'none';
        upcomingList.style.display = '';
        updateLoadMoreVisibility();
    });

    loadMoreBtn.addEventListener('click', function(e) {
        e.preventDefault();

        let page;
        if (activeTab === 'past') {
            if (!pastTabExists) return;
            page = parseInt(loadMoreBtn.dataset.pastPage || '1', 10);
        } else {
            page = parseInt(loadMoreBtn.dataset.upcomingPage || '1', 10);
        }

        const nextPage = page + 1;

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
        formData.append('page', nextPage);

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
                const hasMore = data.data.has_more || false;

                if (activeTab === 'upcoming') {
                    upcomingList.insertAdjacentHTML('beforeend', newItemsHtml);
                    loadMoreBtn.dataset.upcomingPage = nextPage;
                    hasMoreUpcoming = hasMore;
                } else if (pastTabExists) {
                    pastList.insertAdjacentHTML('beforeend', newItemsHtml);
                    loadMoreBtn.dataset.pastPage = nextPage;
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

    upcomingList.style.display = '';
    if (pastTabExists) {
        pastList.style.display = 'none';
    }

    const styleBlocks = document.querySelectorAll('style');
    styleBlocks.forEach(block => {
        if (block.textContent.includes('.stagent-bookings-past')) {
            block.remove();
        }
    });
});
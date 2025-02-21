document.addEventListener('DOMContentLoaded', function() {
    const btnPastTab = document.querySelector('.stagent-toggle-past');
    const btnUpcomingTab = document.querySelector('.stagent-toggle-upcoming');
    const upcomingList = document.querySelector('.stagent-bookings-upcoming');
    const pastList = document.querySelector('.stagent-bookings-past');
    const loadMoreBtn = document.querySelector('.stagent-load-more');

    let activeTab = 'upcoming';

    btnPastTab.addEventListener('click', function(e) {
        e.preventDefault();
        activeTab = 'past';
        btnUpcomingTab.classList.remove('active');
        btnPastTab.classList.add('active');
        upcomingList.classList.add('hidden');
        pastList.classList.remove('hidden');
    });

    btnUpcomingTab.addEventListener('click', function(e) {
        e.preventDefault();
        activeTab = 'upcoming';
        btnPastTab.classList.remove('active');
        btnUpcomingTab.classList.add('active');
        pastList.classList.add('hidden');
        upcomingList.classList.remove('hidden');
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
                    alert('Error: ' + data.data);
                    return;
                }

                const newItemsHtml = data.data.html;
                const itemCount = data.data.count || 0;

                if (activeTab === 'upcoming') {
                    upcomingList.insertAdjacentHTML('beforeend', newItemsHtml);
                    loadMoreBtn.dataset.upcomingPage = page + 1;
                } else {
                    pastList.insertAdjacentHTML('beforeend', newItemsHtml);
                    loadMoreBtn.dataset.pastPage = page + 1;
                }

                if (itemCount < perPage) {
                    loadMoreBtn.classList.add('hidden');
                    loadMoreBtn.classList.remove('block');
                } else {
                    loadMoreBtn.classList.add('block');
                    loadMoreBtn.classList.remove('hidden');
                }
            })
            .catch(err => {
                console.error('AJAX Error:', err);
            });
    });
});
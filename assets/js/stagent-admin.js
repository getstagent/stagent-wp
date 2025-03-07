document.addEventListener('DOMContentLoaded', function() {
    // Settings Page Logic
    const widgetInput = document.getElementById('stagent_booking_widget');
    const enableCheckbox = document.getElementById('stagent_enable_booking_widget');

    if (widgetInput && enableCheckbox) {
        function toggleBookingWidgetCheckbox() {
            enableCheckbox.disabled = widgetInput.value.trim() === '';
        }

        toggleBookingWidgetCheckbox();
        widgetInput.addEventListener('input', toggleBookingWidgetCheckbox);
    }

    // Shortcode Generator Logic
    const teamSelect = document.getElementById('shortcode_team_id');
    const artistSelect = document.getElementById('shortcode_artists');
    const artistsRow = document.getElementById('artists_row');
    const generateButton = document.getElementById('generate_shortcode');
    const shortcodeOutput = document.getElementById('shortcode_result');
    const copyButton = document.getElementById('copy_shortcode');

    if (teamSelect && artistSelect && artistsRow && generateButton && shortcodeOutput && copyButton) {
        teamSelect.addEventListener('change', async function() {
            const teamId = this.value;
            const teamType = this.options[this.selectedIndex]?.getAttribute('data-type');

            if (teamType === 'booking_agency' && teamId) {
                artistsRow.style.display = 'table-row';
                artistSelect.innerHTML = '<option value="" disabled>Loading artists...</option>';

                try {
                    const url = `${stagentData.ajaxUrl}?action=stagent_fetch_artists&team_id=${encodeURIComponent(teamId)}&nonce=${encodeURIComponent(stagentData.nonce)}`;
                    const response = await fetch(url, { method: 'GET', credentials: 'same-origin' });
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    const result = await response.json();
                    artistSelect.innerHTML = '';
                    if (result.success && result.data && result.data.length > 0) {
                        result.data.forEach(artist => {
                            const option = document.createElement('option');
                            option.value = artist.id;
                            option.textContent = artist.name;
                            artistSelect.appendChild(option);
                        });
                    } else {
                        artistSelect.innerHTML = '<option value="" disabled>No artists found</option>';
                    }
                } catch (error) {
                    console.error('Failed to fetch artists:', error);
                    artistSelect.innerHTML = '<option value="" disabled>Error loading artists</option>';
                }
            } else {
                artistsRow.style.display = 'none';
                artistSelect.innerHTML = '';
            }
        });

        generateButton.addEventListener('click', function() {
            const teamId = teamSelect.value;
            const selectedArtists = Array.from(artistSelect.selectedOptions).map(option => option.value);
            const show = document.getElementById('shortcode_show')?.value || 'all';
            const perPage = document.getElementById('shortcode_per_page')?.value || '5';
            const showPast = document.getElementById('shortcode_show_past')?.checked ? 'true' : 'false';
            const canceled = document.getElementById('shortcode_canceled')?.checked ? 'true' : 'false';

            let shortcode = '[stagent_bookings';
            if (teamId) shortcode += ` team="${teamId}"`;
            if (selectedArtists.length > 0) shortcode += ` artists="${selectedArtists.join(',')}"`;
            if (show !== 'all') shortcode += ` show="${show}"`;
            if (perPage !== '5') shortcode += ` per_page="${perPage}"`;
            if (showPast === 'false') shortcode += ` show_past="${showPast}"`;
            if (canceled === 'true') shortcode += ` canceled="${canceled}"`;
            shortcode += ']';

            shortcodeOutput.value = shortcode;
        });

        copyButton.addEventListener('click', async function() {
            if (!shortcodeOutput.value) return;
            try {
                await navigator.clipboard.writeText(shortcodeOutput.value);
                copyButton.textContent = 'Copied!';
                setTimeout(() => copyButton.textContent = 'Copy', 2000);
            } catch (err) {
                alert('Failed to copy shortcode. Copy manually.');
            }
        });
    }
});
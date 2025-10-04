document.addEventListener('DOMContentLoaded', function() {
    // General Modal Logic
    const modals = {
        addMovieModal: document.getElementById("addMovieModal"),
        editMovieModal: document.getElementById("editMovieModal"),
        addSeriesModal: document.getElementById("addSeriesModal"),
        editSeriesModal: document.getElementById("editSeriesModal"),
        addEpisodeModal: document.getElementById("addEpisodeModal")
    };

    const buttons = {
        addMovieBtn: document.getElementById("addMovieBtn"),
        addSeriesBtn: document.getElementById("addSeriesBtn"),
        addEpisodeBtn: document.getElementById("addEpisodeBtn")
    };

    const closeButtons = {
        movieClose: document.querySelector(".movie-close"),
        editMovieClose: document.querySelector(".edit-movie-close"),
        seriesClose: document.querySelector(".series-close"),
        editSeriesClose: document.querySelector(".edit-series-close"),
        episodeClose: document.querySelector(".episode-close")
    };

    if (buttons.addMovieBtn) buttons.addMovieBtn.onclick = () => modals.addMovieModal.style.display = "flex";
    if (buttons.addSeriesBtn) buttons.addSeriesBtn.onclick = () => modals.addSeriesModal.style.display = "flex";
    if (buttons.addEpisodeBtn) buttons.addEpisodeBtn.onclick = () => modals.addEpisodeModal.style.display = "flex";

    if (closeButtons.movieClose) closeButtons.movieClose.onclick = () => modals.addMovieModal.style.display = "none";
    if (closeButtons.editMovieClose) closeButtons.editMovieClose.onclick = () => modals.editMovieModal.style.display = "none";
    if (closeButtons.seriesClose) closeButtons.seriesClose.onclick = () => modals.addSeriesModal.style.display = "none";
    if (closeButtons.editSeriesClose) closeButtons.editSeriesClose.onclick = () => modals.editSeriesModal.style.display = "none";
    if (closeButtons.episodeClose) closeButtons.episodeClose.onclick = () => modals.addEpisodeModal.style.display = "none";

    // Edit Movie Logic
    document.querySelectorAll('.edit-movie').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            fetch(`admin.php?get_details=true&type=movie&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('edit_movie_id').value = id;
                        document.getElementById('edit_movie_title').value = data.title;
                        document.getElementById('edit_movie_description').value = data.description;
                        document.getElementById('edit_movie_release_year').value = data.release_year;
                        document.getElementById('edit_movie_duration').value = data.duration;
                        document.getElementById('edit_movie_genre').value = data.genre_id;
                        if (modals.editMovieModal) {
                            modals.editMovieModal.style.display = 'flex';
                        }
                    }
                });
        });
    });

    // Edit Series Logic
    document.querySelectorAll('.edit-series').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            fetch(`admin.php?get_details=true&type=series&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('edit_series_id').value = id;
                        document.getElementById('edit_series_title').value = data.title;
                        document.getElementById('edit_series_description').value = data.description;
                        document.getElementById('edit_series_release_year').value = data.release_year;
                        document.getElementById('edit_series_genre').value = data.genre_id;
                        if (modals.editSeriesModal) {
                            modals.editSeriesModal.style.display = 'flex';
                        }
                    }
                });
        });
    });

    // Close modals if outside is clicked
    window.onclick = function(event) {
        if (event.target == modals.addMovieModal) modals.addMovieModal.style.display = "none";
        if (event.target == modals.editMovieModal) modals.editMovieModal.style.display = "none";
        if (event.target == modals.addSeriesModal) modals.addSeriesModal.style.display = "none";
        if (event.target == modals.editSeriesModal) modals.editSeriesModal.style.display = "none";
        if (event.target == modals.addEpisodeModal) modals.addEpisodeModal.style.display = "none";
    }
});